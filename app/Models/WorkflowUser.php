<?php

namespace App\Models;

use App\Enums\WorkflowUserRole;
use App\Enums\WorkflowUserStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowUser extends Model
{
    use HasFactory;

    protected $table = 'workflow_user';

    protected $fillable = [
        'workflow_id',
        'user_id',
        'role',
        'order_index',
        'status',
        'acted_at',
    ];

    protected $casts = [
        'role' => WorkflowUserRole::class,
        'status' => WorkflowUserStatus::class,
        'acted_at' => 'datetime',
    ];

    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


}
