<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixUserAgentColumnName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Use CHANGE COLUMN syntax for MySQL 5.7 compatibility
        DB::statement('ALTER TABLE click_log CHANGE COLUMN user_agenet user_agent TEXT');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE click_log CHANGE COLUMN user_agent user_agenet TEXT');
    }
}
