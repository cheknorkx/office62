<?php
/**
 * @filesource modules/index/models/lines.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Index\Lines;

use Gcms\Login;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=lines.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * อ่านข้อมูลสำหรับใส่ลงในตาราง.
   *
   * @return \Kotchasan\Database\QueryBuilder
   */
  public static function toDataTable()
  {
    return static::createQuery()
        ->select('id', 'name', 'token')
        ->from('line');
  }

  /**
   * ตารางสมาชิก (lines.php).
   *
   * @param Request $request
   */
  public function action(Request $request)
  {
    $ret = array();
    // session, referer, member, can_config, ไม่ใช่สมาชิกตัวอย่าง
    if ($request->initSession() && $request->isReferer() && $login = Login::isMember()) {
      if (Login::checkPermission($login, 'can_config') && Login::notDemoMode($login)) {
        // รับค่าจากการ POST
        $action = $request->post('action')->toString();
        // id ที่ส่งมา
        if (preg_match_all('/,?([0-9]+),?/', $request->post('id')->toString(), $match)) {
          // ตาราง user
          $user_table = $this->getTableName('user');
          if ($action === 'delete') {
            // ลบ
            $this->db()->delete($this->getTableName('line'), array(
              array('id', $match[1]),
              ), 0);
            // reload
            $ret['location'] = 'reload';
          } elseif ($action === 'edit') {
            // ฟอร์ม
            $index = \Index\Line\Model::get((int)$match[1][0]);
            if ($index) {
              $ret['modal'] = Language::trans(createClass('Index\Line\View')->render($request, $index));
            }
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