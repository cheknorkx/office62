<?php
/**
 * @filesource modules/booking/models/report.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Booking\Report;

use Gcms\Login;
use Kotchasan\Database\Sql;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * โมเดลสำหรับ (report.php).
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * Query ข้อมูลสำหรับส่งให้กับ DataTable.
   *
   * @param int $status
   *
   * @return \Kotchasan\Database\QueryBuilder
   */
  public static function toDataTable($status)
  {
    $sql = Sql::create('(CASE WHEN NOW() BETWEEN V.`begin` AND V.`end` THEN 1 WHEN NOW() > V.`end` THEN 2 ELSE 0 END) AS `today`');

    return static::createQuery()
        ->select('V.id', 'V.topic', 'V.room_id', 'R.name', 'U.name contact', 'U.phone', 'V.begin', 'V.end', 'V.create_date', $sql)
        ->from('reservation V')
        ->join('rooms R', 'INNER', array('R.id', 'V.room_id'))
        ->join('user U', 'INNER', array('U.id', 'V.member_id'))
        ->where(array('V.status', $status));
  }

  /**
   * รับค่าจาก action.
   *
   * @param Request $request
   */
  public function action(Request $request)
  {
    $ret = array();
    // session, referer, สามารถอนุมัติห้องประชุมได้
    if ($request->initSession() && $request->isReferer() && $login = Login::isMember()) {
      if (Login::notDemoMode($login) && Login::checkPermission($login, 'can_approve_room')) {
        // รับค่าจากการ POST
        $action = $request->post('action')->toString();
        // id ที่ส่งมา
        if (preg_match_all('/,?([0-9]+),?/', $request->post('id')->toString(), $match)) {
          if ($action === 'delete') {
            // ลบ
            $this->db()->delete($this->getTableName('reservation'), array('id', $match[1]), 0);
            $this->db()->delete($this->getTableName('reservation_data'), array('reservation_id', $match[1]), 0);
            // reload
            $ret['location'] = 'reload';
          }
        }
      }
    }
    if (empty($ret)) {
      $ret['alert'] = Language::get('Unable to complete the transaction');
    }
    // คืนค่า JSON
    echo json_encode($ret);
  }

  /**
   * อ่านข้อมูลรายการที่เลือก
   * คืนค่าข้อมูล object ไม่พบคืนค่า null.
   *
   * @param int $id
   *
   * @return object|null
   */
  public function bookDetail($id)
  {
    $query = $this->db()->createQuery()
      ->from('reservation V')
      ->join('rooms R', 'INNER', array('R.id', 'V.room_id'))
      ->join('user U', 'INNER', array('U.id', 'V.member_id'))
      ->where(array('V.id', $id));
    $select = array('V.*', 'R.name', 'U.name contact', 'U.phone');
    $n = 1;
    foreach (Language::get('ROOM_CUSTOM_TEXT') as $key => $label) {
      $query->join('rooms_meta M'.$n, 'LEFT', array(array('M'.$n.'.room_id', 'R.id'), array('M'.$n.'.name', $key)));
      $select[] = 'M'.$n.'.value '.$key;
      ++$n;
    }
    foreach (Language::get('BOOKING_SELECT') + Language::get('BOOKING_OPTIONS') as $key => $label) {
      $query->join('reservation_data M'.$n, 'LEFT', array(array('M'.$n.'.reservation_id', 'V.id'), array('M'.$n.'.name', $key)));
      $select[] = 'M'.$n.'.value '.$key;
      ++$n;
    }

    return $query->first($select);
  }
}