<?php

namespace App\Filament\Resources\WorkflowResource\Pages;

use App\Filament\Resources\WorkflowResource;
use App\Enums\WorkflowUserStatus;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditWorkflow extends EditRecord
{
    protected static string $resource = WorkflowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Кнопка СОГЛАСОВАТЬ
            Actions\Action::make('approve')
                ->label('Согласовать')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->visible(fn () => $this->canCurrentUserAction())
                ->action(function () {
                    $workflowUser = $this->getCurrentWorkflowUser();

                    $workflowUser->update([
                        'status' => WorkflowUserStatus::Approved,
                        'acted_at' => now(),
                    ]);

                    Notification::make()
                        ->title('Успешно согласовано')
                        ->success()
                        ->send();

                    $this->refreshFormData(['workflowUsers']);
                }),

            Actions\Action::make('reject')
                ->label('Отклонить')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->visible(fn () => $this->canCurrentUserAction())
                ->requiresConfirmation()
                ->modalHeading('Отклонить процесс?')
                ->form([
                    \Filament\Forms\Components\Textarea::make('comment')
                        ->label('Комментарий/Причина')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $workflowUser = $this->getCurrentWorkflowUser();

                    $workflowUser->update([
                        'status' => WorkflowUserStatus::Rejected,
                        'acted_at' => now(),
                    ]);

                    $this->record->comments()->create([
                        'user_id' => auth()->id(),
                        'content' => "ОТКЛОНЕНО: " . $data['comment'],
                    ]);

                    Notification::make()
                        ->title('Процесс отклонен')
                        ->danger()
                        ->send();

                    $this->refreshFormData(['workflowUsers']);
                }),

            Actions\DeleteAction::make()
                ->visible(fn ($record) => $record->user_id === auth()->id()),
        ];
    }

    /**
     * Вспомогательный метод: получаем запись текущего пользователя в этом процессе
     */
    protected function getCurrentWorkflowUser()
    {
        return $this->record->workflowUsers()
            ->where('user_id', auth()->id())
            ->where('status', WorkflowUserStatus::Pending)
            ->first();
    }

    protected function canCurrentUserAction(): bool
    {
        $currentUser = $this->getCurrentWorkflowUser();

        if (!$currentUser) return false;

        $hasPendingBefore = $this->record->workflowUsers()
            ->where('order_index', '<', $currentUser->order_index)
            ->where('status', '!=', WorkflowUserStatus::Approved)
            ->exists();

        return !$hasPendingBefore;
    }
}
