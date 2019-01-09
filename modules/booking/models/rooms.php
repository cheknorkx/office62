<?php
/**
 * @filesource modules/booking/models/rooms.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Booking\Rooms;

use Gcms\Login;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * โมเดลสำหรับ (rooms.php).
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
   * @return \Kotchasan\Database\QueryBuilder
   */
  public static function toDataTable()
  {
    return static::createQuery()
        ->select('R.id', 'R.name', 'R.detail', 'R.color')
        ->from('rooms R')
        ->where(array('R.published', 1))
        ->order('R.name');
  }

  /**
   * Query ห้องประชุม ใส่ลงใน select.
   *
   * @return array
   */
  public static function toSelect()
  {
    $query = static::createQuery()
      ->select('id', 'name')
      ->from('rooms')
      ->where(array('published', 1))
      ->order('name')
      ->cacheOn();
    $result = array();
    foreach ($query->execute() as $item) {
      $result[$item->id] = $item->name;
    }

    return $result;
  }

  /**
   * รับค่าจาก action.
   *
   * @param Request $request
   */
  public function action(Request $request)
  {
    $ret = array();
    // session, referer, สมาชิก
    if ($request->initSession() && $request->isReferer()) {
      if (Login::isMember()) {
        if ($request->post('action')->toString() === 'detail') {
          // แสดงรายละเอียดห้อง
          $search = \Booking\Write\Model::get($request->post('id')->toInt());
          if ($search) {
            $ret['modal'] = createClass('Booking\Detail\View')->room($search);
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
}