<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Conversation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Document;

class Message extends Model
{
    protected $fillable = [
        'conversation_id',
        'role',
        'content',
        'source',
        'document_id'
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
