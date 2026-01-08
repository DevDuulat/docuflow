<?php

namespace App\Enums;

enum WorkflowUserStatus: int
{
    case Pending = 0;
    case Approved = 1;
    case Rejected = 2;
    case Redirected = 3;

    public function label(): string
    {
        return match($this) {
            self::Pending => 'Ожидает',
            self::Approved => 'Согласовал',
            self::Rejected => 'Отклонил',
            self::Redirected => 'Перенаправлен',
        };
    }

    public function slug(): string
    {
        return match($this) {
            self::Pending => 'pending',
            self::Approved => 'approved',
            self::Rejected => 'rejected',
            self::Redirected => 'redirected',
        };
    }
}
