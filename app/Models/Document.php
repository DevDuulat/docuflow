<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'folder_id',
        'category_id',
        'content',
        'document_number',
        'comment',
        'status',
        'created_by',
    ];



    /**
     * Связь с папкой
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * Связь с категорией
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Связь с создателем (автором)
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function documentFiles(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DocumentFile::class);
    }

}