<?php
/**
 * @filesource modules/repair/models/email.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Repair\Email;

use Kotchasan\Date;
use Kotchasan\Language;

/**
 * ส่งอีเมลไปยังผู้ที่เกี่ยวข้อง.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\KBase
{
    /**
     * ส่งอีเมลแจ้งซ่อม
     *
     * @param string $mailto อีเมลผู้แจ้ง
     * @param string $name   ชื่อผู้แจ้ง
     * @param array  $repair ข้อมูลการแจ้ง
     */
    public static function send($mailto, $name, $repair)
    {
        // อ่านข้อมูลพัสดุ
        $inventory = \Inventory\Write\Model::get($repair['inventory_id']);
        $ret = array();
        // ข้อความ
        $msg = array(
            '{LNG_Repair Jobs}',
            '{LNG_Informer}: '.$name,
            '{LNG_Equipment}: '.$inventory->equipment,
            '{LNG_Serial/Registration number}: '.$inventory->serial,
            '{LNG_date}: '.Date::format($repair['create_date']),
            '{LNG_problems and repairs details}: '.$repair['job_description'],
            'URL: '.WEB_URL,
        );
        $msg = Language::trans(implode("\n", $msg));
        // ส่งอีเมลไปยังผู้ทำรายการเสมอ
        $emails = array($mailto => $mailto.'<'.$name.'>');
        // ส่งอีเมลไปยังผู้ที่เกี่ยว
        if (!empty(self::$cfg->repair_send_mail)) {
            // อีเมลของมาชิกที่สามารถอนุมัติได้ทั้งหมด
            $query = \Kotchasan\Model::createQuery()
                ->select('username', 'name')
                ->from('user')
                ->where(array('social', 0))
                ->andWhere(array(
                    array('status', 1),
                    array('permission', 'LIKE', '%,can_repair,%'),
                ), 'OR')
                ->cacheOn();

            foreach ($query->execute() as $item) {
                $emails[$item->username] = $item->username.'<'.$item->name.'>';
            }
        }
        // ส่งอีเมล
        $subject = '['.self::$cfg->web_title.'] '.Language::get('Repair Jobs');
        $err = \Kotchasan\Email::send(implode(',', $emails), self::$cfg->noreply_email, $subject, nl2br($msg));
        if ($err->error()) {
            // คืนค่า error
            $ret[] = $err->getErrorMessage();
        }
        if (!empty(self::$cfg->repair_line_id)) {
            // อ่าน token
            $search = \Kotchasan\Model::createQuery()
                ->from('line')
                ->where(array('id', self::$cfg->repair_line_id))
                ->cacheOn()
                ->first('token');
            if ($search) {
                $err = \Gcms\Line::send($msg, $search->token);
                if ($err != '') {
                    $ret[] = $err;
                }
            }
        }

        return empty($ret) ? '' : implode("\n", $ret);
    }
}
