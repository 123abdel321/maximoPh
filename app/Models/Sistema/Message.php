<?php

namespace App\Models\Sistema;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
//MODELS
use App\Models\User;

class Message extends Model
{
    use HasFactory;

    protected $connection = 'max';

    protected $table = "messages";

    protected $fillable = [
        'chat_id',
        'user_id',
        'content',
        'status'
    ];

    public function usuario (){
        return $this->belongsTo(User::class, "user_id");
    }

    public function getFormattedCreatedAtAttribute()
    {
        $createdAt = $this->created_at;
        $now = Carbon::now();

        if ($createdAt->isToday()) {
            // Hoy: solo muestra la hora en formato 12 horas
            return $createdAt->format('h:i A');
        } elseif ($createdAt->isYesterday()) {
            // Ayer
            return 'Ayer';
        } elseif ($createdAt->isSameYear($now)) {
            // Mismo año: muestra el día y mes
            return $createdAt->format('d M');
        } else {
            // Año diferente: muestra día, mes y año
            return $createdAt->format('d M Y');
        }
    }
}
