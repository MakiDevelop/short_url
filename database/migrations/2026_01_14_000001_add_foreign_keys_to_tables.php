<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // First, change column types to match (unsigned)
        Schema::table('click_log', function (Blueprint $table) {
            $table->unsignedBigInteger('us_id')->change();
        });

        Schema::table('hash_tags', function (Blueprint $table) {
            $table->unsignedBigInteger('us_id')->change();
        });

        // Add foreign key to click_log table
        Schema::table('click_log', function (Blueprint $table) {
            $table->foreign('us_id')
                ->references('id')
                ->on('url_shortener')
                ->onDelete('cascade');
        });

        // Add foreign key to hash_tags table
        Schema::table('hash_tags', function (Blueprint $table) {
            $table->foreign('us_id')
                ->references('id')
                ->on('url_shortener')
                ->onDelete('cascade');
        });

        // Note: url_shortener.lu_id is not constrained because lu_id=0 means anonymous user
        // If you want to add it later, ensure all lu_id=0 records are handled first
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('click_log', function (Blueprint $table) {
            $table->dropForeign(['us_id']);
        });

        Schema::table('hash_tags', function (Blueprint $table) {
            $table->dropForeign(['us_id']);
        });
    }
}
