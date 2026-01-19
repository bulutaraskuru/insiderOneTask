<?php

use App\Enums\MessageSendStatus;
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
        Schema::create('message_sends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('messages')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('phone_number', 20);
            $table->text('message_content');
            $table->string('status')->default(MessageSendStatus::PENDING->value);
            $table->string('webhook_message_id')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();


            $table->index(['status', 'created_at']);
            $table->index('message_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_sends');
    }
};
