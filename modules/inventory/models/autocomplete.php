<?php
/**
 * @filesource modules/inventory/models/autocomplete.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Inventory\Autocomplete;

use Gcms\Login;
use Kotchasan\Http\Request;

/**
 * ค้นหาครุภัณฑ์สำหรับ autocomplete.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * ค้นหาครุภัณฑ์สำหรับ autocomplete
   * เฉพาะรายการที่ตัวเองรับผิดชอบ และ ที่ไม่มีผู้รับผิดชอบ
   * คืนค่าเป็น JSON.
   *
   * @param Request $request
   */
  public function find(Request $request)
  {
    if ($request->initSession() && $request->isReferer() && $login = Login::isMember()) {
      // ข้อมูลที่ส่งมา
      if ($request->post('equipment')->exists()) {
        $search = $request->post('equipment')->topic();
        $order = 'equipment';
      } elseif ($request->post('serial')->exists()) {
        $search = $request->post('serial')->topic();
        $order = 'serial';
      }
      $where = array(
        array('member_id', array(0, $login['id'])),
      );
      if (isset($search)) {
        $where[] = array($order, 'LIKE', "%$search%");
      }
      // query
      $query = $this->db()->createQuery()
        ->select('id inventory_id', 'equipment', 'serial')
        ->from('inventory')
        ->where($where)
        ->limit($request->post('count', 20)->toInt())
        ->toArray();
      if (isset($order)) {
        $query->order($order);
      }

      $result = $query->execute();
      if (!empty($result)) {
        // คืนค่า JSON
        echo json_encode($result);
      }
    }
  }
}