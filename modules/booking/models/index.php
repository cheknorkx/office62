<?php
/**
 * @filesource modules/booking/models/index.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Booking\Index;

use Gcms\Login;
use Kotchasan\Database\Sql;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * โมเดลสำหรับ (index.php).
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{
    /**
     * Query ข้อมูลสำหรับส่งให้กับ DataTable.
     *
     * @param int $member_id
     *
     * @return \Kotchasan\Database\QueryBuilder
     */
    public static function toDataTable($member_id)
    {
        $sql = Sql::create('(CASE WHEN NOW() BETWEEN V.`begin` AND V.`end` THEN 1 WHEN NOW() > V.`end` THEN 2 ELSE 0 END) AS `today`');

        return static::createQuery()
            ->select('V.id', 'V.topic', 'V.room_id', 'R.name', 'V.begin', 'V.end', 'V.status', $sql, 'R.color')
            ->from('reservation V')
            ->join('rooms R', 'INNER', array('R.id', 'V.room_id'))
            ->where(array('V.member_id', $member_id));
    }

    /**
     * รับค่าจาก action.
     *
     * @param Request $request
     */
    public function action(Request $request)
    {
        $ret = array();
        // session, referer, สมาชิก
        if ($request->initSession() && $request->isReferer()) {
            if ($login = Login::isMember()) {
                $action = $request->post('action')->toString();
                if ($action === 'cancel') {
                    // ยกเลิกการจอง
                    $reservation_table = $this->getTableName('reservation');
                    $search = $this->db()->first($reservation_table, $request->post('id')->toInt());
                    if ($search && $search->status == 0) {
                        // ลบ
                        $this->db()->delete($reservation_table, $search->id);
                        $this->db()->delete($this->getTableName('reservation_data'), array('reservation_id', $search->id), 0);
                        // คืนค่า
                        $ret['alert'] = Language::get('Canceled successfully');
                        $ret['remove'] = 'datatable_'.$search->id;
                    }
                } elseif ($action === 'detail') {
                    // แสดงรายละเอียดการจอง
                    $search = $this->bookDetail($request->post('id')->toInt());
                    if ($search) {
                        $ret['modal'] = createClass('Booking\Detail\View')->booking($search);
                    }
                }
            }
        }
        if (empty($ret)) {
            $ret['alert'] = Language::get('Unable to complete the transaction');
        }
        // คืนค่า JSON
        echo json_encode($ret);
    }

    /**
     * อ่านข้อมูลรายการที่เลือก
     * คืนค่าข้อมูล object ไม่พบคืนค่า null.
     *
     * @param int $id
     *
     * @return object|null
     */
    public function bookDetail($id)
    {
        $query = $this->db()->createQuery()
            ->from('reservation V')
            ->join('rooms R', 'INNER', array('R.id', 'V.room_id'))
            ->join('user U', 'INNER', array('U.id', 'V.member_id'))
            ->where(array('V.id', $id));
        $select = array('V.*', 'R.name', 'U.name contact', 'U.phone', 'R.color');
        $n = 1;
        foreach (Language::get('ROOM_CUSTOM_TEXT') as $key => $label) {
            $query->join('rooms_meta M'.$n, 'LEFT', array(array('M'.$n.'.room_id', 'R.id'), array('M'.$n.'.name', $key)));
            $select[] = 'M'.$n.'.value '.$key;
            ++$n;
        }
        foreach (Language::get('BOOKING_SELECT') + Language::get('BOOKING_OPTIONS') as $key => $label) {
            $query->join('reservation_data M'.$n, 'LEFT', array(array('M'.$n.'.reservation_id', 'V.id'), array('M'.$n.'.name', $key)));
            $select[] = 'M'.$n.'.value '.$key;
            ++$n;
        }

        return $query->first($select);
    }
}
