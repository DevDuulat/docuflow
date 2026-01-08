<?php
namespace App\Filament\Resources\DepartmentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class EmployeesRelationManager extends RelationManager
{
    protected static string $relationship = 'employees';

    protected static ?string $title = 'Сотрудники отдела';
    protected static ?string $modelLabel = 'сотрудника';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('last_name')->label('Фамилия')->required(),
            Forms\Components\TextInput::make('first_name')->label('Имя')->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('last_name')
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('ФИО')
                    ->state(fn ($record) => "{$record->last_name} {$record->first_name}"),
                Tables\Columns\TextColumn::make('position.name')
                    ->label('Должность')
                    ->badge(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}