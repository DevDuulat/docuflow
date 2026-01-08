<?php
namespace App\Filament\Resources\DepartmentResource\Widgets;

use App\Models\Department;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;

class GeneralStatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Всего департаментов', Department::count())
                ->icon('heroicon-o-building-office-2')

                ->description('Активные подразделения'),

            Stat::make('Штат сотрудников', Department::withCount('employees')->get()->sum('employees_count'))
                ->icon('heroicon-o-user-group')
                ->color('success')
                ->description('Общее кол-во людей'),
        ];
    }
}