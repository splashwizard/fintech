<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NewTransactionWithdraw extends Model
{
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
//    protected $dates = ['created_at', 'deleted_at'];
    
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];
}
