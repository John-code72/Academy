<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketMacro extends Model
{
    public $table       = 'ticket_macros';
    protected $fillable = [
        'title',
        'description',
    ];
}
