<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DocumentTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'content',
        'file_path',
        'variables',
        'version',
        'created_by',
        'is_active',
        'status',
    ];


    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];


    /**
     * Связь с создателем шаблона
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Метод для проверки наличия файла-шаблона (Word/PDF)
     */
    public function hasFile(): bool
    {
        return !is_null($this->file_path);
    }
}