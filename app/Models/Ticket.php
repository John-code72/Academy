<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    public $table       = 'tickets';
    protected $fillable = [
        'subject',
        'code',
        'creator_id',
        'user_id',
        'status_id',
        'priority_id',
        'category_id',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function status()
    {
        return $this->belongsTo(TicketStatus::class, 'status_id');
    }
    public function priority()
    {
        return $this->belongsTo(TicketPriority::class, 'priority_id');
    }
    public function category()
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }
}
