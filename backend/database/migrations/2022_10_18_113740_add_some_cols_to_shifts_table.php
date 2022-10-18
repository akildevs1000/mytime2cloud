<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->string('on_duty_time')->nullable();
            $table->string('off_duty_time')->nullable();
            $table->string('late_time')->default("10");
            $table->string('early_time')->default("10");
            $table->string('beginning_in')->nullable();
            $table->string('ending_in')->nullable();
            $table->string('beginning_out')->nullable();
            $table->string('ending_out')->nullable();
            $table->string("absent_min_in")->nullable();
            $table->string("absent_min_out")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shifts', function (Blueprint $table) {
            //
        });
    }
};
