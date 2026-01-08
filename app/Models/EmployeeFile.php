<?php
namespace App\Models;

use App\Enums\EmployeeFileType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class EmployeeFile extends Model
{
    protected $fillable = [
        'employee_id',
        'file_name',
        'file_path',
        'type',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    // Отключаем стандартные timestamps, так как у вас свой uploaded_at
    public $timestamps = false;

    /**
     * Связь с сотрудником
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Аксессор для получения полного URL файла (удобно для Filament)
     */
    public function getFullUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->file_url);
    }
}