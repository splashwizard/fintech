<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdminHasBusiness extends Model
{
    //
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'admin_has_business';
    protected $guarded = ['id'];
}
