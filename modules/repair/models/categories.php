<?php
/**
 * @filesource modules/repair/modules/categories.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Repair\Categories;

use Gcms\Login;
use Kotchasan\Config;
use Kotchasan\Database\Sql;
use Kotchasan\Language;

/**
 * บันทึกสถานะสมาชิก
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\KBase
{

  /**
   * ลิสต์รายการหมวดหมู่ ตาม $type.
   *
   * @param int $type
   *
   * @return array
   */
  public static function all($type)
  {
    return \Kotchasan\Model::createQuery()
        ->select()
        ->from('category')
        ->where(array('type', $type))
        ->order('id')
        ->toArray()
        ->execute();
  }

  /**
   * อ่านรายการหมวดหมู่สำหรับใส่ลงใน select.
   *
   * @param int $type
   *
   * @return array
   */
  public static function toSelect($type)
  {
    $result = array();
    foreach (self::all($type) as $item) {
      $result[$item['category_id']] = $item['topic'];
    }

    return $result;
  }

  /**
   * รับค่าจาก action.
   */
  public function action()
  {
    $ret = array();
    // session, referer, can_config, ไม่ใช่สมาชิกตัวอย่าง
    if (self::$request->initSession() && self::$request->isReferer() && $login = Login::isMember()) {
      if (Login::checkPermission($login, 'can_config') && Login::notDemoMode($login)) {
        // ค่าที่ส่งมา
        $action = self::$request->post('action')->toString();
        $value = self::$request->post('value')->topic();
        // ตรวจสอบค่าที่ส่งมา
        if (preg_match('/^list_(add|delete|color|name|published|status)_([0-9]+)_([a-z]+)$/', $action, $match)) {
          // Model
          $model = new \Kotchasan\Model();
          // ตารางหมวดหมู่
          $table = $model->getTableName('category');
          if ($match[1] == 'add') {
            // เพิ่มแถวใหม่
            $data = array(
              'id' => Sql::NEXT('id', $table),
              'topic' => Language::get('Click to edit'),
              'color' => '#000000',
              'published' => 1,
              'type' => $match[3],
            );
            $data['id'] = $model->db()->insert($table, $data);
            // คืนค่าแถวใหม่
            $ret['data'] = Language::trans(\Repair\Categories\View::createRow($data));
            $ret['newId'] = 'list_'.$data['id'].'_'.$match[3];
          } elseif ($match[1] == 'delete') {
            // ลบ
            $model->db()->delete($table, array('id', (int)$match[2]));
            // คืนค่าแถวที่ลบ
            $ret['del'] = 'list_'.$match[2].'_'.$match[3];
          } elseif ($match[1] == 'color') {
            // แก้ไขสี
            $save = array('color' => $value);
          } elseif ($match[1] == 'name') {
            // แก้ไขชื่อ
            $save = array('topic' => $value);
          } elseif ($match[1] == 'published') {
            // แก้ไขการเผยแพร่
            $value = $value == 1 ? 0 : 1;
            $save = array('published' => $value);
          } elseif ($match[1] == 'status') {
            // โหลด config
            $config = Config::load(ROOT_PATH.'settings/config.php');
            // สถานะเริ่มต้นของการรับซ่อม
            $config->repair_first_status = (int)$value;
            // save config
            if (Config::save($config, ROOT_PATH.'settings/config.php')) {
              // คืนค่าข้อมูลที่แก้ไข
              $ret['edit'] = $value;
            } else {
              // ไม่สามารถบันทึก config ได้
              $ret['alert'] = sprintf(Language::get('File %s cannot be created or is read-only.'), 'settings/config.php');
            }
          }
          if (isset($save)) {
            // บันทึก
            $model->db()->update($table, (int)$match[2], $save);
            // คืนค่าข้อมูลที่แก้ไข
            $ret['edit'] = $value;
            $ret['editId'] = $action;
          }
        }
      }
    }
    if (empty($ret)) {
      $ret['alert'] = Language::get('Unable to complete the transaction');
    }
    // คืนค่าเป็น JSON
    echo json_encode($ret);
  }
}