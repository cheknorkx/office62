<?php
/**
 * @filesource modules/inventory/models/setup.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Inventory\Setup;

use Gcms\Login;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * โมเดลสำหรับ (setup.php).
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
     * @return \Kotchasan\Database\QueryBuilder
     */
    public static function toDataTable()
    {
        $select = array(
            'R.*',
        );
        $query = static::createQuery()
            ->from('inventory R')
            ->join('user U', 'LEFT', array('U.id', 'R.member_id'));
        $n = 1;
        foreach (Language::get('INVENTORY_CATEGORIES') as $type => $text) {
            $query->join('inventory_meta M'.$n, 'LEFT', array(array('M'.$n.'.inventory_id', 'R.id'), array('M'.$n.'.name', $type)));
            $select[] = 'M'.$n.'.value '.$type;
            ++$n;
        }
        $select[] = 'U.name device_user';

        return $query->select($select);
    }

    /**
     * รับค่าจาก action.
     *
     * @param Request $request
     */
    public function action(Request $request)
    {
        $ret = array();
        // session, referer, can_manage_inventory
        if ($request->initSession() && $request->isReferer() && $login = Login::isMember()) {
            if (Login::notDemoMode($login) && Login::checkPermission($login, 'can_manage_inventory')) {
                // รับค่าจากการ POST
                $action = $request->post('action')->toString();
                // ตาราง inventory
                $table = $this->getTableName('inventory');
                // id ที่ส่งมา
                if (preg_match_all('/,?([0-9]+),?/', $request->post('id')->toString(), $match)) {
                    if ($action === 'delete') {
                        // ลบ
                        $this->db()->delete($table, array('id', $match[1]), 0);
                        $this->db()->delete($this->getTableName('inventory_meta'), array('inventory_id', $match[1]), 0);
                        // reload
                        $ret['location'] = 'reload';
                    } elseif ($action == 'status') {
                        // สถานะ
                        $search = $this->db()->first($table, (int) $match[1][0]);
                        if ($search) {
                            $status = $search->status == 1 ? 0 : 1;
                            $this->db()->update($table, $search->id, array('status' => $status));
                            // คืนค่า
                            $ret['elem'] = 'status_'.$search->id;
                            $lng = Language::get('INVENTORY_STATUS');
                            $ret['title'] = $lng[$status];
                            $ret['class'] = 'icon-valid '.($status == 0 ? 'disabled' : 'access');
                        }
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
}
