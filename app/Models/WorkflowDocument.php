<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class WorkflowDocument extends Pivot
{
    use HasFactory;

    protected $table = 'workflow_document';
    protected $fillable = [
        'workflow_id',
        'document_id',
    ];

    public $timestamps = true;
}
