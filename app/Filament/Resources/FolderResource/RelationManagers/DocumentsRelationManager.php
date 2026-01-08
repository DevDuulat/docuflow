<?php

namespace App\Filament\Resources\FolderResource\RelationManagers;

use App\Models\Workflow;
use App\Enums\WorkflowStatus;
use App\Enums\Status;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';
    protected static ?string $title = 'Документы в этой папке';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Заголовок документа')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),

                    Forms\Components\TextInput::make('document_number')
                        ->label('Рег. номер')
                        ->placeholder('Напр. № 125-А'),
                ]),

                Section::make('Файлы документа')
                    ->description('Прикрепите основной документ и дополнительные файлы (приложения)')
                    ->schema([
                        Repeater::make('documentFiles')
                            ->relationship('documentFiles')
                            ->schema([
                                FileUpload::make('file_path')
                                    ->label('Загрузить файл')
                                    ->directory('documents')
                                    ->required()
                                    ->preserveFilenames()
                                    ->openable()
                                    ->downloadable(),
                            ])
                            ->grid(2)
                            ->defaultItems(1)
                            ->addActionLabel('Добавить файл'),
                    ]),

                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\Select::make('status')
                        ->label('Статус')
                        ->options([
                            'draft' => 'Черновик',
                            'published' => 'Опубликован',
                            'archived' => 'Архив',
                            'on_approval' => 'На согласовании',
                        ])
                        ->default('draft'),

                    Forms\Components\TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->unique(table: 'documents', ignoreRecord: true),
                ]),

                Forms\Components\Hidden::make('created_by')
                    ->default(auth()->id()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('icon')
                    ->label('')
                    ->icon('heroicon-o-document-text')
                    ->color('info'),

                Tables\Columns\TextColumn::make('document_number')
                    ->label('№')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Название')
                    ->searchable()
                    ->description(fn ($record) => $record->slug),

                // Счётчик прикрепленных файлов
                Tables\Columns\TextColumn::make('document_files_count')
                    ->label('Файлы')
                    ->counts('documentFiles')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'on_approval' => 'warning',
                        'draft' => 'gray',
                        'archived' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Загружен')
                    ->date('d.m.Y')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Загрузить документ')
                    ->icon('heroicon-m-plus-circle'),
            ])
            ->actions([
                Tables\Actions\Action::make('startWorkflow')
                    ->label('Создать процесс') // Меняем название, так как это только начало
                    ->icon('heroicon-m-plus-circle')
                    ->color('success')
                    ->hidden(fn ($record) => $record->status === 'on_approval')
                    ->form([
                        Forms\Components\TextInput::make('workflow_title')
                            ->label('Тема процесса')
                            ->default(fn ($record) => "Согласование: " . $record->title)
                            ->required(),

                        Forms\Components\DatePicker::make('due_date')
                            ->label('Срок исполнения')
                            ->required()
                            ->default(now()->addDays(3)),
                    ])
                    ->action(function (array $data, $record): void {
                        // 1. Создаем только "шапку" процесса
                        $workflow = \App\Models\Workflow::create([
                            'title' => $data['workflow_title'],
                            'slug' => Str::slug($data['workflow_title']) . '-' . uniqid(),
                            'due_date' => $data['due_date'],
                            'user_id' => auth()->id(),
                            'status' => \App\Enums\Status::published->value,
                            'workflow_status' => \App\Enums\WorkflowStatus::draft->value, // Сначала Черновик!
                        ]);

                        // 2. Привязываем документ через пивот
                        $workflow->documents()->attach($record->id);

                        // 3. Обновляем статус документа
                        $record->update(['status' => 'on_approval']);

                        Notification::make()
                            ->success()
                            ->title('Процесс инициализирован')
                            ->body('Теперь настройте участников и комментарии в разделе процессов.')
                            ->send();

                        // 4. ПЕРЕНАПРАВЛЕНИЕ: отправляем пользователя сразу в созданный процесс
                        // Замените 'workflows' на имя вашего ресурса, если оно отличается
                        redirect()->to(\App\Filament\Resources\WorkflowResource::getUrl('edit', ['record' => $workflow]));
                    }),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ]);
    }
}