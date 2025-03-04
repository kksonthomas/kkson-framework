<?php
namespace KKsonFramework\CRUD;

use KKsonFramework\App\App;
use KKsonFramework\Auth\Auth;
use KKsonFramework\CRUD\Middleware\CSRFGuard;
use KKsonFramework\RedBeanPHP\Model\SystemLog;
use RedBeanPHP\RedException;
use Slim\Slim;
use KKsonFramework\Utils\UrlUtils;
use Stringy\Stringy;
use KKsonFramework\CRUD\FieldType\ReadOnlyUsernameField;

class SlimKKsonCRUD extends KKsonCRUD
{
    /**
     * @var bool
     */
    protected $checkLogin = false;

    private $groupName;
    private $apiGroupName;

    /** @var Slim */
    private $slim;

    /** @var callable */
    protected $configFunction;

    /** @var callable */
    protected $listviewFunction = null;

    /** @var callable */
    protected $createFunction = null;

    /** @var callable */
    protected $editFunction = null;

    /** @var callable */
    protected $deleteFunction = null;

    /** @var callable */
    protected $exportFunction = null;

    /** @var string[] */
    private $tableList = [];
    private $routeNameList = [];
    private $tableDisplayName = [];

    private $currentRouteName = "";

    protected $firstPageURL = "";
    
    protected $pageLayout = null;
    
    protected $loginViewName = "login";

    protected $menuViewName = "menu";

    protected $insufficientPermissionViewName = "insufficient_permission";

    /**
     * @return string
     */
    public function getPageLayout()
    {
        if ($this->pageLayout == null) {
            return $this->getThemeName() . "::page";
        } else {
            return $this->pageLayout;
        }
    }
    
    /**
     * @param string $pageLayout
     */
    public function setPageLayout($pageLayout)
    {
        $this->pageLayout = $pageLayout;
    }
    
    public function setCurrentTheme($theme)
    {
        parent::setCurrentTheme($theme);
    }

