<?php

namespace KKsonFramework\App\Controller\backend;

use KKsonFramework\CRUD\SlimKKsonCRUD;
use KKsonFramework\Auth\Auth;
use KKsonFramework\CRUD\BaseCRUDController;
use KKsonFramework\CRUD\FieldType\Dropdown;
use KKsonFramework\CRUD\FieldType\PermissionCheckboxList;
use KKsonFramework\CRUD\FieldType\PasswordWithConfirm;
use KKsonFramework\CRUD\FieldType\YesNoChineseSwitch;  
use KKsonFramework\CRUD\KKsonCRUD;
use KKsonFramework\CRUD\SearchFieldType\DropdownSearchField;
use KKsonFramework\CRUD\SearchFieldType\NumberSearchField;
use KKsonFramework\CRUD\SearchFieldType\TextSearchField;
use KKsonFramework\RedBeanPHP\Model\Permission;
use KKsonFramework\RedBeanPHP\Model\SystemLog;
use KKsonFramework\RedBeanPHP\Model\User;
use KKsonFramework\Utils\UrlUtils;
use RedBeanPHP\R;

class UserController extends BaseCRUDController
{
    /**
     * @param SlimKKsonCRUD $crud
     * @throws \ErrorException
     */
    public function main($crud)
    {
        $this->setTableDisplayName("用戶");
        $crud->checkPermission(Permission::PERMISSION_USER_ADMIN_VIEW);
        $this->setBaseTableName(User::_getTableName(), "u");

        if(!Auth::isPermitted(Permission::PERMISSION_USER_ADMIN_MODIFY)) {
            $crud->enableCreate(false);
            $crud->enableEdit(false);
            $crud->enableDelete(false);
        }

        if(!Auth::getUser()->isSystemAdmin()) {
            $notInRoleList[] = User::ROLE_SYSTEM_ADMIN;
        }

        $this->addWhereClause("{$this->baseFieldName("_deleted")} = 0");
        if(!empty($notInRoleList)) {
            $this->addWhereClause("{$this->baseFieldName("role")} NOT IN (".R::genSlots($notInRoleList).")", $notInRoleList);
        }

        $crud->field("username")->setDisplayName("用戶名稱");
        $crud->field("password")->setDisplayName("新密碼")->setFieldType(new PasswordWithConfirm());


        $roleFieldType = new Dropdown(User::getRoleList());
        $roleList = $roleFieldType->getOptions(true);

        $crud->field("role")->setDisplayName("身份")->setFieldType($roleFieldType);
        $crud->field("permission")->setDisplayName("權限(只適用於管理員身份)")->setFieldType(new PermissionCheckboxList(function($permission) {
            /** @var Permission $permission */
            return $permission->display_name;
        }));

        $crud->field("active")->setDisplayName("啟用")->setFieldType(new YesNoChineseSwitch());

        $this->addSearchableField(new TextSearchField("username", $this->crud));
        $this->addSearchableField(new NumberSearchField("id", $this->crud));
        if(count($roleList) > 1) {
            $this->addSearchableField(new DropdownSearchField("role", $roleList, "身份", $this->crud));
        }

        $crud->hideAllFields();
        $crud->showFields([
            "id",
            "username",
            "password",
            "role",
            "permission",
            "active",
            "creation_date",
            "creation_user_id",
            "modified_date",
            "modified_user_id"
        ]);

        $crud->beforeInsert(function($b) {
            if($b->role == User::ROLE_ADMIN) {
                $b->sharedPermissionList = [];
            }
        });
        $crud->beforeUpdate(function($b) {
            if($b->role != User::ROLE_ADMIN) {
                $b->sharedPermissionList = [];
            }
        });

        $crud->rowAction(function($user) {
            $html = "";
            /** @var User $user */
            if($user->role != User::ROLE_GENERAL_USER) {
                if(!Auth::isLoginAs() && Auth::isPermitted(Permission::PERMISSION_BACKEND_LOGIN_AS) && !$user->isSystemAdmin() && $user->active) {
                    $html .= "<a href='".UrlUtils::burl("login_as/{$user->id}")."' class='btn btn-success' style='margin-left:5px;'>登入</a>";
                } else {
                    $html .= "<a href='#' class='btn btn-success disabled' style='margin-left:5px;'>登入</a>";
                }
            }

            if(Auth::isPermitted(Permission::PERMISSION_FRONTEND_LOGIN_AS) && $user->active) {
                $html .= "<a href='".UrlUtils::burl("frontend_login_as/{$user->id}")."' class='btn btn-success' target='_blank' style='margin-left:5px;'>前台登入</a>";
            }

            return $html;


        });

        $this->initSearchFunction();
        $this->setupListViewDataClosures();
    }

    /**
     * @param KKsonCRUD $crud
     * @throws \Exception
     */
    public function listView($crud)
    {
        $crud->hideFields([
            "permission",
            "password",
            "creation_date",
            "creation_user_id",
            "modified_date",
            "modified_user_id"
        ]);
    }

    /**
     * @param KKsonCRUD $crud
     * @throws \Exception
     */
    public function create($crud)
    {
        $crud->field("username")->setRequired(true)->setUnique(true);

        $crud->hideFields([
            "active",
            "creation_date",
            "creation_user_id",
            "modified_date",
            "modified_user_id"
        ]);
    }

    /**
     * @param KKsonCRUD $crud
     * @throws \Exception
     */
    public function edit($crud)
    {
        /** @var User $editUser */
        $editUser = $crud->getBean();
        if($editUser->role == User::ROLE_SYSTEM_ADMIN && Auth::getUser()->role != User::ROLE_SYSTEM_ADMIN) {
            SystemLog::createInsufficientPermissionLog("role", User::ROLE_SYSTEM_ADMIN);
            die($crud->render("backend/no_permission"));
        }
        $crud->field("username")->setReadOnly(true)->setDisabled(true);
    }
}