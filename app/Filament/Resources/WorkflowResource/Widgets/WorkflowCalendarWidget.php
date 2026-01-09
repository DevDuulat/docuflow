<?php

namespace App\Filament\Resources\WorkflowResource\Widgets;

use App\Models\Workflow;
use Guava\Calendar\Widgets\CalendarWidget;
use Illuminate\Support\Collection;
use App\Filament\Resources\WorkflowResource;

class WorkflowCalendarWidget extends CalendarWidget
{
    protected int | string | array $columnSpan = 'full';
    protected string|\Closure|null|\Illuminate\Support\HtmlString $heading = 'Календарь рабочих процессов';

    public function getEvents(array $fetchInfo = []): Collection | array
    {
        $start = $fetchInfo['start'] ?? null;
        $end = $fetchInfo['end'] ?? null;

        return Workflow::query()
            ->where(function ($query) {
                $query->where('user_id', auth()->id())
                    ->orWhereHas('workflowUsers', fn($q) => $q->where('user_id', auth()->id()));
            })
            // Ищем записи, у которых именно Срок исполнения (due_date) попадает в диапазон
            ->when($start, fn($query) => $query->where('due_date', '>=', $start))
            ->when($end, fn($query) => $query->where('due_date', '<=', $end))
            ->get()
            ->map(fn (Workflow $workflow) => $workflow->toCalendarEvent());
    }

    public function onEventClick(array $info = [], ?string $action = null): void
    {
        $recordId = $info['event']['id'] ?? null;

        if ($recordId) {
            $this->redirect(WorkflowResource::getUrl('edit', ['record' => $recordId]));
        }
    }
}