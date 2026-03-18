<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketFaq extends Model
{
    public $table       = 'ticket_faqs';
    protected $fillable = [
        'question',
        'answer',
    ];

}
