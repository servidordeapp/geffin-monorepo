<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('password_reset_audit_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_type', 40);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->char('email_hash', 64);
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->string('outcome', 20);
            $table->string('reason', 80)->nullable();
            $table->timestamp('created_at');

            $table->index(['event_type', 'created_at']);
            $table->index(['email_hash', 'created_at']);
            $table->index(['ip_address', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_audit_events');
    }
};
