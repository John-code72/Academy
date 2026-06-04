<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketMessage extends Model
{
    public $table       = 'ticket_messages';
    protected $fillable = [
        'ticket_thread_code',
        'message',
        'sender_id',
        'receiver_id',
        'file',
    ];
}
