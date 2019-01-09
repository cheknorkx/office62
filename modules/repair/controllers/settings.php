<?php
/**
 * @filesource modules/repair/controllers/settings.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Repair\Settings;

use Gcms\Login;
use Kotchasan\Html;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=repair-settings.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{

  /**
   * ตั้งค่าโมดูล Repair.
   *
   * @param Request $request
   *
   * @return string
   */
  public function render(Request $request)
  {
    // ข้อความ title bar
    $this->title = Language::trans('{LNG_Module settings} {LNG_Repair}');
    // เลือกเมนู
    $this->menu = 'settings';
    // สามารถตั้งค่าระบบได้
    if (Login::checkPermission(Login::isMember(), 'can_config')) {
      // แสดงผล
      $section = Html::create('section', array(
          'class' => 'content_bg',
      ));
      // breadcrumbs
      $breadcrumbs = $section->add('div', array(
        'class' => 'breadcrumbs',
      ));
      $ul = $breadcrumbs->add('ul');
      $ul->appendChild('<li><span class="icon-settings">{LNG_Settings}</span></li>');
      $ul->appendChild('<li><span>{LNG_Repair}</span></li>');
      $section->add('header', array(
        'innerHTML' => '<h2 class="icon-product">'.$this->title.'</h2>',
      ));
      // แสดงฟอร์ม
      $section->appendChild(createClass('Repair\Settings\View')->render());

      return $section->render();
    }
    // 404

    return \Index\Error\Controller::execute($this);
  }
}