<?php

namespace App\Enums;

enum  WorkflowStatus: int
{
    case draft = 1;
    case in_review = 2;
    case approved = 3;
    case rejected = 4;
    case executing = 5;
    case completed = 6;
    case archived = 7;

    public function label(): string
    {
        return match ($this) {
            self::draft => 'Черновик',
            self::in_review => 'В процессе рассмотрения',
            self::approved => 'Утвержденный',
            self::rejected => 'Отклоненный',
            self::executing => 'Исполнение',
            self::completed => 'Завершенный',
            self::archived => 'Архивированный',
        };
    }
    public function slug(): string
    {
        return match ($this) {
            self::draft => 'draft',
            self::in_review => 'in_review',
            self::approved => 'approved',
            self::rejected => 'rejected',
            self::executing => 'executing',
            self::completed => 'completed',
            self::archived => 'archived',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::draft => 'gray',
            self::in_review => 'blue',
            self::approved => 'green',
            self::rejected => 'red',
            self::executing => 'yellow',
            self::completed => 'emerald',
            self::archived => 'slate',
        };
    }
}