    /**
     * SlimCRUD constructor.
     * @throws RedException
     */
    public function __construct(string $groupName = "crud", string $apiGroupName = "ajax", ?Slim $slim = null)
    {
        if(session_id() == '') {
            session_start();
        }

        parent::__construct();
        $this->groupName = $groupName;
        $this->apiGroupName = $apiGroupName;

        if ($slim == null) {
            $this->slim = new Slim();
        } else {
            $this->slim = $slim;
        }

        $this->slim->add(new CSRFGuard());

        $baseUrl = UrlUtils::baseUrl(false, true);

        // Upload function
        $this->slim->post("$baseUrl/kkson-crud/upload/:type", function ($type) {

            if (!empty($_POST['uploadpath'])) {
                $result = $this->upload("upload", $_POST['uploadpath']);
            } else {
                $result = $this->upload();
            }

            if (isset($_GET["fullpath"]) && $_GET["fullpath"] == "no") {

            } else {
                $result["url"] = UrlUtils::fullRes($result["url"]);
            }
            
            if ($type == "js") {
                $url = $result["url"];

                if ($result["uploaded"]) {

                    $funcNum = isset($_GET['CKEditorFuncNum']) ? $_GET['CKEditorFuncNum'] : 0;

                    echo <<< HTML
<script type="text/javascript">
    window.parent.CKEDITOR.tools.callFunction("$funcNum", "$url", "");
</script>
HTML;
                } else {
                    $msg = $result["msg"];

                        echo <<< HTML
<script type="text/javascript">
    alert("$msg");
</script>
HTML;
                }
            } else {
                $this->enableJSONResponse();
                echo json_encode($result);
            }

        });

        // Upload Image function
        $this->slim->post("$baseUrl/kkson-crud/upload-image/:type", function ($type) {
            die("not fixed ");
            $result = $this->uploadImage("upload", "upload/", 1000);

            if (isset($_GET["fullpath"]) && $_GET["fullpath"] == "no") {

            } else {
                $result["url"] = UrlUtils::fullRes($result["url"]);
            }



            if ($type == "js") {
                $url = $result["url"];

                if ($result["uploaded"]) {
                    echo <<< HTML
<script type="text/javascript">
    window.parent.CKEDITOR.tools.callFunction("0", "$url", "");
</script>
HTML;
                } else {
                    $msg = $result["msg"];

                    echo <<< HTML
<script type="text/javascript">
    alert("$msg");
</script>
HTML;
                }

            } else {
                $this->enableJSONResponse();
                echo json_encode($result);
            }

        });

        $crud = $this;
        $this->slim->get("$baseUrl/auth/login", function () use ($crud) {
            if(Auth::isLoggedIn()) {
                $this->slim->redirect($this->getFirstPageURL());
            } else {
                echo $this->getTemplateEngine()->render($crud->loginViewName);  
            }

        });

        $app = $this->slim;

        $this->slim->post("$baseUrl/auth/login", function () use ($app, $crud) {
            $result=  Auth::login($_POST["username"], $_POST["password"], $error);

            if ($result) {
                if (isset($_SESSION["redirect"])) {
                    $app->redirect($_SESSION["redirect"]);
                } else {
                    $app->redirect($this->getFirstPageURL());
                }
            } else {
//                $_SESSION['msg'] = "Username or password invalid";
                $ipData = SystemLog::getHeaderIpData(true);
                $ip = reset($ipData);
                $failCount = null;
                if($ip) {
                    $failCount = App::checkIpLoginFailedCount($ip);
                }

                if(!App::isUAT()) {
                    $error = "密碼錯誤或用戶不存在";
                }
                $_SESSION['msg'] = $error . ($failCount ? " ({$failCount}次登入失敗)" : "");
                $app->redirect(UrlUtils::fullURL("auth/login"));
            }
        });

        $this->slim->get("$baseUrl/auth/logout", function () use ($app, $crud) {
            Auth::logout();
            $app->redirect(UrlUtils::fullURL("auth/login"));
        });

        $this->slim->response->headers->set("X-Frame-Options", "SAMEORIGIN");
        $this->slim->response->headers->set("X-Content-Type-Options", "nosniff");
        $this->slim->response->headers->set("Content-Security-Policy", "script-src 'self'");
    }

    private function init($tableName, $routeName, $p1 = null, $p2 = null, $p3 = null, $p4 = null, $p5 = null) {
        // Table Name set this time ONLY.
        $this->setTable($tableName);
        $this->setTableDisplayName($this->tableDisplayName[$routeName]);

        $this->currentRouteName = $routeName;

        $params = "";

        for ($i = 1; $i <= 5; $i++) {
            $paramName = "p$i";

            if ($$paramName != null) {
                $params .= "/" . $$paramName;
            } else {
                break;
            }
        }

        // WEB UI Url
        $this->setListViewLink(UrlUtils::burl("$this->groupName/$routeName/list" . $params));
        $this->setCreateLink(UrlUtils::burl("$this->groupName/$routeName/create" . $params));
        $this->setEditLink(UrlUtils::burl("$this->groupName/$routeName/edit/:id" . $params));
        $this->setCreateSuccURL($this->getListViewLink());

        // Export URL
        $this->setExportLink(UrlUtils::burl("$this->groupName/$routeName/export" . $params));

        // API Url
        $this->setCreateSubmitLink(UrlUtils::burl("$this->apiGroupName/$routeName" . $params));
        $this->setListViewJSONLink(UrlUtils::burl("$this->apiGroupName/$routeName/datatables" . $params));
        // var_dump($this->getListViewJSONLink());
        // die();
        $this->setEditSubmitLink(UrlUtils::burl("$this->apiGroupName/$routeName/:id" . $params));
        $this->setDeleteLink(UrlUtils::burl("$this->apiGroupName/$routeName/:id" . $params));

        $this->setEditName("編輯");
        $this->setCreateName("新增");
        $this->setDeleteName("刪除");
        $this->setExportName("匯出Excel");
    
        $this->field("creation_date")->setDisplayName("新增日期")->setDisabled(true)->setReadOnly(true);
        $this->field("creation_user_id")->setDisplayName("新增用戶")->setFieldType(new ReadOnlyUsernameField())->setDisabled(true)->setReadOnly(true);
        $this->field("modified_date")->setDisplayName("最後修改日期")->setDisabled(true)->setReadOnly(true);
        $this->field("modified_user_id")->setDisplayName("最後修改用戶")->setFieldType(new ReadOnlyUsernameField())->setDisabled(true)->setReadOnly(true);
    
        $this->field("_deleted")->setDisplayName("*已刪除*")->hide();
    }

