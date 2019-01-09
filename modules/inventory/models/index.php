<?php
/**
 * @filesource modules/inventory/models/index.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Inventory\Index;

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
    $select = array(
      'R.*',
    );
    $query = static::createQuery()
      ->from('inventory R')
      ->where(array('R.member_id', $member_id));
    $n = 1;
    foreach (Language::get('INVENTORY_CATEGORIES') as $type => $text) {
      $query->join('inventory_meta M'.$n, 'LEFT', array(array('M'.$n.'.inventory_id', 'R.id'), array('M'.$n.'.name', $type)));
      $select[] = 'M'.$n.'.value '.$type;
      ++$n;
    }

    return $query->select($select);
  }
}