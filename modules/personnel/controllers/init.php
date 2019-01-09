<?php
/**
 * @filesource modules/personnel/controllers/init.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Personnel\Init;

use Gcms\Login;
use Kotchasan\Http\Request;

/**
 * Init Module.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\KBase
{

  /**
   * ฟังก์ชั่นเริ่มต้นการทำงานของโมดูลที่ติดตั้ง
   * และจัดการเมนูของโมดูล.
   *
   * @param Request                $request
   * @param \Index\Menu\Controller $menu
   * @param array                  $login
   */
  public static function execute(Request $request, $menu, $login)
  {
    $menu->addTopLvlMenu('personnel', '{LNG_Personnel}', 'index.php?module=personnel-setup', null, 'module');
    if (Login::checkPermission($login, 'can_manage_personnel')) {
      $menu->add('settings', '{LNG_Personnel}', null, array(
        array(
          'text' => '{LNG_Settings}',
          'url' => 'index.php?module=personnel-settings',
        ),
      ));
    }
  }

  /**
   * รายการ permission ของโมดูล.
   *
   * @param array $permissions
   *
   * @return array
   */
  public static function updatePermissions($permissions)
  {
    $permissions['can_manage_personnel'] = '{LNG_Can manage personnel}';

    return $permissions;
  }
}