<?php
/**
 * @filesource modules/edocument/views/sent.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Edocument\Sent;

use Gcms\Login;
use Kotchasan\DataTable;
use Kotchasan\Date;
use Kotchasan\Http\Request;
use Kotchasan\Text;

/**
 * module=edocument-sent.
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
    private $sender;

    /**
     * แสดงรายการเอกสารส่ง.
     *
     * @param Request $request
     * @param array   $login
     *
     * @return string
     */
    public function render(Request $request, $login)
    {
        if (Login::checkPermission($login, 'can_handle_all_edocument')) {
            $sender_id = $request->request('sender_id')->toInt();
            $sender = 0;
        } else {
            $sender_id = (int) $login['id'];
            $sender = $sender_id;
        }
        // ผู้ส่ง
        $this->sender = \Edocument\Sender\Model::init($sender);
        // URL สำหรับส่งให้ตาราง
        $uri = $request->createUriWithGlobals(WEB_URL.'index.php');
        // ตาราง
        $table = new DataTable(array(
            /* Uri */
            'uri' => $uri,
            /* Model */
            'model' => \Edocument\Sent\Model::toDataTable(),
            /* รายการต่อหน้า */
            'perPage' => $request->cookie('edocument_perPage', 30)->toInt(),
            /* เรียงลำดับ */
            'sort' => 'last_update DESC',
            /* ฟังก์ชั่นจัดรูปแบบการแสดงผลแถวของตาราง */
            'onRow' => array($this, 'onRow'),
            /* คอลัมน์ที่ไม่ต้องแสดงผล */
            'hideColumns' => array('id'),
            /* ตัวเลือกการแสดงผลที่ส่วนหัว */
            'filters' => array(
                'sender_id' => array(
                    'name' => 'sender_id',
                    'text' => '{LNG_Sender}',
                    'options' => $sender == 0 ? array(0 => '{LNG_all items}') + $this->sender->toSelect() : $this->sender->toSelect(),
                    'default' => 0,
                    'value' => $sender_id,
                ),
            ),
            /* ตั้งค่าการกระทำของของตัวเลือกต่างๆ ด้านล่างตาราง ซึ่งจะใช้ร่วมกับการขีดถูกเลือกแถว */
            'action' => 'index.php/edocument/model/sent/action',
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
                array(
                    'class' => 'button green icon-plus',
                    'href' => $uri->createBackUri(array('module' => 'edocument-write')),
                    'text' => '{LNG_Send Document}',
                ),
            ),
            /* คอลัมน์ที่สามารถค้นหาได้ */
            'searchColumns' => array('topic', 'document_no'),
            /* ส่วนหัวของตาราง และการเรียงลำดับ (thead) */
            'headers' => array(
                'document_no' => array(
                    'text' => '{LNG_Document No.}',
                ),
                'ext' => array(
                    'text' => '',
                ),
                'topic' => array(
                    'text' => '{LNG_File Name}',
                ),
                'sender_id' => array(
                    'text' => '{LNG_Sender}',
                    'class' => 'center',
                ),
                'size' => array(
                    'text' => '{LNG_size of} {LNG_File}',
                    'class' => 'center',
                ),
                'last_update' => array(
                    'text' => '{LNG_date}',
                    'class' => 'center',
                ),
                'downloads' => array(
                    'text' => '{LNG_Download}',
                    'class' => 'center',
                ),
            ),
            /* รูปแบบการแสดงผลของคอลัมน์ (tbody) */
            'cols' => array(
                'ext' => array(
                    'class' => 'center',
                ),
                'sender_id' => array(
                    'class' => 'center',
                ),
                'size' => array(
                    'class' => 'center',
                ),
                'last_update' => array(
                    'class' => 'center nowrap',
                ),
                'downloads' => array(
                    'class' => 'center visited',
                ),
            ),
            /* ปุ่มแสดงในแต่ละแถว */
            'buttons' => array(
                'download' => array(
                    'class' => 'icon-download button purple notext',
                    'id' => ':id',
                    'title' => '{LNG_Download}',
                ),
                'report' => array(
                    'class' => 'icon-report button orange notext',
                    'href' => $uri->createBackUri(array('module' => 'edocument-report', 'id' => ':id')),
                    'title' => '{LNG_Download history}',
                ),
                'edit' => array(
                    'class' => 'icon-edit button green notext',
                    'href' => $uri->createBackUri(array('module' => 'edocument-write', 'id' => ':id')),
                    'title' => '{LNG_Edit}',
                ),
            ),
        ));
        // save cookie
        setcookie('edocument_perPage', $table->perPage, time() + 2592000, '/', HOST, HTTPS, true);

        return $table->render();
    }

    /**
     * จัดรูปแบบการแสดงผลในแต่ละแถว.
     *
     * @param array $item
     *
     * @return array
     */
    public function onRow($item, $o, $prop)
    {
        $item['sender_id'] = $this->sender->get($item['sender_id']);
        $item['topic'] = $item['topic'].'.'.$item['ext'];
        $item['downloads'] = '<span id=downloads_'.$item['id'].'>'.(int) $item['downloads'].'</span>';
        $item['size'] = Text::formatFileSize($item['size']);
        $item['last_update'] = Date::format($item['last_update']);
        $item['ext'] = '<img src="'.(is_file(ROOT_PATH.'skin/ext/'.$item['ext'].'.png') ? WEB_URL.'skin/ext/'.$item['ext'].'.png' : WEB_URL.'skin/ext/file.png').'" alt="'.$item['ext'].'">';

        return $item;
    }
}
