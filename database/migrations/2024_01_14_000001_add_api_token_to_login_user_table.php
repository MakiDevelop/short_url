<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddApiTokenToLoginUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('login_user', function (Blueprint $table) {
            $table->string('api_token', 64)->nullable()->unique()->after('oauth_last_login');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('login_user', function (Blueprint $table) {
            $table->dropColumn('api_token');
        });
    }
}
