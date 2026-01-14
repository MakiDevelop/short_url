<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixUserAgentColumnName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('click_log', function (Blueprint $table) {
            $table->renameColumn('user_agenet', 'user_agent');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('click_log', function (Blueprint $table) {
            $table->renameColumn('user_agent', 'user_agenet');
        });
    }
}
