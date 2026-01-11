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
use Illuminate\Support\Str;
use Filament\Forms\Set;
class WorkflowResource extends Resource
{
    protected static ?string $model = Workflow::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $modelLabel = 'Рабочий процесс';

    protected static ?string $pluralModelLabel = 'Рабочие процессы';

    protected static ?string $navigationLabel = 'Рабочие процессы';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getGloballySearchableAttributes(): array
    {
        return ['title'];
    }


    // ... в начале класса
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Детали процесса')
                    ->tabs([
                        Tabs\Tab::make('Настройка процесса')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('title')
                                            ->label('Тема процесса')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))
                                            // Блокируем, если не инициатор
                                            ->disabled(fn ($record) => static::isNotInitiator($record)),

                                        Forms\Components\TextInput::make('slug')
                                            ->label('Технический адрес (Slug)')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->disabled()
                                            ->dehydrated(),

                                        Forms\Components\Select::make('workflow_status')
                                            ->label('Статус процесса')
                                            ->options(collect(WorkflowStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]))
                                            ->required()
                                            ->native(false)
                                            // Блокируем, если не инициатор
                                            ->disabled(fn ($record) => static::isNotInitiator($record)),

                                        Forms\Components\DatePicker::make('due_date')
                                            ->label('Срок исполнения')
                                            ->disabled(fn ($record) => static::isNotInitiator($record)),

                                        Forms\Components\Textarea::make('note')
                                            ->label('Описание/Задача')
                                            ->columnSpanFull()
                                            ->disabled(fn ($record) => static::isNotInitiator($record)),
                                    ]),
                            ]),

                        Tabs\Tab::make('Участники и Маршрут')
                            ->icon('heroicon-o-users')
                            ->schema([
                                Repeater::make('workflowUsers')
                                    ->relationship('workflowUsers')
                                    // Запрещаем добавлять и удалять строки, если не автор
                                    ->addable(fn ($record) => $record === null || $record->user_id === auth()->id())
                                    ->deletable(fn ($record) => $record === null || $record->user_id === auth()->id())
                                    ->reorderable('order_index')
                                    ->label('Участники')
                                    ->schema([
                                        Forms\Components\Select::make('user_id')
                                            ->label('Сотрудник')
                                            ->relationship('user', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->disabled(fn ($record) => static::isNotInitiator($record)),

                                        Forms\Components\Select::make('role')
                                            ->label('Роль')
                                            ->options(collect(WorkflowUserRole::cases())->mapWithKeys(fn ($role) => [$role->value => $role->label()]))
                                            ->required()
                                            ->native(false)
                                            ->disabled(fn ($record) => static::isNotInitiator($record)),

                                        Forms\Components\TextInput::make('order_index')
                                            ->label('Порядок')
                                            ->numeric()
                                            ->default(0)
                                            ->disabled(fn ($record) => static::isNotInitiator($record)),

                                        Forms\Components\Placeholder::make('status_display')
                                            ->label('Текущий статус')
                                            ->content(fn ($record) => $record?->status?->label() ?? 'Ожидает')
                                            ->visible(fn ($record) => $record !== null),
                                    ])
                                    ->columns(3)
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
                                    // Блокируем выбор документов
                                    ->disabled(fn ($record) => static::isNotInitiator($record)),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }

    protected static function isNotInitiator($record): bool
    {
        if (!$record) return false;

        if ($record instanceof \App\Models\WorkflowUser) {
            return $record->workflow->user_id !== auth()->id();
        }

        return $record->user_id !== auth()->id();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Тема')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->category?->name),

                Tables\Columns\TextColumn::make('workflow_status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn ($state) => WorkflowStatus::from($state)->label())
                    ->color(fn ($state) => WorkflowStatus::from($state)->color()),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Срок')
                    ->date('d.m.Y')
                    ->sortable()
                    ->color(fn ($record) => $record->due_date?->isPast() ? 'danger' : 'gray'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Инициатор')
                    ->searchable()
                    ->toggleable()
                    ->icon('heroicon-m-user'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('workflow_status')
                    ->label('Статус выполнения')
                    ->multiple()
                    ->options(WorkflowStatus::class),

                Tables\Filters\Filter::make('is_mine')
                    ->label('Мои записи')
                    ->query(fn ($query) => $query->where('user_id', auth()->id())),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('notify_participants')
                        ->label('Уведомить участников')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('info')
                        ->requiresConfirmation()
                        ->action(function (Workflow $record) {
                            $userIds = \App\Models\WorkflowUser::where('workflow_id', $record->id)
                                ->pluck('user_id')
                                ->toArray();

                            // Если здесь будет пусто - значит данные не сохранились в БД
                            if (empty($userIds)) {
                                \Filament\Notifications\Notification::make()->title('В базе нет участников!')->danger()->send();
                                return;
                            }


                            // 1. Обязательно подгружаем участников и их данные пользователей
                            $participants = $record->workflowUsers()->with('user')->get();

                            if ($participants->isEmpty()) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Участники не найдены')
                                    ->warning()
                                    ->send();
                                return;
                            }

                            $count = 0;
                            foreach ($participants as $participant) {
                                $user = $participant->user;

                                // Проверяем, существует ли пользователь и это не сам автор
                                if ($user && $user->id !== auth()->id()) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Новое задание')
                                        ->body("Вы назначены участником в процессе: \"{$record->title}\"")
                                        ->icon('heroicon-o-clipboard-document-check')
                                        ->actions([
                                            \Filament\Notifications\Actions\Action::make('view')
                                                ->label('Открыть')
                                                ->url(WorkflowResource::getUrl('edit', ['record' => $record]))
                                        ])
                                        ->sendToDatabase($user); // Отправка в БД (для колокольчика)

                                    $count++;
                                }
                            }

                            \Filament\Notifications\Notification::make()
                                ->title($count > 0 ? "Уведомления отправлены ($count)" : "Нет получателей")
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->visible(fn ($record) => $record->user_id === auth()->id()),
                ]),
            ])
            ->defaultSort('due_date', 'asc');
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