    /**
     * @param $customCRUDFunction
     * @param BaseCRUDController $controller
     * @param mixed $p1
     * @param mixed $p2
     * @param mixed $p3
     * @param mixed $p4
     * @param mixed $p5
     * @return bool
     */
    private function loadMainClosure($customCRUDFunction, $controller, $p1 = null, $p2 = null, $p3 = null, $p4 = null, $p5 = null) {
        $result = true;

        if ($customCRUDFunction != null) {
            if ($controller != null) {
                $controller->setParam(0, $p1);
                $controller->setParam(1, $p2);
                $controller->setParam(2, $p3);
                $controller->setParam(3, $p4);
                $controller->setParam(4, $p5);
                $result = $controller->main($this);

            } else {
                $result = $customCRUDFunction($p1, $p2, $p3, $p4, $p5);
            }
        }

        return $result;
    }

    /**
     * @param BaseCRUDController $controller
     * @param mixed $p1
     * @param mixed $p2
     * @param mixed $p3
     * @param mixed $p4
     * @param mixed $p5
     * @return bool
     */
    private function loadListViewClosure($controller, $p1 = null, $p2 = null, $p3 = null, $p4 = null, $p5 = null) {
        $result = true;

        if ($controller != null) {
            $controller->setParam(0, $p1);
            $controller->setParam(1, $p2);
            $controller->setParam(2, $p3);
            $controller->setParam(3, $p4);
            $controller->setParam(4, $p5);
            $result = $controller->listView($this);


        } elseif ($this->listviewFunction != null) {
            $listviewFunction = $this->listviewFunction;
            $result = $listviewFunction($p1, $p2, $p3, $p4, $p5);

        }

        return $result;
    }

    /**
     * @param BaseCRUDController $controller
     * @param mixed $p1
     * @param mixed $p2
     * @param mixed $p3
     * @param mixed $p4
     * @param mixed $p5
     * @return bool
     */
    private function loadCreateClosure($controller, $p1 = null, $p2 = null, $p3 = null, $p4 = null, $p5 = null) {
        $result = true;

        if ($controller != null) {
            $controller->setParam(0, $p1);
            $controller->setParam(1, $p2);
            $controller->setParam(2, $p3);
            $controller->setParam(3, $p4);
            $controller->setParam(4, $p5);
            $result = $controller->create($this);


        } elseif ($this->createFunction != null) {
            $func = $this->createFunction;
            $result = $func($p1, $p2, $p3, $p4, $p5);

        }

        return $result;
    }

    /**
     * @param BaseCRUDController $controller
     * @param mixed $p1
     * @param mixed $p2
     * @param mixed $p3
     * @param mixed $p4
     * @param mixed $p5
     * @return bool
     */
    private function loadEditClosure($controller, $p1 = null, $p2 = null, $p3 = null, $p4 = null, $p5 = null) {
        $result = true;

        if ($controller != null) {
            $controller->setParam(0, $p1);
            $controller->setParam(1, $p2);
            $controller->setParam(2, $p3);
            $controller->setParam(3, $p4);
            $controller->setParam(4, $p5);
            $result = $controller->edit($this);


        } elseif ($this->editFunction != null) {
            $func = $this->editFunction;
            $result = $func($p1, $p2, $p3, $p4, $p5);

        }

        return $result;
    }

