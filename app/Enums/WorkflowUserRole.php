<?php

namespace App\Enums;

enum WorkflowUserRole: int
{
    case Initiator = 0;
    case Approver = 1;
    case Executor = 2;
    case Observer = 3;
    case Participant = 4;

    public function label(): string
    {
        return match($this) {
            self::Initiator => 'Инициатор',
            self::Approver => 'Согласующий',
            self::Executor => 'Исполнитель',
            self::Observer => 'Наблюдатель',
            self::Participant => 'Участник',
        };
    }

    public function slug(): string
    {
        return match($this) {
            self::Initiator => 'initiator',
            self::Approver => 'approver',
            self::Executor => 'executor',
            self::Observer => 'observer',
            self::Participant => 'participant',
        };
    }

    public function can(string $action): bool
    {
        return match($action) {
            'comment' => true,
            'approve' => $this === self::Approver,
            'execute' => $this === self::Executor,
            'edit' => $this === self::Initiator,
            default => false,
        };
    }
}
