<?php

namespace App\Observers;

use App\Models\Sistema\Message;
use App\Events\PrivateMessageEvent;
use Illuminate\Support\Facades\Auth;

class MessageObserver
{
    /**
     * Handle the Message "created" event.
     */
    public function created(Message $message): void
    {
        // event(new PrivateMessageEvent('mensajeria-'.$this->token_db, [
        //     'chat_id' => $message->id,
        //     'permisos' => '',
        //     'action' => 'actualizar_estados'
        // ]));
    }

    /**
     * Handle the Message "updated" event.
     */
    public function updated(Message $message): void
    {
        $user = Auth::user();

        if ($user) {
            event(new PrivateMessageEvent('mensajeria-'.$user->has_empresa.'_'.$message->user_id, [
                'chat_id' => $message->chat_id,
                'permisos' => '',
                'action' => 'actualizar_estados'
            ]));
        }
    }

    /**
     * Handle the Message "deleted" event.
     */
    public function deleted(Message $message): void
    {
        dd('asdasd');
    }

    /**
     * Handle the Message "restored" event.
     */
    public function restored(Message $message): void
    {
        dd('asdasd');
    }

    /**
     * Handle the Message "force deleted" event.
     */
    public function forceDeleted(Message $message): void
    {
        dd('asdasd');
    }
}