    /**
     * @param string $routeName
     * @param string $tableName
     * @param callable|BaseCRUDController $customCRUDFunction
     * @param string $displayName
     */
    public function add($routeName, $customCRUDFunction = null, $tableName = null, $displayName = null)
    {

        /**
         * @var BaseCRUDController
         */
        $controller = null;

        if ($customCRUDFunction instanceof BaseCRUDController) {
            $controller = $customCRUDFunction;
            $controller->setCRUD($this);
        }

        if ($tableName == null) {
            $tableName = $routeName;
        }

        $this->tableList[$routeName] = $tableName;
        $this->tableDisplayName[$routeName] = $displayName;
        $this->routeNameList[] = $routeName;

        /*
         * Page Group (ListView, CreateView, EditView)
         */
        $this->slim->group(UrlUtils::baseUrl(false, true) ."/$this->groupName/$routeName", function () use ($routeName, $customCRUDFunction, $tableName, $controller) {

            $this->slim->get("/", function () use ($routeName)  {
                $this->slim->redirectTo("_KKsonCRUD_" . $routeName);
            });

            /*
             * ListView
             */
            $this->slim->get("/list(/:p1(/:p2(/:p3(/:p4(/:p5)))))", function ($p1 = null, $p2 = null, $p3 = null, $p4 = null, $p5 = null) use ($routeName, $customCRUDFunction, $tableName, $controller) {

                // MUST INIT FIRST
                $this->init($tableName, $routeName, $p1, $p2, $p3, $p4, $p5);

                if ($this->configFunction != null) {
                    $function = $this->configFunction;
                    $result = $function();

                    if ($result === false) {
                        return;
                    }
                }

                $result = $this->loadMainClosure($customCRUDFunction, $controller, $p1, $p2, $p3, $p4, $p5);

                if ($result === false) {
                    return;
                }

                $result = $this->loadListViewClosure($controller, $p1, $p2, $p3, $p4, $p5);

                if ($result === false) {
                    return;
                }

                if ($this->isEnabledListView()) {
                    $this->renderListView();
                }

            })->name("_KKsonCRUD_" . $routeName);

            /*
             * Create
             */
            $this->slim->get("/create(/:p1(/:p2(/:p3(/:p4(/:p5)))))", function ($p1 = null, $p2 = null, $p3 = null, $p4 = null, $p5 = null) use ($routeName, $customCRUDFunction, $tableName, $controller) {

                // MUST INIT FIRST
                $this->init($tableName, $routeName, $p1, $p2, $p3, $p4, $p5);

                if ($this->configFunction != null) {
                    $function = $this->configFunction;
                    $result = $function();

                    if ($result === false) {
                        return;
                    }
                }

                $result = $this->loadMainClosure($customCRUDFunction, $controller, $p1, $p2, $p3, $p4, $p5);

                if ($result === false) {
                    return;
                }

                $result = $this->loadCreateClosure($controller, $p1, $p2, $p3, $p4, $p5);

                if ($result === false) {
                    return;
                }

                // Force Hide ID field
                $this->field("id")->hide();

                if ($this->isEnabledCreate()) {
                    $this->renderCreateView();
                }
            });

            /*
             * Edit
             */
            $this->slim->get("/edit/:id(/:p1(/:p2(/:p3(/:p4(/:p5)))))", function ($id, $p1 = null, $p2 = null, $p3 = null, $p4 = null, $p5 = null) use ($routeName, $customCRUDFunction, $tableName, $controller) {

                // MUST INIT FIRST
                $this->init($tableName, $routeName, $p1, $p2, $p3, $p4, $p5);

                // Load Bean first
                $this->loadBean($id);

                // ID must be hidden
                $this->field("id")->hide();

                if ($this->configFunction != null) {
                    $function = $this->configFunction;
                    $result = $function();

                    if ($result === false) {
                        return;
                    }
                }

                $result = $this->loadMainClosure($customCRUDFunction, $controller, $p1, $p2, $p3, $p4, $p5);

                if ($result === false) {
                    return;
                }

                $result = $this->loadEditClosure($controller, $p1, $p2, $p3, $p4, $p5);

                if ($result === false) {
                    return;
                }

                // If user show the ID field, force set it to readonly
                $this->field("id")->setReadOnly(true);

                if ($this->isEnabledEdit()) {
                    $this->renderEditView();
                }
            });

            /*
             * Export Excel
             */
            $this->slim->map("/export(/:p1(/:p2(/:p3(/:p4(/:p5)))))", function ($p1 = null, $p2 = null, $p3 = null, $p4 = null, $p5 = null) use ($routeName, $customCRUDFunction, $tableName, $controller) {

                // MUST INIT FIRST
                $this->init($tableName, $routeName, $p1, $p2, $p3, $p4, $p5);

                if ($this->configFunction != null) {
                    $function = $this->configFunction;
                    $result = $function();

                    if ($result === false) {
                        return;
                    }
                }

                $result = $this->loadMainClosure($customCRUDFunction, $controller, $p1, $p2, $p3, $p4, $p5);

                if ($result === false) {
                    return;
                }

                $result = $this->loadListViewClosure($controller, $p1, $p2, $p3, $p4, $p5);

                if ($result === false) {
                    return;
                }

                if ($this->exportFunction != null) {
                    $exportFunction = $this->exportFunction;
                    $result = $exportFunction($p1, $p2, $p3, $p4, $p5);

                    if ($result === false) {
                        return;
                    }
                }

                // TODO: isEnabledExport();
                $this->renderExcel();

            })->via('GET', 'POST');

        });

        /*
         * API Group, RESTful style.
         */
        $this->slim->group(UrlUtils::baseUrl(false, true) ."/$this->apiGroupName/$routeName", function () use ($routeName, $customCRUDFunction, $tableName, $controller) {

            /*
             * JSON for Listview
             */
            $this->slim->map("/list(/:p1(/:p2(/:p3(/:p4(/:p5)))))", function ($p1 = null, $p2 = null, $p3 = null, $p4 = null, $p5 = null) use ($routeName, $customCRUDFunction, $tableName, $controller) {
                $this->enableJSONResponse();

                // MUST INIT FIRST
                $this->init($tableName, $routeName, $p1, $p2, $p3, $p4, $p5);

                if ($this->configFunction != null) {
                    $function = $this->configFunction;
                    $result = $function();

                    if ($result === false) {
                        return;
                    }
                }

                $result = $this->loadMainClosure($customCRUDFunction, $controller, $p1, $p2, $p3, $p4, $p5);

                if ($result === false) {
                    return;
                }

                $result = $this->loadListViewClosure($controller, $p1, $p2, $p3, $p4, $p5);

                if ($result === false) {
                    return;
                }

                if ($this->isEnabledListView()) {
                    $this->getJSONList();
                }
                return;
            })->via('GET', 'POST');

            /*
             * For Datatables
             */
            $this->slim->map("/datatables(/:p1(/:p2(/:p3(/:p4(/:p5)))))", function ($p1 = null, $p2 = null, $p3 = null, $p4 = null, $p5 = null) use ($routeName, $customCRUDFunction, $tableName, $controller) {
                $this->enableJSONResponse();

                // MUST INIT FIRST
                $this->init($tableName, $routeName, $p1, $p2, $p3, $p4, $p5);

                if ($this->configFunction != null) {
                    $function = $this->configFunction;
                    $result = $function();

                    if ($result === false) {
                        return;
                    }
                }

                $result = $this->loadMainClosure($customCRUDFunction, $controller, $p1, $p2, $p3, $p4, $p5);

                if ($result === false) {
                    return;
                }

                $result = $this->loadListViewClosure($controller, $p1, $p2, $p3, $p4, $p5);

                if ($result === false) {
                    return;
                }

                if ($this->isEnabledListView()) {
                    $this->getListViewJSONString();
                }
                return;
            })->via('GET', 'POST');


            /*
         * View a bean
         * PUT /api/{tableName}/{id}
         */
            $this->slim->get("/:id(/:p1(/:p2(/:p3(/:p4(/:p5)))))", function ($id, $p1 = null, $p2 = null, $p3 = null, $p4 = null, $p5 = null) use ($routeName, $customCRUDFunction, $tableName, $controller) {

                // MUST INIT FIRST
                $this->init($tableName, $routeName, $p1, $p2, $p3, $p4, $p5);

                // Load Bean
                $this->loadBean($id);

                if ($this->configFunction != null) {
                    $function = $this->configFunction;
                    $result = $function();

                    if ($result === false) {
                        return;
                    }
                }

                // Custom Global Function
                $result = $this->loadMainClosure($customCRUDFunction, $controller, $p1, $p2, $p3, $p4, $p5);

                if ($result === false) {
                    return;
                }

                $result = $this->loadEditClosure($controller, $p1, $p2, $p3, $p4, $p5);

                if ($result === false) {
                    return;
                }

                // Force hide ID
                $this->field("id")->hide();

                // Insert into database
                if ($this->isEnabledEdit()) {


                   $json = $this->getJSON(false);

                    $this->enableJSONResponse();
                    echo $json;
                }
            });

            /*
             * Insert a bean
             * POST /api/{tableName}
             */
            $this->slim->post("(/:p1(/:p2(/:p3(/:p4(/:p5)))))", function ($p1 = null, $p2 = null, $p3 = null, $p4 = null, $p5 = null) use ($routeName, $customCRUDFunction, $tableName, $controller) {

                // MUST INIT FIRST
                $this->init($tableName, $routeName, $p1, $p2, $p3, $p4, $p5);

                if ($this->configFunction != null) {
                    $function = $this->configFunction;
                    $result = $function();

                    if ($result === false) {
                        return;
                    }
                }

                $result = $this->loadMainClosure($customCRUDFunction, $controller, $p1, $p2, $p3, $p4, $p5);

                if ($result === false) {
                    return;
                }

                // Custom Create Function
                $result = $this->loadCreateClosure($controller, $p1, $p2, $p3, $p4, $p5);

                if ($result === false) {
                    return;
                }

                // Force hide ID
                $this->field("id")->hide();

                // Insert into database
                if ($this->isEnabledCreate()) {
                    $jsonObject = $this->insertBean($_POST);

                    $this->enableJSONResponse();
                    echo json_encode($jsonObject);
                } else {
                    // TODO: Should be json object
                    echo "No permission";
                }

            });

            /*
             * Update a bean
             * PUT /crud/{tableName}/{id}
             */
            $this->slim->put("/:id(/:p1(/:p2(/:p3(/:p4(/:p5)))))", function ($id, $p1 = null, $p2 = null, $p3 = null, $p4 = null, $p5 = null) use ($routeName, $customCRUDFunction, $tableName, $controller) {

                // MUST INIT FIRST
                $this->init($tableName, $routeName, $p1, $p2, $p3, $p4, $p5);

                // Load Bean
                $this->loadBean($id);

                if ($this->configFunction != null) {
                    $function = $this->configFunction;
                    $result = $function();

                    if ($result === false) {
                        return;
                    }
                }

                // Custom Global Function
                $result = $this->loadMainClosure($customCRUDFunction, $controller, $p1, $p2, $p3, $p4, $p5);

                if ($result === false) {
                    return;
                }

                // Custom Create Function
                $result = $this->loadEditClosure($controller, $p1, $p2, $p3, $p4, $p5);

                if ($result === false) {
                    return;
                }

                // Force hide ID
                $this->field("id")->hide();

                // Insert into database
                if ($this->isEnabledEdit()) {
                    $jsonObject = $this->updateBean($this->slim->request()->params());

                    $this->enableJSONResponse();
                    echo json_encode($jsonObject);
                }
            });

            /*
             * Delete a bean
             * DELETE /crud/{tableName}/{id}
             */
            $this->slim->delete("/:id(/:p1(/:p2(/:p3(/:p4(/:p5)))))", function ($id, $p1 = null, $p2 = null, $p3 = null, $p4 = null, $p5 = null) use ($routeName, $customCRUDFunction, $tableName, $controller) {

                // MUST INIT FIRST
                $this->init($tableName, $routeName, $p1, $p2, $p3, $p4, $p5);

                $this->enableJSONResponse();

                $this->loadBean($id);

                if ($this->configFunction != null) {
                    $function = $this->configFunction;
                    $result = $function();

                    if ($result === false) {
                        return;
                    }
                }

                // Custom Global Function
                $result = $this->loadMainClosure($customCRUDFunction, $controller, $p1, $p2, $p3, $p4, $p5);

                if ($result === false) {
                    return;
                }

                // Custom Delete Function
                if ($this->deleteFunction != null) {
                    $deleteFunction = $this->deleteFunction;
                    $result =  $deleteFunction($id, $p1, $p2, $p3, $p4, $p5);

                    if ($result === false) {
                        return;
                    }
                }

                if ($this->isEnabledDelete()) {
                    $this->deleteBean();

                    $result = new \stdClass();
                    $result->status = "succ";

                    echo json_encode($result);
                }

            });

        });

    }

