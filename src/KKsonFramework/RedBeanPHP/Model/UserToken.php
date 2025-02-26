<?php

namespace KKsonFramework\RedBeanPHP\Model;


use KKsonFramework\Auth\Auth;
use KKsonFramework\RedBeanPHP\ModelBase\BaseModelBase;
use RedBeanPHP\OODBBean;
use RedBeanPHP\R;

/**
 * @property mixed data
 * @property mixed type
 * @property mixed expiry_date
 * @property mixed salt_token
 * @property mixed user_id
 * @property int is_used
 * @property int is_voided
 */
class UserToken extends BaseModelBase
{
    const TYPE_FRONTEND_LOGIN_AS = "frontend_login_as";

    public static function _getTableName()
    {
        return "user_token";
    }

    /**
     * @param $token
     * @return UserToken
     */
    public static function findByToken($token) {
        $saltToken = self::saltToken($token);
        $bean = R::findOne(self::_getTableName(), "salt_token = ?", [$saltToken]);
        if($bean) {
            return $bean->box();
        } else {
            return null;
        }
    }

    public static function voidToken($sql, $data = []) {
        /** @var UserToken[] $tokens */
        $tokens = UserToken::find($sql, $data);
        foreach ($tokens as $token) {
            $token->is_voided = 1;
            $token->save();
        }
        return $tokens;
    }

    /**
     * @param $type
     * @param null $data
     * @param int $expiryIntervalSec
     * @param UserToken $tokenObject
     * @return bool|string
     * @throws \Exception
     */
    public static function genToken($type, $data = null, $expiryIntervalSec = 180, &$tokenObject = null) {
        $token = self::uniqidReal(32);
        $tokenObject = self::dispenseModel();
        $tokenObject->salt_token = self::saltToken($token);
        if(Auth::getUser()) {
            $tokenObject->user_id = Auth::getUser()->id;
        } else {
            $tokenObject->user_id = null;
        }
        $expiry_date = new \DateTime();
        $expiry_date->add(new \DateInterval("PT{$expiryIntervalSec}S"));
        $tokenObject->expiry_date = $expiry_date->format("Y-m-d H:i:s");
        $tokenObject->type = $type;
        $tokenObject->data = $data ? json_encode($data) : null;
        $tokenObject->store();
        return $token;
    }

    public static function saltToken($token) {
        return sha1(md5(md5($token)));
    }

    public static function uniqidReal($length = 13) {
        // uniqid gives 13 chars, but you could adjust it to your needs.
        if (function_exists("random_bytes")) {
            $bytes = random_bytes(ceil($length / 2));
        } elseif (function_exists("openssl_random_pseudo_bytes")) {
            $bytes = openssl_random_pseudo_bytes(ceil($length / 2));
        } else {
            throw new \Exception("no cryptographically secure random function available");
        }
        return substr(bin2hex($bytes), 0, $length);
    }

    /*
     * Helper functions
     */
    public function isUsed()
    {
        return $this->is_used != 0;
    }

    public function use()
    {
        $this->is_used = 1;
        R::store($this);
    }

    public function isExpired() {
        return new \DateTime() >= \DateTime::createFromFormat("Y-m-d H:i:s", $this->expiry_date);
    }

    public function isVoided() {
        return $this->is_voided != 0;
    }

    /**
     * @return User
     */
    public function getUser() {
        if(!$this->hasCache("user")) {
            $this->putCache("user", User::load($this->user_id));
        }
        return $this->getCache("user");
    }

    public function getDecodedData()
    {
        return json_decode($this->data, true);
    }

    public function getVerifyCode($len = 4) {
        $result = preg_replace('/[^\d\s]/m', '', $this->salt_token);
        return substr($result, 0, $len);
    }
}