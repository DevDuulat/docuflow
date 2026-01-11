<?php

namespace App\Filament\Resources\WorkflowResource\Pages;

use App\Enums\WorkflowStatus;
use App\Filament\Resources\WorkflowResource;
use App\Enums\WorkflowUserStatus;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;

class EditWorkflow extends EditRecord
{
    protected static string $resource = WorkflowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Кнопка СОГЛАСОВАТЬ
            // Кнопка СОГЛАСОВАТЬ
            Actions\Action::make('approve')
                ->label('Согласовать')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->visible(fn () => $this->canCurrentUserAction())
                ->form([
                    SignaturePad::make('signature')
                        ->label('Ваша подпись')
                        ->confirmable()
                        ->required(),
                    \Filament\Forms\Components\Textarea::make('comment')
                        ->label('Комментарий')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $workflowUser = $this->getCurrentWorkflowUser();

                    // 1. Обновляем статус текущего участника
                    $workflowUser->update([
                        'status' => WorkflowUserStatus::Approved,
                        'acted_at' => now(),
                        'signature' => $data['signature'],
                    ]);

                    // 2. Создаем комментарий
                    $this->record->comments()->create([
                        'user_id' => auth()->id(),
                        'comment' => "СОГЛАСОВАНО: " . $data['comment'],
                    ]);

                    // 3. ПРОВЕРКА: Все ли согласовали?
                    // Ищем участников, у которых статус НЕ 'Approved'
                    $hasPendingParticipants = $this->record->workflowUsers()
                        ->where('status', '!=', WorkflowUserStatus::Approved)
                        ->exists();

                    if (!$hasPendingParticipants) {
                        // Если таких нет (все Approved), закрываем весь процесс
                        $this->record->update([
                            'workflow_status' => WorkflowStatus::approved,
                        ]);

                        Notification::make()
                            ->title('Процесс утвержден')
                            ->body('Все участники согласовали документ.')
                            ->success()
                            ->send();
                    } else {
                        // Если кто-то еще остался, переводим из черновика в "рассмотрение"
                        if ($this->record->workflow_status === WorkflowStatus::draft) {
                            $this->record->update([
                                'workflow_status' => WorkflowStatus::in_review,
                            ]);
                        }

                        Notification::make()
                            ->title('Успешно согласовано')
                            ->success()
                            ->send();
                    }

                    $this->refreshFormData(['workflowUsers', 'workflow_status']);
                }),

            // Кнопка ОТКЛОНИТЬ
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

                    // 1. Обновляем участника
                    $workflowUser->update([
                        'status' => WorkflowUserStatus::Rejected,
                        'acted_at' => now(),
                    ]);

                    // 2. Создаем комментарий
                    $this->record->comments()->create([
                        'user_id' => auth()->id(),
                        'comment' => "ОТКЛОНЕНО: " . $data['comment'],
                    ]);

                    // 3. ЛОГИКА ОТКЛОНЕНИЯ: Если один отклонил, весь процесс - Rejected
                    $this->record->update([
                        'workflow_status' => WorkflowStatus::rejected,
                    ]);

                    Notification::make()
                        ->title('Процесс отклонен')
                        ->danger()
                        ->send();

                    $this->refreshFormData(['workflowUsers', 'workflow_status']);
                }),

            Actions\DeleteAction::make()
                ->visible(fn ($record) => $record->user_id === auth()->id()),
        ];
    }

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