    /**
     * @param callable $func
     */
    public function config($func) {
        $this->configFunction = $func;
    }

    /**
     * @param callable $func
     */
    public function listView($func)
    {
        $this->listviewFunction = $func;
    }

    /**
     * @param callable $func
     */
    public function create($func)
    {
        $this->createFunction = $func;
    }

    /**
     * @param callable $func
     */
    public function edit($func)
    {
        $this->editFunction = $func;
    }

    /**
     * @param callable $func
     */
    public function delete($func)
    {
        $this->deleteFunction = $func;
    }

    /**
     * @param callable $func
     */
    public function export($func)
    {
        $this->exportFunction = $func;
    }

    /**
     * @return Slim
     */
    public function getSlim()
    {
        return $this->slim;
    }

    public function run()
    {
        $this->enableMenu();
        $this->slim->run();
    }

    /**
     * Please make sure you return a valid JSON.
     */
    public function enableJSONResponse()
    {
        $this->slim->response->header('Content-Type', 'application/json');
    }

    /**
     * @return string
     * @deprecated
     */
    public function generateMenu() {
        $temp = "<nav><ul>";

        foreach ($this->routeNameList as $routeName) {
            $url = $this->slim->urlFor("_KKsonCRUD_" . $routeName);
            $temp .= "<li><a href='$url'>$routeName</a></li>";
        }

        $temp .= "</ul></nav>";
        return $temp;
    }

