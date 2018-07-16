<?php

namespace App\Models\Traits;

/**
 * It was shown to me, that Laravel's Eloquent and even Query Builder cannot
 * do some certain things... That's why this trait exists.
 */
trait DBAdvancedTrait
{
    /**
     * a bulk insert or update on duplicate in 1 sql. Like thousands of rows.
     * Be careful, no prepared statements used.
     *
     * insertOrUpdate([
     *   ['id'=>1,'title'=>'aaa'],
     *   ['id'=>2,'title'=>'bbb'],
     * ]);
     */
    public static function insertOrUpdate(array $rows)
    {
        $table = \DB::getTablePrefix().with(new self)->getTable();
        if (count($rows) === 0) {
            return true;
        }
        $first = reset($rows);
        $column_names_arr = array_keys($first);
        $columns = implode(',', $column_names_arr);
        $values = implode(',',
            array_map(function($row) {
                return '(' . implode(',',
                    array_map(function($value) {
                        return '"'.str_replace('"', '""', $value).'"';
                    } , $row)
                ) . ')';
            } , $rows)
        );
        $updates = implode(',',
            array_map(function($value) {
                return "$value = VALUES($value)";
            } , $column_names_arr)
        );
        $sql = "INSERT INTO {$table}({$columns}) VALUES {$values} ON DUPLICATE KEY UPDATE {$updates}";
        return \DB::statement($sql);
    }
}
