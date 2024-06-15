<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users', 'id');
            $table->string('phone', 16)->required;
            $table->string('location');
            $table->string('section')->required;
            $table->string('specialization')->required;
            $table->string('specialization_type')->required;
            $table->string('governorate');
            $table->string('info', 255);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
