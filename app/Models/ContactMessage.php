<?php

namespace App\Models;

use App\Domain\Contact\ContactMessageCategory;
use App\Domain\Contact\ContactMessageStatus;
use Database\Factories\ContactMessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    /** @use HasFactory<ContactMessageFactory> */
    use HasFactory;

    protected $fillable = ['full_name', 'email', 'subject', 'category', 'message', 'status'];

    protected function casts(): array
    {
        return [
            'category' => ContactMessageCategory::class,
            'status' => ContactMessageStatus::class,
        ];
    }
}
