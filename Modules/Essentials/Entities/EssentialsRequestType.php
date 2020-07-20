<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;

class EssentialsRequestType extends Model
{
    protected $fillable = [];
    protected $guarded = ['id'];

    public static function forDropdown($business_id, $pos_type = 'unclaimed')
    {
        $leave_types = EssentialsRequestType::where('business_id', $business_id);
        $leave_types->where('request_type', 'Request to delete');
        if($pos_type == 'deposit' || $pos_type == 'withdraw')
            $leave_types->orWhere('request_type', 'Changes for Deposit/Withdraw');
        else if($pos_type == 'GTransfer')
            $leave_types->orWhere('request_type', 'Changes for Gtransfer');
        else if($pos_type == 'Deduction')
            $leave_types->orWhere('request_type', 'Changes for Deduction');
        $leave_types = $leave_types->pluck('request_type', 'id');

        return $leave_types;
    }
}
