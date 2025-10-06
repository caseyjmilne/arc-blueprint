<?php

namespace ARC\Blueprint\Test;

use ARC\Blueprint\Schema;

class TicketSchema extends Schema
{
    protected $collection = TicketCollection::class;

    protected $fields = [
        'priority' => [
            'type' => 'select',
            'options' => ['low', 'medium', 'high', 'urgent'],
            'placeholder' => 'Select priority level',
        ],
        'status' => [
            'type' => 'select',
            'options' => ['open', 'in_progress', 'pending', 'closed'],
            'placeholder' => 'Select status',
        ],
    ];
}
