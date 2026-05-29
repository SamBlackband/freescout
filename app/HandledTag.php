<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HandledTag extends Model
{
    protected $table = 'handled_tags';

    protected $fillable = ['name', 'slug', 'color'];

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'handled_conversation_tag', 'tag_id', 'conversation_id')
            ->withPivot('applied_by_user_id')
            ->withTimestamps();
    }
}
