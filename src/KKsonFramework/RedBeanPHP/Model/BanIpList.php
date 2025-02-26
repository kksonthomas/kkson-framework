<?php

namespace KKsonFramework\RedBeanPHP\Model;

use DateTime;
use KKsonFramework\RedBeanPHP\ModelBase\BaseModelBase;
use KKsonFramework\Utils\DateUtils;
use RedBeanPHP\R;

/**
 * @property  string ip
 * @property  string reason
 * @property  string reason_chi
 * @property  string unbanned_date
 * @property int is_auto_unban
 */
class BanIpList extends BaseModelBase
{

    public static function _getTableName()
    {
        return "ban_ip_list";
    }

    //override base update method to do nothing
    public function update() {
    }

    public static function createBanIp($ip, $reason = null, $reasonChi = null, $unbanDate = null, $isAutoUnban = false) {
        $banIp = self::dispenseModel();
        $banIp->ip = $ip;
        $banIp->reason = $reason;
        $banIp->reason_chi = $reasonChi ? $reasonChi : $reason;
        $banIp->unbanned_date = $unbanDate;
        $banIp->is_auto_unban = $isAutoUnban ? 1 : 0;
        $banIp->store();
        return $banIp;
    }

    public static function getIpLastUnbannedDate($ip) {
        return R::getCell("SELECT unbanned_date FROM ban_ip_list WHERE ip = ? AND unbanned_date is not null ORDER BY unbanned_date DESC LIMIT 1", [$ip]);
    }

    public static function getIpLastAutoUnbannedDate($ip) {
        return R::getCell("SELECT unbanned_date FROM ban_ip_list WHERE ip = ? AND unbanned_date is not null AND is_auto_unban = 1 ORDER BY unbanned_date DESC LIMIT 1", [$ip]);
    }


    public static function getIpAutoUnbannedCount($ip) {
        $dateString = (new DateTime())->sub(new \DateInterval("P1M"))->format("Y-m-d H:i:s");

        return R::getCell("SELECT COUNT(*) FROM ban_ip_list WHERE ip = ? AND is_auto_unban = 1  AND creation_date > ?", [$ip , $dateString]) - 0;
    }

    public static function getNextIpAutoUnbannedDate($ip) {
        $count = self::getIpAutoUnbannedCount($ip);

        $min = pow(5, $count);
        return (new DateTime())->add(new \DateInterval("PT{$min}M"))->format("Y-m-d H:i:s");
    }


    public static function getBannedIpList($ip) {
        return self::findOne("ip = ? AND (unbanned_date is null OR unbanned_date > ?)", [$ip, DateUtils::now()]);
    }


    /*
     * Helper functions
     */

}