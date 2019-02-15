<?php
/**
 * @filesource modules/booking/models/home.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Booking\Home;

use Kotchasan\Database\Sql;

/**
 * โมเดลสำหรับอ่านข้อมูลแสดงในหน้า  Home.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{
    /**
     * อ่านรายการจองวันนี้.
     *
     * @return object
     */
    public static function getNew($login)
    {
        $search = static::createQuery()
            ->selectCount()
            ->from('reservation')
            ->where(array(
                array('status', 1),
                Sql::BETWEEN(date('Y-m-d'), Sql::DATE('begin'), Sql::DATE('end')),
            ))
            ->toArray()
            ->execute();
        if (!empty($search)) {
            return $search[0]['count'];
        }

        return 0;
    }
}
