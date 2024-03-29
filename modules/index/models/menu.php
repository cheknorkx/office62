<?php
/**
 * @filesource modules/index/models/menu.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Index\Menu;

use Gcms\Login;

/**
 * รายการเมนู.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model
{
    /**
     * รายการเมนู.
     *
     * @param array $login
     *
     * @return array
     */
    public static function getMenus($login)
    {
        // เมนูตั้งค่า
        $settings = array();
        if (Login::checkPermission($login, 'can_config')) {
            // สามารถตั้งค่าระบบได้
            $settings[] = array(
                'text' => '{LNG_Site settings}',
                'url' => 'index.php?module=system',
            );
            $settings[] = array(
                'text' => '{LNG_Email settings}',
                'url' => 'index.php?module=mailserver',
            );
            $settings[] = array(
                'text' => '{LNG_Member status}',
                'url' => 'index.php?module=memberstatus',
            );
            $settings[] = array(
                'text' => '{LNG_LINE group account}',
                'url' => 'index.php?module=lines',
            );
            $settings[] = array(
                'text' => '{LNG_Language}',
                'url' => 'index.php?module=language',
            );
            $settings[] = array(
                'text' => '{LNG_Position}',
                'url' => 'index.php?module=index-categories&amp;type=position',
            );
            $settings[] = array(
                'text' => '{LNG_Department}',
                'url' => 'index.php?module=index-categories&amp;type=department',
            );
        }
        // เมนูหลัก
        $menus = array(
            'home' => array(
                'text' => '{LNG_Home}',
                'url' => 'index.php?module=home',
            ),
            'module' => array(
                'text' => '{LNG_Module}',
                'submenus' => array(),
            ),
            'member' => array(
                'text' => '{LNG_Users}',
                'submenus' => array(
                    array(
                        'text' => '{LNG_Member list}',
                        'url' => 'index.php?module=member',
                    ),
                    array(
                        'text' => '{LNG_Register}',
                        'url' => 'index.php?module=register',
                    ),
                ),
            ),
            'report' => array(
                'text' => '{LNG_Report}',
                'submenus' => array(),
            ),
            'settings' => array(
                'text' => '{LNG_Settings}',
                'submenus' => $settings,
            ),
        );
        if ($login) {
            $menus['signout'] = array(
                'text' => '{LNG_Sign out}',
                'url' => 'index.php?action=logout',
            );
        } else {
            $menus['signin'] = array(
                'text' => '{LNG_Sign in}',
                'url' => 'index.php?module=welcome&amp;action=login',
            );
        }

        return $menus;
    }
}
