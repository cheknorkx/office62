<?php
/**
 * @filesource modules/repair/models/receive.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Repair\Receive;

use Gcms\Login;
use Kotchasan\Database\Sql;
use Kotchasan\File;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * เพิ่ม-แก้ไข ใบแจ้งซ่อม
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{
    /**
     * อ่านข้อมูลรายการที่เลือก
     * ถ้า $id = 0 หมายถึงรายการใหม่
     * คืนค่าข้อมูล object ไม่พบคืนค่า null.
     *
     * @param int  $id     ID
     * @param bool $new_id true คืนค่า ID ของรายการใหม่ (สำหรับการบันทึก), false คืนค่า ID หากเป็นรายการใหม่
     *
     * @return object|null
     */
    public static function get($id, $new_id = false)
    {
        $model = new static();
        if (empty($id)) {
            // ใหม่
            return (object) array(
                'equipment' => '',
                'serial' => '',
                'inventory_id' => 0,
                'job_description' => '',
                'id' => $new_id ? $model->db()->getNextId($model->getTableName('repair')) : 0,
                'comment' => '',
                'status_id' => 0,
            );
        } else {
            // แก้ไข
            $q1 = $model->db()->createQuery()
                ->select('repair_id', Sql::MAX('id', 'max_id'))
                ->from('repair_status')
                ->groupBy('repair_id');

            return $model->db()->createQuery()
                ->from('repair R')
                ->join(array($q1, 'T'), 'LEFT', array('T.repair_id', 'R.id'))
                ->join('repair_status S', 'LEFT', array('S.id', 'T.max_id'))
                ->join('inventory V', 'LEFT', array('V.id', 'R.inventory_id'))
                ->where(array('R.id', $id))
                ->first('R.*', 'V.equipment', 'V.serial', 'S.status', 'S.comment', 'S.id status_id');
        }
    }

    /**
     * บันทึกค่าจากฟอร์ม
     *
     * @param Request $request
     */
    public function submit(Request $request)
    {
        $ret = array();
        // session, token, member
        if ($request->initSession() && $request->isSafe() && $login = Login::isMember()) {
            // รับค่าจากการ POST
            $repair = array(
                'job_description' => $request->post('job_description')->textarea(),
                'inventory_id' => $request->post('inventory_id')->toInt(),
            );
            $equipment = $request->post('equipment')->topic();
            $serial = $request->post('serial')->topic();
            $id = $request->post('id')->toInt();
            // ตรวจสอบรายการที่เลือก
            $index = self::get($id, $id == 0);
            if (!$index || ($id > 0 && ($login['id'] != $index->customer_id && !Login::checkPermission($login, 'can_manage_repair')))) {
                // ไม่พบรายการที่แก้ไข
                $ret['alert'] = Language::get('Sorry, Item not found It&#39;s may be deleted');
            } elseif (empty($equipment)) {
                // equipment
                $ret['ret_equipment'] = 'Please fill in';
            } elseif (empty($serial)) {
                // serial
                $ret['ret_serial'] = 'Please fill in';
            } elseif (empty($repair['inventory_id'])) {
                // ไม่พบรายการพัสดุที่เลือก
                $ret['ret_equipment'] = Language::get('Please select from the search results');
            } else {
                // อัปโหลดไฟล์
                $dir = ROOT_PATH.DATA_FOLDER.'repair/'.$index->id.'/';
                // เวลาตอนนี้ สำหรับเป็นชื่อไฟล์
                $mktime = time();
                foreach ($request->getUploadedFiles() as $item => $file) {
                    /* @var $file \Kotchasan\Http\UploadedFile */
                    if ($file->hasUploadFile()) {
                        if (!File::makeDirectory(ROOT_PATH.DATA_FOLDER.'repair/') || !File::makeDirectory($dir)) {
                            // ไดเรคทอรี่ไม่สามารถสร้างได้
                            $ret['ret_files_tmp'] = sprintf(Language::get('Directory %s cannot be created or is read-only.'), DATA_FOLDER.'repair/'.$index->id.'/');
                        } elseif (!$file->validFileExt(self::$cfg->repair_file_typies)) {
                            // ชนิดของไฟล์ไม่ถูกต้อง
                            $ret['ret_files_tmp'] = Language::get('The type of file is invalid');
                        } else {
                            try {
                                $ext = strtolower($file->getClientFileExt());
                                while (file_exists($dir.$mktime.'.'.$ext)) {
                                    ++$mktime;
                                }
                                $file->moveTo($dir.$mktime.'.'.$ext);
                            } catch (\Exception $exc) {
                                // ไม่สามารถอัปโหลดได้
                                $ret['ret_files_tmp'] = Language::get($exc->getMessage());
                            }
                        }
                    } elseif ($file->hasError()) {
                        // ข้อผิดพลาดการอัปโหลด
                        $ret['ret_files_tmp'] = Language::get($file->getErrorMessage());
                    }
                }
                // ตาราง
                $repair_table = $this->getTableName('repair');
                $repair_status_table = $this->getTableName('repair_status');
                // Database
                $db = $this->db();
                if ($id == 0) {
                    $repair['id'] = $index->id;
                    $repair['customer_id'] = $login['id'];
                    $repair['create_date'] = date('Y-m-d H:i:s');
                    // บันทึกรายการแจ้งซ่อม
                    $db->insert($repair_table, $repair);
                    // บันทึกประวัติการทำรายการ แจ้งซ่อม
                    $db->insert($repair_status_table, array(
                        'repair_id' => $index->id,
                        'member_id' => $login['id'],
                        'comment' => $request->post('comment')->topic(),
                        'status' => isset(self::$cfg->repair_first_status) ? self::$cfg->repair_first_status : 1,
                        'create_date' => $repair['create_date'],
                        'operator_id' => 0,
                    ));
                    // ใหม่ ส่งอีเมลไปยังผู้ที่เกี่ยวข้อง
                    $err = \Repair\Email\Model::send($login['username'], $login['name'], $repair);
                    if ($err == '') {
                        $ret['alert'] = Language::get('Your message was sent successfully');
                    } else {
                        $ret['alert'] = $err;
                    }
                } else {
                    // แก้ไขรายการแจ้งซ่อม
                    $db->update($repair_table, $index->id, $repair);
                    // อัปเดทหมายเหตุ
                    $db->update($repair_status_table, $index->status_id, array(
                        'comment' => $request->post('comment')->topic(),
                    ));
                    // คืนค่า
                    $ret['alert'] = Language::get('Saved successfully');
                }
                $ret['location'] = 'index.php?module=repair-history';
                // clear
                $request->removeToken();
            }
        }
        if (empty($ret)) {
            $ret['alert'] = Language::get('Unable to complete the transaction');
        }
        // คืนค่าเป็น JSON
        echo json_encode($ret);
    }
}
