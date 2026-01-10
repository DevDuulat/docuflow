<?php
namespace App\Filament\Resources;

use App\Filament\Resources\DepartmentResource\Pages;
use App\Filament\Resources\DepartmentResource\RelationManagers;
use App\Models\Department;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static ?string $navigationLabel = 'Подразделения';
    protected static ?string $modelLabel = 'Подразделение';
    protected static ?string $pluralModelLabel = 'Подразделения';

    protected static ?string $navigationGroup = 'Справочники';
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Основная информация')
                    ->description('Укажите название и фактическое местоположение отдела')
                    ->icon('heroicon-m-information-circle')
                    ->schema([
                        Grid::make(2) // Разделяем на 2 колонки
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Название подразделения')
                                ->placeholder('например, Отдел кадров')
                                ->required()
                                ->maxLength(255)
                                ->columnSpan(1),

                            Forms\Components\TextInput::make('location')
                                ->label('Местоположение')
                                ->placeholder('Корпус А, офис 302')
                                ->maxLength(255)
                                ->columnSpan(1),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Department $record): string => $record->location ?? 'Адрес не указан'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата создания')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Обновлено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Редактировать'),
                Tables\Actions\DeleteAction::make()
                    ->label('Удалить'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Удалить выбранные'),
                    ExportBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Подразделений пока нет')
            ->emptyStateDescription('Создайте первое подразделение, чтобы начать работу.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDepartments::route('/'),
            'create' => Pages\CreateDepartment::route('/create'),
            'edit' => Pages\EditDepartment::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\EmployeesRelationManager::class,
        ];
    }
}