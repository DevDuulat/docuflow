<?php
namespace App\Filament\Resources\WorkflowResource\Pages;

use App\Filament\Resources\WorkflowResource;
use App\Enums\WorkflowStatus;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ListRecords;

class ListWorkflows extends ListRecords
{
    protected static string $resource = WorkflowResource::class;

    public function getTabs(): array
    {
        return [
            // Вкладка "Все"
            'all' => Tab::make('Все'),

            'active' => Tab::make('В работе')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereIn('workflow_status', [
                        WorkflowStatus::in_review,
                        WorkflowStatus::executing
                    ])
                )
                ->badge(fn() => $this->getModel()::whereIn('workflow_status', [
                    WorkflowStatus::in_review,
                    WorkflowStatus::executing
                ])->count())
                ->badgeColor('primary'),

            // Черновики
            'drafts' => Tab::make('Черновики')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('workflow_status', WorkflowStatus::draft))
                ->icon('heroicon-m-pencil-square'),

            // Утвержденные
            'approved' => Tab::make('Утвержденные')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('workflow_status', WorkflowStatus::approved))
                ->icon('heroicon-m-check-badge')
                ->badgeColor('success'),

            // Архив
            'archived' => Tab::make('Архив')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('workflow_status', WorkflowStatus::archived))
                ->icon('heroicon-m-archive-box'),

        ];
    }
}