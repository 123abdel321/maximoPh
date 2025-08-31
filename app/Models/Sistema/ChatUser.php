<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
//MODELS
use App\Models\User;

class ChatUser extends Model
{
    use HasFactory;

    protected $connection = 'max';

    protected $table = "chat_users";

    protected $fillable = [
        'chat_id',
        'user_id',
        'created_by',
        'updated_by',
    ];

    public function usuario (){
        return $this->belongsTo(User::class, "user_id");
    }
}
