<?php
/**
 * @filesource modules/booking/views/report.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Booking\Report;

use Kotchasan\DataTable;
use Kotchasan\Date;
use Kotchasan\Http\Request;

/**
 * module=booking-report.
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
     * @param object  $index
     *
     * @return string
     */
    public function render(Request $request, $index)
    {
        // ค่าที่ส่งมา
        $params = array(
            'room_id' => $request->request('room_id')->toInt(),
            'status' => $index->status,
        );
        // URL สำหรับส่งให้ตาราง
        $uri = $request->createUriWithGlobals(WEB_URL.'index.php');
        // ตาราง
        $table = new DataTable(array(
            /* Uri */
            'uri' => $uri,
            /* Model */
            'model' => \Booking\Report\Model::toDataTable($params),
            /* รายการต่อหน้า */
            'perPage' => $request->cookie('booking_report_perPage', 30)->toInt(),
            /* เรียงลำดับ */
            'sort' => $request->cookie('booking_report_sort', 'create_date')->toString(),
            /* ฟังก์ชั่นจัดรูปแบบการแสดงผลแถวของตาราง */
            'onRow' => array($this, 'onRow'),
            /* คอลัมน์ที่ไม่ต้องแสดงผล */
            'hideColumns' => array('id', 'today', 'end'),
            /* คอลัมน์ที่สามารถค้นหาได้ */
            'searchColumns' => array('name', 'contact', 'phone'),
            /* ตั้งค่าการกระทำของของตัวเลือกต่างๆ ด้านล่างตาราง ซึ่งจะใช้ร่วมกับการขีดถูกเลือกแถว */
            'action' => 'index.php/booking/model/report/action',
            'actionCallback' => 'dataTableActionCallback',
            'actions' => array(
                array(
                    'id' => 'action',
                    'class' => 'ok',
                    'text' => '{LNG_With selected}',
                    'options' => array(
                        'delete' => '{LNG_Delete}',
                    ),
                ),
            ),
            /* ตัวเลือกด้านบนของตาราง ใช้จำกัดผลลัพท์การ query */
            'filters' => array(
                array(
                    'name' => 'room_id',
                    'default' => 0,
                    'text' => '{LNG_Room}',
                    'options' => array(0 => '{LNG_all items}')+\Booking\Rooms\Model::toSelect(),
                    'value' => $params['room_id'],
                ),
            ),
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
                    'sort' => 'name',
                ),
                'contact' => array(
                    'text' => '{LNG_Contact name}',
                ),
                'phone' => array(
                    'text' => '{LNG_Phone}',
                    'class' => 'center',
                ),
                'begin' => array(
                    'text' => '{LNG_Date}',
                    'class' => 'center',
                    'sort' => 'begin',
                ),
                'create_date' => array(
                    'text' => '{LNG_Created}',
                    'class' => 'center',
                    'sort' => 'create_date',
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
                'phone' => array(
                    'class' => 'center',
                ),
                'begin' => array(
                    'class' => 'center',
                ),
                'end' => array(
                    'class' => 'center',
                ),
                'create_date' => array(
                    'class' => 'center',
                ),
            ),
            /* ฟังก์ชั่นตรวจสอบการแสดงผลปุ่มในแถว */
            'onCreateButton' => array($this, 'onCreateButton'),
            /* ปุ่มแสดงในแต่ละแถว */
            'buttons' => array(
                'edit' => array(
                    'class' => 'icon-edit button green',
                    'href' => $uri->createBackUri(array('module' => 'booking-order', 'id' => ':id')),
                    'text' => '{LNG_Edit}',
                ),
            ),
        ));
        // save cookie
        setcookie('booking_report_perPage', $table->perPage, time() + 2592000, '/', HOST, HTTPS, true);
        setcookie('booking_report_sort', $table->sort, time() + 2592000, '/', HOST, HTTPS, true);

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
        $item['phone'] = '<a href="tel:'.$item['phone'].'">'.$item['phone'].'</a>';
        $item['begin'] = Date::format($item['begin']).' - '.Date::format($item['end']);
        $item['create_date'] = Date::format($item['create_date']);

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
        if ($btn == 'edit' && in_array($items['today'], array(1, 2))) {
            return false;
        } else {
            return $attributes;
        }
    }
}
