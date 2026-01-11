<?php

namespace App\Filament\Resources\WorkflowResource\Pages;

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
                    // Добавляем поле комментария, чтобы было откуда брать текст!
                    \Filament\Forms\Components\Textarea::make('comment')
                        ->label('Комментарий')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $workflowUser = $this->getCurrentWorkflowUser();

                    $workflowUser->update([
                        'status' => WorkflowUserStatus::Approved,
                        'acted_at' => now(),
                        'signature' => $data['signature'],
                    ]);

                    $this->record->comments()->create([
                        'user_id' => auth()->id(),
                        'comment' => "СОГЛАСОВАНО: " . $data['comment'], // Теперь ключ 'comment' совпадает с формой и БД
                    ]);

                    Notification::make()
                        ->title('Успешно согласовано с подписью')
                        ->success()
                        ->send();

                    $this->refreshFormData(['workflowUsers']);
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
                    // Здесь подпись обычно не нужна, только причина
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
                        'comment' => "ОТКЛОНЕНО: " . $data['comment'], // Исправлено с 'content' на 'comment'
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