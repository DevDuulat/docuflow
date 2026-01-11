<?php

namespace App\Models;

use App\Enums\WorkflowStatus; // Убедитесь, что путь к вашему Enum верен
use Guava\Calendar\Contracts\Eventable;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workflow extends Model implements Eventable
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'note', 'due_date', 'workflow_status', 'status', 'user_id'
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }
    protected static function booted()
    {
        static::creating(function ($workflow) {
            if (auth()->check() && ! $workflow->user_id) {
                $workflow->user_id = auth()->id();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class, 'workflow_document');
    }

    public function workflowUsers(): HasMany
    {
        return $this->hasMany(WorkflowUser::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(WorkflowComment::class);
    }

    /**
     * Логика преобразования модели в событие календаря
     */
    public function toCalendarEvent(): CalendarEvent
    {
        $date = $this->due_date ?? $this->created_at;

        return CalendarEvent::make($this)
            ->title($this->title)
            ->start($date)
            ->end($date)
            ->allDay()
            ->backgroundColor(match ($this->workflow_status?->value ?? $this->workflow_status) {
                'completed' => '#10b981',
                'rejected' => '#ef4444',
                default => '#f59e0b',
            });
    }
}