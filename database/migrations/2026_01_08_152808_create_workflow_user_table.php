<?php

use App\Enums\WorkflowUserRole;
use App\Enums\WorkflowUserStatus;
use App\Models\User;
use App\Models\Workflow;
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
        Schema::create('workflow_user', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Workflow::class)
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignIdFor(User::class)
                ->constrained()
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('role')->default(WorkflowUserRole::Participant->value);
            $table->integer('order_index')->default(0);
            $table->unsignedTinyInteger('status')->default(WorkflowUserStatus::Pending->value);
            $table->longText('signature')->nullable();
            $table->timestamp('acted_at')->nullable();
            $table->timestamps();
            $table->unique(['workflow_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_user');
    }
};
