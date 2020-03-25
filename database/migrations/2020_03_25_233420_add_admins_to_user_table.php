<?php

use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdminsToUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user', function (Blueprint $table) {
            //
            $user_details['first_name'] = 'Steven';
            $user_details['username'] = 'Steven Admin';
            $user_details['password'] = Hash::make('steven');
            $user = User::create($user_details);
            $user->assignRole('Admin');
            //
            $user_details['first_name'] = 'David';
            $user_details['username'] = 'David';
            $user_details['password'] = Hash::make('david');
            $user = User::create($user_details);
            $user->assignRole('Admin');
            //
            $user_details['first_name'] = 'Anson';
            $user_details['username'] = 'Anson';
            $user_details['password'] = Hash::make('anson');
            $user = User::create($user_details);
            $user->assignRole('Admin');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user', function (Blueprint $table) {
            //
        });
    }
}
