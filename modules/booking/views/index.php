<?php
/**
 * @filesource modules/booking/views/index.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Booking\Index;

use Gcms\Login;
use Kotchasan\DataTable;
use Kotchasan\Date;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=booking-index.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    /**
     * @var array
     */
    private $status;

    /**
     * ตารางรายชื่อสมาชิก
     *
     * @param Request $request
     * @param array   $login
     *
     * @return string
     */
    public function render(Request $request, $login)
    {
        $this->status = Language::get('BOOKING_STATUS');
        // URL สำหรับส่งให้ตาราง
        $uri = $request->createUriWithGlobals(WEB_URL.'index.php');
        // ตาราง
        $table = new DataTable(array(
            /* Uri */
            'uri' => $uri,
            /* Model */
            'model' => \Booking\Index\Model::toDataTable($login['id']),
            /* รายการต่อหน้า */
            'perPage' => $request->cookie('booking_perPage', 30)->toInt(),
            /* เรียงลำดับ */
            'sort' => 'begin DESC',
            /* ฟังก์ชั่นจัดรูปแบบการแสดงผลแถวของตาราง */
            'onRow' => array($this, 'onRow'),
            /* คอลัมน์ที่ไม่ต้องแสดงผล */
            'hideColumns' => array('id', 'today', 'color'),
            /* คอลัมน์ที่สามารถค้นหาได้ */
            'searchColumns' => array('name', 'topic'),
            /* ตั้งค่าการกระทำของของตัวเลือกต่างๆ ด้านล่างตาราง ซึ่งจะใช้ร่วมกับการขีดถูกเลือกแถว */
            'action' => 'index.php/booking/model/index/action',
            'actionCallback' => 'dataTableActionCallback',
            /* ส่วนหัวของตาราง และการเรียงลำดับ (thead) */
            'headers' => array(
                'topic' => array(
                    'text' => '{LNG_Topic}',
                ),
                'room_id' => array(
                    'text' => '{LNG_Image}',
                    'class' => 'center',
                ),
                'name' => array(
                    'text' => '{LNG_Room name}',
                ),
                'begin' => array(
                    'text' => '{LNG_Begin date}',
                    'class' => 'center',
                ),
                'end' => array(
                    'text' => '{LNG_End date}',
                    'class' => 'center',
                ),
                'status' => array(
                    'text' => '{LNG_Status}',
                    'class' => 'center',
                ),
                'reason' => array(
                    'text' => '{LNG_Reason}',
                ),
            ),
            /* รูปแบบการแสดงผลของคอลัมน์ (tbody) */
            'cols' => array(
                'room_id' => array(
                    'class' => 'center',
                ),
                'begin' => array(
                    'class' => 'center',
                ),
                'end' => array(
                    'class' => 'center',
                ),
                'status' => array(
                    'class' => 'center',
                ),
            ),
            /* ฟังก์ชั่นตรวจสอบการแสดงผลปุ่มในแถว */
            'onCreateButton' => array($this, 'onCreateButton'),
            /* ปุ่มแสดงในแต่ละแถว */
            'buttons' => array(
                'cancel' => array(
                    'class' => 'icon-warning button cancel',
                    'id' => ':id',
                    'text' => '{LNG_Cancel}',
                ),
                'edit' => array(
                    'class' => 'icon-edit button green',
                    'href' => $uri->createBackUri(array('module' => 'booking-booking', 'id' => ':id')),
                    'text' => '{LNG_Edit}',
                ),
                'detail' => array(
                    'class' => 'icon-info button orange',
                    'id' => ':id',
                    'text' => '{LNG_Detail}',
                ),
            ),
        ));
        // save cookie
        setcookie('booking_perPage', $table->perPage, time() + 2592000, '/', HOST, HTTPS, true);

        return $table->render();
    }

    /**
     * จัดรูปแบบการแสดงผลในแต่ละแถว.
     *
     * @param array  $item ข้อมูลแถว
     * @param int    $o    ID ของข้อมูล
     * @param object $prop กำหนด properties ของ TR
     *
     * @return array
     */
    public function onRow($item, $o, $prop)
    {
        if ($item['today'] == 1) {
            $prop->class = 'bg3';
        }
        $thumb = is_file(ROOT_PATH.DATA_FOLDER.'booking/'.$item['room_id'].'.jpg') ? WEB_URL.DATA_FOLDER.'booking/'.$item['room_id'].'.jpg' : WEB_URL.'modules/booking/img/noimage.png';
        $item['room_id'] = '<img src="'.$thumb.'" style="max-height:50px;max-width:50px" alt=thumbnail>';
        $item['status'] = '<span class="term'.$item['status'].'">'.$this->status[$item['status']].'</span>';
        $item['begin'] = Date::format($item['begin']);
        $item['end'] = Date::format($item['end']);
        $item['name'] = '<span class="term" style="background-color:'.$item['color'].'">'.$item['name'].'</span>';

        return $item;
    }

    /**
     * ฟังกชั่นตรวจสอบว่าสามารถสร้างปุ่มได้หรือไม่.
     *
     * @param array $item
     *
     * @return array
     */
    public function onCreateButton($btn, $attributes, $items)
    {
        if ($btn == 'edit') {
            return $items['status'] == 0 && $items['today'] == 0 ? $attributes : false;
        } elseif ($btn == 'cancel') {
            return $items['status'] == 0 ? $attributes : false;
        } else {
            return $attributes;
        }
    }
}
