<?php

namespace App\Filament\Resources;

use App\Enums\WorkflowStatus;
use App\Enums\WorkflowUserRole;
use App\Models\Workflow;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Tabs;

class WorkflowResource extends Resource
{
    protected static ?string $model = Workflow::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?string $navigationLabel = 'Рабочие процессы';
    protected static ?string $modelLabel = 'Рабочий процесс';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Workflow Details')
                    ->tabs([
                        // ВКЛАДКА 1: Основные настройки
                        Tabs\Tab::make('Настройка процесса')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('title')
                                            ->label('Тема процесса')
                                            ->required(),
                                        Forms\Components\Select::make('workflow_status')
                                            ->label('Статус процесса')
                                            // Используем метод label() вашего Enum
                                            ->options(collect(WorkflowStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]))
                                            ->required()
                                            ->native(false),
                                        Forms\Components\DatePicker::make('due_date')
                                            ->label('Срок исполнения'),
                                        Forms\Components\Textarea::make('note')
                                            ->label('Описание/Задача')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // ВКЛАДКА 2: Участники (Маршрут)
                        Tabs\Tab::make('Участники и Маршрут')
                            ->icon('heroicon-o-users')
                            ->schema([
                                Repeater::make('workflowUsers')
                                    ->relationship('workflowUsers')
                                    ->schema([
                                        Forms\Components\Select::make('user_id')
                                            ->label('Сотрудник')
                                            ->relationship('user', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                          Forms\Components\Select::make('role')
                                              ->label('Роль')
                                              ->options(
                                                  collect(WorkflowUserRole::cases())
                                                      ->mapWithKeys(fn ($role) => [$role->value => $role->label()])
                                              )
                                              ->required()
                                              ->native(false)
                                            ->required(),
                                        Forms\Components\TextInput::make('order_index')
                                            ->label('Порядок')
                                            ->numeric()
                                            ->default(0),
                                    ])
                                    ->columns(3)
                                    ->reorderable('order_index')
                                    ->addActionLabel('Добавить участника'),
                            ]),

                        Tabs\Tab::make('Документы')
                            ->icon('heroicon-o-document-duplicate')
                            ->schema([
                                Forms\Components\Select::make('documents')
                                    ->label('Привязанные документы')
                                    ->relationship('documents', 'title')
                                    ->multiple()
                                    ->preload()
                                    ->helperText('Эти документы будут отправлены на согласование выбранным участникам'),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Тема')
                    ->searchable(),
                Tables\Columns\TextColumn::make('workflow_status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn ($state) => WorkflowStatus::from($state)->label())
                    ->color(fn ($state) => WorkflowStatus::from($state)->color()),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Срок')
                    ->date('d.m.Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Инициатор'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('workflow_status')
                    ->options(collect(WorkflowStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            WorkflowResource\RelationManagers\CommentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => WorkflowResource\Pages\ListWorkflows::route('/'),
            'create' => WorkflowResource\Pages\CreateWorkflow::route('/create'),
            'edit' => WorkflowResource\Pages\EditWorkflow::route('/{record}/edit'),
        ];
    }
}