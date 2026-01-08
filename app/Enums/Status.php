<?php

namespace App\Enums;

enum Status: int
{
    case draft = 0;
    case published = 1;
    case archived = 2;
    case trash = 3;

    public function label(): string
    {
        return match ($this) {
            self::draft => 'Черновик',
            self::published => 'Опубликован',
            self::archived => 'В архиве',
            self::trash => 'В корзине',
        };
    }

    public function slug(): string
    {
        return match ($this) {
            self::draft => 'draft',
            self::published => 'published',
            self::trash => 'trash',
            self::archived => 'archived',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::draft, self::published]);
    }

    public function isInactive(): bool
    {
        return in_array($this, [self::trash, self::archived]);
    }

    public function isTrash(): bool
    {
        return $this === self::trash;
    }

    public function isArchived(): bool
    {
        return $this === self::archived;
    }
}
