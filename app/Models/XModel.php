<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Tool\XCZ\Tool;

class XModel extends Model {

    protected $primaryKey = 'id';

    protected $condition = [];

    /**
     * Simple
     * [
     *     'where_param_1' => 'where_value_1',     #and
     *     'condition' => [                        #where fillable
     *
     *     ],
     *     'whereIn' => [                          #and
     *         [
     *             'column' => 'whereIn_column_1',
     *             'value'  => 'whereIn_value_1'
     *         ],
     *         [
     *             'column' => 'whereIn_column_2',
     *             'value'  => 'whereIn_value_2'
     *         ]
     *     ],
     *     'whereNotIn' => [                       #and
     *         [
     *             'column' => 'whereNotIn_column_1',
     *             'value'  => 'whereNotIn_value_1'
     *         ],
     *         [
     *             'column' => 'whereNotIn_column_2',
     *             'value'  => 'whereNotIn_value_2'
     *         ]
     *     ],
     *     'whereBetween' => [                     #and
     *         [
     *             'column' => 'whereBetween_column_1',
     *             'value'  => 'whereBetween_value_1'
     *         ],
     *         [
     *             'column' => 'whereBetween_column_2',
     *             'value'  => 'whereBetween_value_2'
     *         ]
     *     ],
     *     'search' => [                           #and
     *         'column' => 'search_column',
     *         'value' => 'search_value'
     *     ],
     *     //orderBy                               #and
     *     'order_by_id' => '1,2,3',               #or
     *     'order' => 'column',                    #or
     *     'sort' => 'sort',
     * ]
     */
    public function scopeSimple($query, $input){

        $condition = isset($input['condition']) ? $input['condition'] : $this->condition;

        $where = self::paramFormat($input, $condition);

        if(!isset($where['state'])) {
            $where['state'] = 1;
        }

        $query->where($where);

        if(isset($input['whereIn'])) {

            foreach($input['whereIn'] as $each) {

                if(!isset($each['column']) || !isset($each['value'])) {
                    continue;
                }

                $query->whereIn($each['column'], $each['value']);
            }
        }

        if(isset($input['whereNotIn'])) {

            foreach($input['whereNotIn'] as $each) {

                if(!isset($each['column']) || !isset($each['value'])) {
                    continue;
                }

                $query->whereNotIn($each['column'], $each['value']);
            }
        }

        if(isset($input['whereBetween']) && is_array($input['whereBetween'])) {

            foreach($input['whereBetween'] as $each) {

                if(!isset($each['column']) || !isset($each['value'])) {
                    continue;
                }

                $query->whereBetween($each['column'], $each['value']);
            }
        }

        //搜索

        if(isset($input['search']) ) {

            $query->where($input['search']['column'], 'like', '%' . $input['search']['value'] . '%');
        }

        //排序
        if(
            isset($input['order_by_id']) &&
            is_array($input['order_by_id']) &&
            count($input['order_by_id']) > 0
        ){

            $query->orderBy(DB::raw('FIELD(' . $this->primaryKey . ','. implode(',', $input['order_by_id']).')'));
        }else {

            $order = isset($input['order']) ? $input['order'] : 'updated_at';
            $sort  = isset($input['sort']) ? $input['sort'] : 'desc';

            if(is_array($order)) {

                foreach($order as $each) {

                    $query->orderBy($each[0], $each[1]);
                }
            }else if(isset($input['use_order'])){

                $query->orderBy($order, $sort);
            }
        }

        return $query;
    }


    public function scopeXpluck($query, $column) {

        return $query->pluck($column)->toArray();
    }

    public function scopeXget($query, $toArray = 0) {

        if($toArray == 0) {
            return $query->get();
        }

        return $query->get()->toArray();
    }

    private static function paramFormat($input, $condition) {

        $arr = [];

        foreach($input as $k=>$v){

            if(in_array($k, $condition)) {
                $arr[$k] = $v;
            }

        }

        return $arr;
    }
}
