<?php
/**
 * @filesource modules/inventory/controllers/init.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Inventory\Init;

use Gcms\Login;
use Kotchasan\Http\Request;
use Kotchasan\Language;

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
    $submenus = array();
    // สามารถตั้งค่าระบบได้
    if (Login::checkPermission($login, 'can_config')) {
      $submenus[] = array(
        'text' => '{LNG_Settings}',
        'url' => 'index.php?module=inventory-settings',
      );
      foreach (Language::get('INVENTORY_CATEGORIES') as $type => $text) {
        $submenus[] = array(
          'text' => $text,
          'url' => 'index.php?module=inventory-categories&amp;type='.$type,
        );
      }
    }
    // สามารถบริหารจัดการคลังสินค้าได้
    if (Login::checkPermission($login, 'can_manage_inventory')) {
      $submenus[] = array(
        'text' => '{LNG_Inventory}',
        'url' => 'index.php?module=inventory-setup',
      );
      $submenus[] = array(
        'text' => '{LNG_Add New} {LNG_Equipment}',
        'url' => 'index.php?module=inventory-write',
      );
    }
    if (!empty($submenus)) {
      $menu->add('settings', '{LNG_Inventory}', null, $submenus);
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
    $permissions['can_manage_inventory'] = '{LNG_Can manage the inventory}';

    return $permissions;
  }
}