    public function getMenuItems() {
        $tempList = [];

        foreach ($this->routeNameList as $routeName) {
            $item = [];
            $item["url"] = $this->slim->urlFor("_KKsonCRUD_" . $routeName);
            $item["name"] = $this->getTableDisplayName($routeName);
            $item["routeName"] = $routeName;
            $tempList[] = $item;
        }
        return $tempList;
    }

    public function enableMenu($menuItems = []) {
        $plates = $this->getTemplateEngine();

        $name = $this->menuViewName;

        $menu = $plates->render($name, [
            "menuItems" => array_merge($this->getMenuItems(), $menuItems)
        ]);

        $this->setData("menu", $menu);
    }

    /**
     * $crud->url("user", ["male", "1970"]);
     * @param $routeName
     * @param array $data
     * @return string
     */
    public function url($routeName, $data = []) {
        $data2 = [];
        $i = 1;

        // Map key (p1, p2, p3....)
        foreach ($data as $value) {
            $data2["p" . $i++] = $value;
        }

        return $this->slim->urlFor("_KKsonCRUD_" . $routeName, $data2);
    }

    public function getTableDisplayName($routeName = null) {
        if (isset($this->tableDisplayName[$routeName] ) && $this->tableDisplayName[$routeName] != null) {
            return $this->tableDisplayName[$routeName];
        } else {
            return Stringy::create($routeName)->humanize()->titleize()->__toString();
        }
    }

