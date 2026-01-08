<?php
namespace App\Filament\Resources;

use App\Filament\Resources\FolderResource\Pages;
use App\Filament\Resources\FolderResource\RelationManagers;
use App\Models\Folder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class FolderResource extends Resource
{
    protected static ?string $model = Folder::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder-open';
    protected static ?string $navigationLabel = 'Диск';
    protected static ?string $breadcrumb = 'Диск';
    protected static ?string $modelLabel = 'Папка';
    protected static ?string $pluralModelLabel = 'Папки';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Сведения о папке')
                    ->description('Управление структурой хранения документов')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Название папки')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Str::slug($state))),

                        TextInput::make('slug')
                            ->label('URL-код (Slug)')
                            ->required()
                            ->unique(ignoreRecord: true),

                        Select::make('parent_id')
                            ->label('Родительская папка')
                            ->relationship('parent', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Корневой уровень'),

                        TextInput::make('order_index')
                            ->label('Порядок сортировки')
                            ->numeric()
                            ->default(0),

                        Toggle::make('status')
                            ->label('Активна')
                            ->default(true)
                            ->onColor('success'),

                        // Скрытые поля для путей и авторов
                        Forms\Components\Hidden::make('path'),
                        Forms\Components\Hidden::make('created_by')
                            ->default(auth()->id()),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('folder_icon')
                    ->label('')
                    ->default('folder')
                    ->icon('heroicon-s-folder')
                    ->color('warning'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Folder $record): string => $record->slug),

                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Родитель')
                    ->placeholder('Root')
                    ->sortable(),

                Tables\Columns\TextColumn::make('documents_count')
                    ->label('Документов')
                    ->counts('documents')
                    ->badge()
                    ->color('info'),

                Tables\Columns\ToggleColumn::make('status')
                    ->label('Статус'),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Создал')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('order_index')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_root')
                    ->label('Уровень')
                    ->placeholder('Все папки')
                    ->trueLabel('Только корневые')
                    ->falseLabel('Только вложенные')
                    ->queries(
                        true: fn ($query) => $query->whereNull('parent_id'),
                        false: fn ($query) => $query->whereNotNull('parent_id'),
                    )
                    ->default(true), // По умолчанию показываем только корни
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

    public static function getRelations(): array
    {
        return [
            RelationManagers\ChildrenRelationManager::class,
            RelationManagers\DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFolders::route('/'),
            'create' => Pages\CreateFolder::route('/create'),
            'edit' => Pages\EditFolder::route('/{record}/edit'),
        ];
    }
}