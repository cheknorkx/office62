<?php
/**
 * @filesource modules/booking/controllers/home.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Booking\Home;

use Gcms\Login;
use Kotchasan\Http\Request;

/**
 * module=booking-home.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{
    /**
     * ฟังก์ชั่นสร้าง card.
     *
     * @param Request         $request
     * @param \Kotchasan\Html $card
     * @param array           $login
     */
    public static function addCard(Request $request, $card, $login)
    {
        \Index\Home\Controller::renderCard($card, 'icon-calendar', '{LNG_Book a meeting}', number_format(\Booking\Home\Model::getNew()), '{LNG_Booking today}', 'index.php?module=booking-booking');
        \Index\Home\Controller::renderCard($card, 'icon-office', '{LNG_Room}', number_format(\Booking\Home\Model::rooms()), '{LNG_All meeting rooms}', 'index.php?module=booking-rooms');
    }

    /**
     * ฟังก์ชั่นสร้าง block.
     *
     * @param Request         $request
     * @param \Kotchasan\Html $block
     * @param array           $login
     */
    public static function addBlock(Request $request, $block, $login)
    {
        $content = createClass('Booking\Home\View')->render($request, $login);
        $block->set('Booking calendar', $content);
    }
}
