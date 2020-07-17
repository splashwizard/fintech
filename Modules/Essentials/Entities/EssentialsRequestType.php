<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;

class EssentialsRequestType extends Model
{
    protected $fillable = [];
    protected $guarded = ['id'];

    public static function forDropdown($business_id, $pos_type = 'pos')
    {
        $leave_types = EssentialsRequestType::where('business_id', $business_id);
        if($pos_type != 'pos')
            $leave_types->where('id', 2);
        else
            $leave_types->where('id', '!=', 3);
        $leave_types = $leave_types->pluck('request_type', 'id');

        return $leave_types;
    }
}
