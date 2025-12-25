<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('bosta_id')->nullable(); // _id from webhook
            $table->integer('state_code')->nullable(); // state number
            $table->string('type')->nullable(); // SEND, EXCHANGE, etc.
            $table->decimal('cod', 10, 2)->nullable(); // cash on delivery amount
            $table->timestamp('state_changed_at')->nullable(); // from timeStamp
            $table->boolean('is_confirmed_delivery')->default(false);
            $table->date('delivery_promise_date')->nullable();
            $table->string('exception_reason')->nullable();
            $table->integer('exception_code')->nullable();
            $table->string('business_reference')->nullable();
            $table->integer('number_of_attempts')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'bosta_id',
                'state_code',
                'type',
                'cod',
                'state_changed_at',
                'is_confirmed_delivery',
                'delivery_promise_date',
                'exception_reason',
                'exception_code',
                'business_reference',
                'number_of_attempts',
            ]);
        });
    }
};
