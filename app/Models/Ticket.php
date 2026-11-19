<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'user_id',
        'category_id',
        'assigned_to',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'notes' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
