<?php
namespace App\Filament\Resources\FolderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
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

                // внутри form...
                Section::make('Файлы')
                    ->description('Прикрепите основной документ и приложения')
                    ->schema([
                        Repeater::make('documentFiles') // Должно совпадать с именем метода в модели
                        ->relationship('documentFiles') // Автоматически сохранит в document_files
                        ->schema([
                            FileUpload::make('file_path')
                                ->label('Файл')
                                ->directory('documents')
                                ->required()
                                ->preserveFilenames()
                                ->openable()
                                ->downloadable(),
                        ])
                            ->grid(2) // Файлы будут в два столбика для экономии места
                            ->defaultItems(1) // Сразу показывать одно поле для загрузки
                            ->addActionLabel('Добавить еще один файл')
                    ]),

                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\Select::make('status')
                        ->label('Статус')
                        ->options([
                            'draft' => 'Черновик',
                            'published' => 'Опубликован',
                            'archived' => 'Архив',
                        ])
                        ->default('draft'),

                    Forms\Components\TextInput::make('slug')
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
                // ДОБАВЛЕНА ИКОНКА ДОКУМЕНТА
                Tables\Columns\IconColumn::make('document_icon')
                    ->label('')
                    ->default('document')
                    ->icon(fn ($record) => $record->file_path ? 'heroicon-o-document-text' : 'heroicon-o-document')
                    ->color('info'),

                Tables\Columns\TextColumn::make('document_number')
                    ->label('№')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Название')
                    ->searchable()
                    ->description(fn ($record) => $record->slug),

                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
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
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ]);
    }
}