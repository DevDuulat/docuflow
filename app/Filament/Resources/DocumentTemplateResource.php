<?php
namespace App\Filament\Resources;

use App\Filament\Resources\DocumentTemplateResource\Pages;
use App\Models\DocumentTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;

class DocumentTemplateResource extends Resource
{
    protected static ?string $model = DocumentTemplate::class;

    protected static ?string $navigationLabel = 'Шаблоны документов';
    protected static ?string $modelLabel = 'Шаблон';
    protected static ?string $pluralModelLabel = 'Шаблоны документов';
    protected static ?string $navigationGroup = 'Настройки СЭД';
    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)
                    ->schema([
                        // Основная колонка (слева)
                        Forms\Components\Group::make([
                            Section::make('Контент шаблона')
                                ->schema([
                                    Tabs::make('Тип шаблона')
                                        ->tabs([
                                            Tabs\Tab::make('Текстовый редактор')
                                                ->icon('heroicon-m-pencil-square')
                                                ->schema([
                                                    Forms\Components\RichEditor::make('content')
                                                        ->label('Текст шаблона')
                                                        ->helperText('Используйте фигурные скобки для переменных, например: {{client_name}}'),
                                                ]),
                                            Tabs\Tab::make('Файл-заготовка')
                                                ->icon('heroicon-m-paper-clip')
                                                ->schema([
                                                    Forms\Components\FileUpload::make('file_path')
                                                        ->label('Загрузить .docx или .pdf')
                                                        ->directory('document-templates')
                                                        ->openable()
                                                        ->downloadable(),
                                                ]),
                                        ]),
                                ]),

                            Section::make('Переменные')
                                ->description('Определите ключи, которые будут заменяться данными')
                                ->schema([
                                    Forms\Components\KeyValue::make('variables')
                                        ->label('')
                                        ->keyLabel('Ключ (variable)')
                                        ->valueLabel('Описание / Тип')
                                        ->reorderable(),
                                ]),
                        ])->columnSpan(2),

                        // Боковая колонка (справа)
                        Forms\Components\Group::make([
                            Section::make('Параметры')
                                ->schema([
                                    Forms\Components\TextInput::make('name')
                                        ->label('Название')
                                        ->required()
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) =>
                                        $operation === 'create' ? $set('slug', \Str::slug($state)) : null
                                        ),

                                    Forms\Components\TextInput::make('slug')
                                        ->label('Код (Slug)')
                                        ->required()
                                        ->unique(ignoreRecord: true),

                                    Forms\Components\TextInput::make('version')
                                        ->label('Версия')
                                        ->default('1.0')
                                        ->required(),

                                    Forms\Components\Select::make('status')
                                        ->label('Статус')
                                        ->options([
                                            'draft' => 'Черновик',
                                            'published' => 'Опубликован',
                                            'archived' => 'Архив',
                                        ])
                                        ->default('draft')
                                        ->required(),

                                    Forms\Components\Toggle::make('is_active')
                                        ->label('Активен для выбора')
                                        ->default(true),
                                ]),

                            Section::make('Описание')
                                ->collapsed()
                                ->schema([
                                    Forms\Components\Textarea::make('description')
                                        ->label('Краткое описание назначения шаблона'),
                                ]),
                        ])->columnSpan(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Наименование')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('version')
                    ->label('Версия')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\SelectColumn::make('status')
                    ->label('Статус')
                    ->options([
                        'draft' => 'Черновик',
                        'published' => 'Опубликован',
                        'archived' => 'Архив',
                    ]),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean(),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Создал')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата')
                    ->date('d.m.Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Черновик',
                        'published' => 'Опубликован',
                        'archived' => 'Архив',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Только активные'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocumentTemplates::route('/'),
            'create' => Pages\CreateDocumentTemplate::route('/create'),
            'edit' => Pages\EditDocumentTemplate::route('/{record}/edit'),
        ];
    }
}
