<?php

namespace ARC\Blueprint\Test;

class TicketReplyCollection extends \ARC\Gateway\Collection
{
    /**
     * @var string The Eloquent model this collection manages
     */
    protected $model = TicketReply::class;

    /**
     * @var array API route configuration
     */
    protected $routes = [
        'enabled' => true,
        'prefix' => 'ticket_replys',
        'methods' => [
            'get_many' => true,
            'get_one' => true,
            'create' => true,
            'update' => true,
            'delete' => true,
        ],
        'middleware' => [],
        'permissions' => [
            'get_many' => 'read',
            'get_one' => 'read',
            'create' => 'edit_posts',
            'update' => 'edit_posts',
            'delete' => 'delete_posts',
        ],
    ];

    /**
     * @var array Model analysis configuration
     */
    protected $config = [
        'searchable' => ['message'],
        'filterable' => ['ticket_id', 'author_id'],
        'sortable' => ['created_at', 'updated_at'],
        'relations' => ['ticket'],
        'hidden' => [],
        'appends' => [],
        'per_page' => 15,
        'max_per_page' => 100,
    ];
}
