<?php
namespace App\Filament\Resources;

use App\Filament\Resources\PositionResource\Pages;
use App\Models\Position;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class PositionResource extends Resource
{
    protected static ?string $model = Position::class;

    protected static ?string $navigationLabel = 'Должности';
    protected static ?string $modelLabel = 'Должность';
    protected static ?string $pluralModelLabel = 'Должности';

    protected static ?string $navigationGroup = 'Справочники';
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Сведения о должности')
                    ->description('Укажите наименование штатной единицы')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Наименование должности')
                            ->placeholder('например, Ведущий специалист')
                            ->required()
                            ->maxLength(255)
                            ->extraInputAttributes(['onchange' => 'this.value = this.value.charAt(0).toUpperCase() + this.value.slice(1)']),

                        // Если в вашей таблице есть связь с департаментом, раскомментируйте:
                        /*
                        Forms\Components\Select::make('department_id')
                            ->label('Подразделение')
                            ->relationship('department', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        */
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
                    ->sortable()
                    ->copyable() // Позволяет быстро скопировать название
                    ->copyMessage('Название скопировано'),

                /* Если есть связь с департаментом:
                Tables\Columns\TextColumn::make('department.name')
                    ->label('Подразделение')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                */

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Добавлена')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Список должностей пуст')
            ->emptyStateIcon('heroicon-o-briefcase');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPositions::route('/'),
            'create' => Pages\CreatePosition::route('/create'),
            'edit' => Pages\EditPosition::route('/{record}/edit'),
        ];
    }
}
