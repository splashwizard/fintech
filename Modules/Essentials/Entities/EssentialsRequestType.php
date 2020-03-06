<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;

class EssentialsRequestType extends Model
{
    protected $fillable = [];
    protected $guarded = ['id'];

    public static function forDropdown($business_id)
    {
        $leave_types = EssentialsLeaveType::where('business_id', $business_id)
            ->pluck('leave_type', 'id');

        return $leave_types;
    }
}
