<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class ConnectedKiosk extends Model
{

    protected $guarded = ['id'];

    public static function forDropdown($prepend_none = true, $prepend_all = false)
    {
        $all_cg = ConnectedKiosk::pluck('name', 'id');

        //Prepend none
        if ($prepend_none) {
            $all_cg = $all_cg->prepend("Add a custom kiosk", 0);
        }

        //Prepend none
        if ($prepend_all) {
            $all_cg = $all_cg->prepend(__("report.all"), '');
        }

        return $all_cg;
    }
}
