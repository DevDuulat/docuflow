<?php
namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\FileUpload;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationLabel = 'Сотрудники';
    protected static ?string $modelLabel = 'Сотрудник';
    protected static ?string $pluralModelLabel = 'Сотрудники';

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Справочники';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Личная информация')
                    ->columns(3)
                    ->schema([
                        FileUpload::make('avatar_url')
                            ->label('Фото')
                            ->avatar()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('avatars')
                            ->visibility('public'),

                        Forms\Components\Group::make([
                            Forms\Components\TextInput::make('first_name')
                                ->label('Имя')
                                ->required()
                                ->maxLength(50),
                            Forms\Components\TextInput::make('last_name')
                                ->label('Фамилия')
                                ->required()
                                ->maxLength(50),

                        ])->columnSpan(2),
                    ]),

                Section::make('Трудоустройство')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('department_id')
                            ->label('Подразделение')
                            ->relationship('department', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('position_id')
                            ->label('Должность')
                            ->relationship('position', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('salary')
                            ->label('Оклад')
                            ->numeric()
                            ->prefix('C')
                            ->required(),

                        Forms\Components\DatePicker::make('hire_date')
                            ->label('Дата приема')
                            ->native(false)
                            ->displayFormat('d.m.Y')
                            ->required(),
                    ]),

                Section::make('Документы')
                    ->collapsed()
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('passport_number')
                            ->label('Паспорт')
                            ->placeholder('Серия и номер')
                            ->maxLength(20),
                        Forms\Components\TextInput::make('inn')
                            ->label('ИНН')
                            ->maxLength(20),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar_url')
                    ->label('')
                    ->circular(),

                Tables\Columns\TextColumn::make('full_name')
                ->label('ФИО')
                    ->state(fn (Employee $record): string => "{$record->last_name} {$record->first_name}")
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('department.name')
                    ->label('Подразделение')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('position.name')
                    ->label('Должность')
                    ->color('gray'),

                Tables\Columns\TextColumn::make('salary')
                    ->label('Оклад')
                    ->money('KGS', locale: 'kg')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('hire_date')
                    ->label('Принят')
                    ->date('d.m.Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('department')
                    ->label('Отдел')
                    ->relationship('department', 'name'),
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
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}