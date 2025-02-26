<?php

namespace KKsonFramework\RedBeanPHP\ModelBase;

abstract class BaseModel extends BaseModelBase
{
    /**
     * @return User
     */
    public function getCreationUser() {
        return User::load($this->creation_user_id, true);
    }

    /**
     * @return User
     */
    public function getModifiedUser() {
        return User::load($this->modified_user_id,  true);
    }

    public function emptyStringFieldsToNull($fields) {
        foreach ($fields as $field) {
            if($this->{$field} == "") {
                $this->{$field} = null;
            }
        }
    }
}