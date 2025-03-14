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
        Schema::create('timezone_employees', function (Blueprint $table) {
            $table->id();
            $table->integer("company_id");
            $table->integer("branch_id")->nullable();
            $table->integer("timezone_table_id");
            $table->integer("device_table_id");
            $table->integer("employee_table_id");
            $table->integer("device_timezone_id");
            $table->string("sdk_response")->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('timezone_employees');
    }
};
