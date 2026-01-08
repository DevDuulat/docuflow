<?php

namespace App\Filament\Resources\WorkflowResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';
    protected static ?string $title = 'Обсуждение';
    protected static ?string $modelLabel = 'комментарий';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('comment')
                    ->label('Ваше сообщение')
                    ->placeholder('Напишите замечание или уточнение...')
                    ->required()
                    ->maxLength(1000)
                    ->columnSpanFull(),
                Forms\Components\Hidden::make('user_id')
                    ->default(auth()->id()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('comment')
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\Layout\Split::make([
                        Tables\Columns\TextColumn::make('user.name')
                            ->weight('bold')
                            ->grow(false)
                            ->icon('heroicon-m-user-circle'),
                        Tables\Columns\TextColumn::make('created_at')
                            ->dateTime('d.m.Y H:i')
                            ->color('gray')
                            ->alignEnd(),
                    ]),
                    Tables\Columns\TextColumn::make('comment')
                        ->wrap(),
                ])->space(2),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Добавить комментарий')
                    ->icon('heroicon-m-chat-bubble-left-right')
                    ->modalHeading('Новое сообщение'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => $record->user_id === auth()->id()),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => $record->user_id === auth()->id()),
            ])
            ->emptyStateHeading('Пока нет комментариев')
            ->emptyStateDescription('Напишите первое сообщение по этому процессу.');
    }
}