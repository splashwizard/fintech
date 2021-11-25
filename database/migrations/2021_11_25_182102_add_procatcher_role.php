<?php

use App\Business;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class AddProcatcherRole extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        $businesses = Business::get();
        foreach ($businesses as $business) {
            Role::create([
                'name' => 'Procatcher#' . $business->id,
                'business_id' => $business->id,
                'is_service_staff' => 0
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
