<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('invoice_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('key', 32)->unique();
            $table->unsignedBigInteger('last_number')->default(0);
            $table->timestamps();
        });

        DB::table('invoice_sequences')->insert([
            'key'         => 'invoice',
            'last_number' => 0,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('invoice_sequences');
    }
};
