<?php

namespace ARC\Blueprint\Test;

use Illuminate\Database\Eloquent\Model;

class TicketReply extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ticket_replies';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ticket_id',
        'message',
        'author_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'ticket_id' => 'integer',
        'author_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the ticket that owns the reply.
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}
