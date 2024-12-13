<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $connection = 'max';

    protected $table = "chats";

    protected $fillable = [
        'name',
        'relation_id',
        'relation_type',
        'is_group',
        'created_by',
        'updated_by',
    ];

    public function relation()
    {
        return $this->morphTo();
    }

    public function personas()
    {
        return $this->hasMany(ChatUser::class, "chat_id", "id");
	}

    public function mensajes()
    {
        return $this->hasMany(Message::class, "chat_id", "id");
	}

    public function ultimo_mensaje()
    {
        return $this->hasOne(Message::class, 'chat_id', 'id')->latest('created_at');
    }

    public function scopeConUsuario($query, $userId)
    {
        return $query->whereHas('personas', function($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }
}