    /**
     * Override render Excel function
     * @throws Exception\NoFieldException
     */
    public function renderExcel()
    {
        $this->beforeRender();
        $list = $this->getListViewData();

        $helper = new ExcelHelper();
 
        $helper->setHeaderClosure(function ($key, $value) {
            $this->getSlim()->response()->header($key, $value);
        });

        $helper->genExcel($this, $list, $this->getExportFilename());
    }

    /**
     * Content Page
     * @param $route
     * @param callable $callback
     */
    public function page($route, $callback) {
        if ($this->configFunction != null) {
            $function = $this->configFunction;
            $result = $function();

            if ($result === false) {
                return;
            }
        }

        $crud = $this;
        
        $this->getSlim()->get($route, function () use ($crud, $callback) {
            $content = $callback();
            
            $this->render($this->getPageLayout(), [
                "content" => $content
            ], true);
        });
    }

    public function notFound() {
        $this->getSlim()->notFound();
    }

    public function checkLogin() {
        $crud = $this;
        Auth::checkLogin(function () use ($crud) {
            // Get request object
            $req = $crud->getSlim()->request;

            //Get root URI
            $rootUri = $req->getRootUri();

            //Get resource URI
            $resourceUri = $req->getResourceUri();

            $_SESSION["redirect"] = $resourceUri;
            $crud->getSlim()->redirect(UrlUtils::fullURL("auth/login"));    
        });
    }

