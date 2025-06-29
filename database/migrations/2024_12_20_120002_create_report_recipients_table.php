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
        Schema::create('report_recipients', function (Blueprint $table) {
            $table->id();

            $table->uuid('uuid')->unique();

            $table->foreignUuid('report_id')->index()->constrained('reports', 'uuid')->onDelete('cascade');

            $table->foreignUuid('contact_id')->nullable()->index()->constrained('contacts', 'uuid')->onDelete('set null');

            $table->string('email');

            $table->string('name');

            $table->timestamp('sent_at')->nullable();

            $table->enum('delivery_status', ['pending', 'sent', 'delivered', 'failed'])->default('pending');

            $table->text('delivery_notes')->nullable();

            $table->timestamps();

            // Indexes for better query performance
            $table->index(['report_id', 'delivery_status']);
            $table->index(['email', 'sent_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_recipients');
    }
};
