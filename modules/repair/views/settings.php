<?php
/**
 * @filesource modules/repair/views/settings.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Repair\Settings;

use Kotchasan\Html;
use Kotchasan\Language;

/**
 * module=repair-settings.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    /**
     * ฟอร์มตั้งค่า person.
     *
     * @return string
     */
    public function render()
    {
        // form
        $form = Html::create('form', array(
            'id' => 'setup_frm',
            'class' => 'setup_frm',
            'autocomplete' => 'off',
            'action' => 'index.php/repair/model/settings/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true,
        ));
        $fieldset = $form->add('fieldset', array(
            'title' => '{LNG_Notification}',
        ));
        // repair_send_mail
        $fieldset->add('select', array(
            'id' => 'repair_send_mail',
            'labelClass' => 'g-input icon-email',
            'itemClass' => 'item',
            'label' => '{LNG_Emailing}',
            'comment' => '{LNG_Send a notification to the Email when the transaction is made}',
            'options' => Language::get('BOOLEANS'),
            'value' => isset(self::$cfg->repair_send_mail) ? self::$cfg->repair_send_mail : 1,
        ));
        // repair_line_id
        $fieldset->add('select', array(
            'id' => 'repair_line_id',
            'itemClass' => 'item',
            'label' => '{LNG_LINE group account}',
            'labelClass' => 'g-input icon-comments',
            'comment' => '{LNG_Send notification to LINE group when making a transaction}',
            'options' => array(0 => '') + \Index\Linegroup\Model::create()->toSelect(),
            'value' => isset(self::$cfg->repair_line_id) ? self::$cfg->repair_line_id : 0,
        ));
        $fieldset = $form->add('fieldset', array(
            'class' => 'submit',
        ));
        // submit
        $fieldset->add('submit', array(
            'class' => 'button save large icon-save',
            'value' => '{LNG_Save}',
        ));

        return $form->render();
    }
}
