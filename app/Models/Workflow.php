<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'slug', 'note', 'due_date', 'workflow_status', 'status', 'user_id'
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function documents() {
        return $this->belongsToMany(Document::class, 'workflow_document');
    }

    public function workflowUsers() {
        return $this->hasMany(WorkflowUser::class);
    }

    public function comments() {
        return $this->hasMany(WorkflowComment::class);
    }


}
