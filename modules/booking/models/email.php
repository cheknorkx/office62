<?php
/**
 * @filesource modules/booking/models/email.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Booking\Email;

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
   * ส่งอีเมลแจ้งการจอง.
   *
   * @param string $mailto  อีเมลผู้จอง
   * @param string $name    ชื่อผู้จอง
   * @param array  $booking ข้อมูลการจอง
   */
  public static function send($mailto, $name, $booking)
  {
    $ret = array();
    // ข้อความ
    $msg = array(
      '{LNG_Book a meeting}',
      '{LNG_Contact name}: '.$name,
      '{LNG_Topic}: '.$booking['topic'],
      '{LNG_date}: '.Date::format($booking['begin']).' - '.Date::format($booking['end']),
      '{LNG_Status}: '.Language::find('BOOKING_STATUS', null, $booking['status']),
      'URL: '.WEB_URL,
    );
    $msg = Language::trans(implode("\n", $msg));
    // ส่งอีเมลไปยังผู้ทำรายการเสมอ
    $emails = array($mailto => $mailto.'<'.$name.'>');
    // ส่งอีเมลไปยังผู้ที่เกี่ยว
    if (!empty(self::$cfg->booking_send_mail)) {
      // อีเมลของมาชิกที่สามารถอนุมัติได้ทั้งหมด
      $query = \Kotchasan\Model::createQuery()
        ->select('username', 'name')
        ->from('user')
        ->where(array('social', 0))
        ->andWhere(array(
          array('status', 1),
          array('permission', 'LIKE', '%,can_approve_room,%'),
          ), 'OR')
        ->cacheOn();

      foreach ($query->execute() as $item) {
        $emails[$item->username] = $item->username.'<'.$item->name.'>';
      }
    }
    // ส่งอีเมล
    $subject = '['.self::$cfg->web_title.'] '.Language::get('Book a meeting');
    $err = \Kotchasan\Email::send(implode(',', $emails), self::$cfg->noreply_email, $subject, nl2br($msg));
    if ($err->error()) {
      // คืนค่า error
      $ret[] = $err->getErrorMessage();
    }
    if (!empty(self::$cfg->booking_line_id)) {
      // อ่าน token
      $search = \Kotchasan\Model::createQuery()
        ->from('line')
        ->where(array('id', self::$cfg->booking_line_id))
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