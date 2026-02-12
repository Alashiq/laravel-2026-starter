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
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->unique();
            $table->string('password');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('photo')->nullable()->default('assets/avatar.webp');

            $table->foreignId('role_id')->nullable()->constrained('roles')->onDelete('cascade');

            $table->integer('login_attempts')->default(0);
            $table->dateTime('attempts_at')->nullable();
            $table->timestamp('locked_until')->nullable();

            $table->enum('status', ['not_active', 'active', 'banned', 'deleted'])->default('not_active');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
