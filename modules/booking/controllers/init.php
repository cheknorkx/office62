<?php
/**
 * @filesource modules/booking/controllers/init.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Booking\Init;

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
        $menu->addTopLvlMenu('booking', '{LNG_Room}', null, array(
            array(
                'text' => '{LNG_My Booking}',
                'url' => 'index.php?module=booking',
            ),
            array(
                'text' => '{LNG_List of} {LNG_Room}',
                'url' => 'index.php?module=booking-rooms',
            ),
            array(
                'text' => '{LNG_Book a meeting}',
                'url' => 'index.php?module=booking-booking',
            ),
        ), 'member');
        if (Login::checkPermission($login, 'can_manage_room')) {
            // เมนูตั้งค่า
            $submenus = array(
                array(
                    'text' => '{LNG_Settings}',
                    'url' => 'index.php?module=booking-settings',
                ),
                array(
                    'text' => '{LNG_List of} {LNG_Room}',
                    'url' => 'index.php?module=booking-setup',
                ),
                array(
                    'text' => '{LNG_Add New} {LNG_Room}',
                    'url' => 'index.php?module=booking-write',
                ),
            );
            foreach (Language::get('BOOKING_OPTIONS') as $type => $text) {
                $submenus[] = array(
                    'text' => $text,
                    'url' => 'index.php?module=booking-categories&amp;type='.$type,
                );
            }
            foreach (Language::get('BOOKING_SELECT') as $type => $text) {
                $submenus[] = array(
                    'text' => $text,
                    'url' => 'index.php?module=booking-categories&amp;type='.$type,
                );
            }
            $menu->add('settings', '{LNG_Room}', null, $submenus);
        }
        if (Login::checkPermission($login, 'can_approve_room')) {
            $submenus = array();
            foreach (Language::get('BOOKING_STATUS') as $type => $text) {
                $submenus[] = array(
                    'text' => $text,
                    'url' => 'index.php?module=booking-report&amp;status='.$type,
                );
            }
            $menu->add('report', '{LNG_Booking}', null, $submenus);
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
        $permissions['can_manage_room'] = '{LNG_Can manage room}';
        $permissions['can_approve_room'] = '{LNG_Can be approve}';

        return $permissions;
    }
}
