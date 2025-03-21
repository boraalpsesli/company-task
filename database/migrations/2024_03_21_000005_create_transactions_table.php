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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('receiver_id');
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['pending', 'completed', 'failed']);
            $table->foreignId('team_id')->nullable()->constrained('teams')->onDelete('set null');
            $table->enum('type', ['income', 'expense']);
            $table->string('description');
            $table->date('date');
            $table->string('category');
            $table->string('reference_number')->nullable()->unique();
            $table->timestamps();

            // Since sender and receiver can be either users or companies,
            // we don't set up foreign key constraints here
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
}; 