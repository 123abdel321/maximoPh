<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageUser extends Model
{
    use HasFactory;

    protected $connection = 'max';

    protected $table = "message_users";

    protected $fillable = [
        'message_id',
        'user_id',
    ];
}
