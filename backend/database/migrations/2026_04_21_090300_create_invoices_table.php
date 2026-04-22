<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->unique()->constrained('payments')->restrictOnDelete();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('number', 32)->unique();
            $table->date('issue_date');
            $table->text('description');
            $table->decimal('subtotal', 14, 2);
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('total', 14, 2);
            $table->string('currency', 3)->default('AED');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index('issue_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoices');
    }
};
