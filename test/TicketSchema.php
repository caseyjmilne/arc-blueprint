<?php

namespace ARC\Blueprint\Test;

use ARC\Blueprint\Schema;

class TicketSchema extends Schema
{
    protected $collection = TicketCollection::class;

    protected $fields = [
        'title' => [
            'label' => 'Title',
            'required' => true,
            'placeholder' => 'Enter ticket title',
        ],
        'description' => [
            'type' => 'textarea',
            'label' => 'Description',
            'required' => true,
            'placeholder' => 'Describe the issue or request',
            'rows' => 5,
        ],
        'contact_email' => [
            'type' => 'email',
            'label' => 'Contact Email',
            'placeholder' => 'your@email.com',
        ],
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
