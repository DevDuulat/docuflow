<?php
namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    // Локализация
    protected static ?string $navigationLabel = 'Категории документов';
    protected static ?string $modelLabel = 'Категория';
    protected static ?string $pluralModelLabel = 'Категории';

    protected static ?string $navigationGroup = 'Справочники';
    protected static ?string $navigationIcon = 'heroicon-o-tag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Сведения о категории')
                    ->description('Укажите название категории для классификации документов')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Наименование категории')
                            ->placeholder('например, Приказы по личному составу')
                            ->required()
                            ->unique(ignoreRecord: true) // Чтобы не дублировать категории
                            ->maxLength(255),

                        Toggle::make('status')
                            ->label('Активна')
                            ->helperText('Отключенные категории нельзя будет выбрать при создании документов')
                            ->default(true) // В БД у вас 1, Filament поймет true как 1
                            ->required(),
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

                Tables\Columns\IconColumn::make('status')
                    ->label('Статус')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создана')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('status')
                    ->label('По статусу')
                    ->placeholder('Все')
                    ->trueLabel('Только активные')
                    ->falseLabel('Только неактивные'),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}