    public function checkPermission($permission) {
        if(!Auth::isPermitted($permission)) {
            SystemLog::createInsufficientPermissionLog("permission", $permission);
            $this->getSlim()->halt(403, $this->render($this->insufficientPermissionViewName, ["permission" => $permission]));
        };
    }

    public function checkPermissionOrRoles($permission, $roles) {
        if(!Auth::isPermitted($permission) && !Auth::getUser()->isRole($roles)) {
            SystemLog::createInsufficientPermissionLog("permission", $permission);
            SystemLog::createInsufficientPermissionLog("role", $roles);
            $this->getSlim()->halt(403, $this->render($this->insufficientPermissionViewName, ["permission" => $permission, "roles" => $roles]));
        };
    }

    /**
     * @param array $roles
     */
    public function checkRole($roles) {
        Auth::checkRole($roles, function () use ($roles) {
            SystemLog::createInsufficientPermissionLog("role", $roles);
            $this->getSlim()->halt(403, $this->render($this->insufficientPermissionViewName, ["roles" => $roles]));
        });
    }

    /**
     * @return string
     */
    public function getFirstPageURL()
    {
        return $this->firstPageURL;
    }

    /**
     * @param string $firstPageURL
     */
    public function setFirstPageURL($firstPageURL)
    {
        $this->firstPageURL = $firstPageURL;
    }
    
    /**
     * @return string
     */
    public function getLoginViewName()
    {
        return $this->loginViewName;
    }
    
    /**
     * @param string $loginViewName
     */
    public function setLoginViewName($loginViewName)
    {
        $this->loginViewName = $loginViewName;
    }

    /**
     * @return string
     */
    public function getMenuViewName(): string
    {
        return $this->menuViewName;
    }

    /**
     * @param string $menuViewName
     */
    public function setMenuViewName(string $menuViewName): void
    {
        $this->menuViewName = $menuViewName;
    }

    /**
     * @return string
     */
    public function getInsufficientPermissionViewName(): string
    {
        return $this->insufficientPermissionViewName;
    }

    /**
     * @param string $insufficientPermissionViewName
     */
    public function setInsufficientPermissionViewName(string $insufficientPermissionViewName): void
    {
        $this->insufficientPermissionViewName = $insufficientPermissionViewName;
    }
}