<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number')->unique();
            $table->string('password');
            $table->string('name')->nullable(true);
            $table->string('email')->unique()->nullable(true);
            $table->string('address')->nullable(true);
            $table->string('referral_code')->nullable(true)->length(6);
            $table->boolean('developer')->default(false);
            $table->boolean('administration')->default(false);
            $table->string('qr_code')->nullable(true);
            $table->integer('point')->nullable(true)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
