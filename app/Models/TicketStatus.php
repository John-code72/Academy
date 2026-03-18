<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketStatus extends Model
{
    public $table       = 'ticket_status';
    protected $fillable = [
        'icon',
        'title',
        'status',
        'default_view',
        'color',
    ];
}
