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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->foreignIdFor(\App\Models\Position::class)
                ->nullable()
                ->constrained()
                ->onDelete('set null');
            $table->decimal('salary', 10, 2);
            $table->date('hire_date');
            $table->unsignedBigInteger('department_id');
            $table->string('passport_number', 20)->nullable();
            $table->string('inn', 20)->nullable();
            $table->string('avatar_url', 255)->nullable();
            $table->timestamps();
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
