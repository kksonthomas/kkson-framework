<?php

namespace KKsonFramework\RedBeanPHP\Model;

use KKsonFramework\RedBeanPHP\ModelBase\BaseModel;

/**
 * @property mixed user_id
 * @property mixed session_id
 */
class Session extends BaseModel
{

    public static function _getTableName()
    {
        return "session";
    }

    public static function _enabledMimicDelete()
    {
        return false;
    }


    /**
     * @param User $user
     * @return Session
     */
    public static function getLastSessionByUser($user) {
        return self::findOne("creation_user_id = ? ORDER BY creation_date DESC", [$user->id]);
    }

    /**
     * @param $user
     * @return Session
     */
    public static function createSessionByCurrentUser() {
        $session = self::dispenseModel();
        $session->session_id = session_id();
        $session->save();
        return $session;
    }

    /**
     * @return bool
     */
    public function isMatchedToCurrentSession() {
        return $this->session_id == session_id();
    }
}