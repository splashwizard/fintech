<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PromotionCollection extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Return list of customer group for a business
     *
     * @param $business_id int
     * @param $prepend_none = true (boolean)
     * @param $prepend_all = false (boolean)
     *
     * @return array
     */
    public static function forDropdown($prepend_none = true, $prepend_all = false)
    {
        $all_cg = PromotionCollection::pluck('name', 'id');

        //Prepend none
        if ($prepend_none) {
            $all_cg = $all_cg->prepend(__("lang_v1.none"), '');
        }

        //Prepend none
        if ($prepend_all) {
            $all_cg = $all_cg->prepend(__("report.all"), '');
        }

        
        return $all_cg;
    }
}
