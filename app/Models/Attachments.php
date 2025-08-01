<?php

namespace App\Models;

use App\Filament\Enums\AttachmentCategory;
use Illuminate\Database\Eloquent\Model;

class Attachments extends Model
{
    protected $fillable = [
        'attachment',
        'attachmentName',
        'category',
        'attachmentOwner',
        'moduleName',
    ];

    protected $casts = [
        'category' => AttachmentCategory::class,
    ];
}
