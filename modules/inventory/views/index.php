<?php
/**
 * @filesource modules/inventory/views/index.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Inventory\Index;

use Gcms\Login;
use Kotchasan\DataTable;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=inventory.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    /**
     * @var mixed
     */
    private $params;
    /**
     * @var mixed
     */
    private $category;
    /**
     * @var mixed
     */
    private $inventory_status;

    /**
     * ตารางอุปกรณ์ของฉัน.
     *
     * @param Request $request
     * @param array   $login
     *
     * @return string
     */
    public function render(Request $request, $login)
    {
        $this->inventory_status = Language::get('INVENTORY_STATUS');
        $fields = array('equipment', 'serial');
        $headers = array(
            'equipment' => array(
                'text' => '{LNG_Equipment}',
                'sort' => 'equipment',
            ),
            'serial' => array(
                'text' => '{LNG_Serial/Registration number}',
                'sort' => 'serial',
            ),
        );
        $cols = array();
        $filters = array();
        $this->category = \Inventory\Category\Model::init();
        foreach (Language::get('INVENTORY_CATEGORIES') as $type => $text) {
            $fields[] = $type;
            $headers[$type] = array(
                'text' => $text,
                'class' => 'center',
            );
            $cols[$type] = array('class' => 'center');
            $this->params[] = $type;
            $filters[$type] = array(
                'name' => $type,
                'default' => 0,
                'text' => $text,
                'options' => array(0 => '{LNG_all items}') + $this->category->toSelect($type),
                'value' => $request->request($type)->toInt(),
            );
        }
        $fields[] = 'id';
        $headers['id'] = array(
            'text' => '{LNG_Image}',
            'class' => 'center',
        );
        $cols['id'] = array('class' => 'center');
        $fields[] = 'status';
        $headers['status'] = array(
            'text' => '{LNG_Status}',
            'class' => 'center',
        );
        $cols['status'] = array('class' => 'center');
        // URL สำหรับส่งให้ตาราง
        $uri = $request->createUriWithGlobals(WEB_URL.'index.php');
        // ตาราง
        $table = new DataTable(array(
            /* Uri */
            'uri' => $uri,
            /* Model */
            'model' => \Inventory\Index\Model::toDataTable($login['id']),
            /* ฟิลด์ที่กำหนด (หากแตกต่างจาก Model) */
            'fields' => $fields,
            /* รายการต่อหน้า */
            'perPage' => $request->cookie('inventory_perPage', 30)->toInt(),
            /* เรียงลำดับ */
            'sort' => $request->cookie('inventory_sort', 'id desc')->toString(),
            /* ฟังก์ชั่นจัดรูปแบบการแสดงผลแถวของตาราง */
            'onRow' => array($this, 'onRow'),
            /* คอลัมน์ที่สามารถค้นหาได้ */
            'searchColumns' => array('equipment', 'serial'),
            /* ตัวเลือกด้านบนของตาราง ใช้จำกัดผลลัพท์การ query */
            'filters' => $filters,
            /* ส่วนหัวของตาราง และการเรียงลำดับ (thead) */
            'headers' => $headers,
            /* รูปแบบการแสดงผลของคอลัมน์ (tbody) */
            'cols' => $cols,
        ));
        // save cookie
        setcookie('inventory_perPage', $table->perPage, time() + 2592000, '/', HOST, HTTPS, true);
        setcookie('inventory_sort', $table->sort, time() + 2592000, '/', HOST, HTTPS, true);

        return $table->render();
    }

    /**
     * จัดรูปแบบการแสดงผลในแต่ละแถว.
     *
     * @param array  $item ข้อมูลแถว
     * @param int    $o    ID ของข้อมูล
     * @param object $prop กำหนด properties ของ TR
     *
     * @return array คืนค่า $item กลับไป
     */
    public function onRow($item, $o, $prop)
    {
        foreach ($this->params as $key) {
            $item[$key] = $this->category->get($key, $item[$key]);
        }
        $item['status'] = '<span class="icon-valid '.($item['status'] == 0 ? 'disabled' : 'access').'" title="'.$this->inventory_status[$item['status']].'"></span>';
        $thumb = is_file(ROOT_PATH.DATA_FOLDER.'inventory/'.$item['id'].'.jpg') ? WEB_URL.DATA_FOLDER.'inventory/'.$item['id'].'.jpg' : WEB_URL.'modules/inventory/img/noimage.png';
        $item['id'] = '<img src="'.$thumb.'" style="max-height:50px;max-width:50px" alt=thumbnail>';

        return $item;
    }
}
