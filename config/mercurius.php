<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mercurius Models
    |--------------------------------------------------------------------------
    |
    | Defines the models used with Mercurius, you can use this to extend your
    | project by placing your own class implementation.
    |
    */

    'models' => [
        'user'          => App\User::class,
        'messages'      => Launcher\Mercurius\Models\Message::class,
        'conversations' => Launcher\Mercurius\Models\Conversation::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Display "is typing..."
    |--------------------------------------------------------------------------
    |
    | When typing a message, we can display a message to the receiver.
    |
    */
    'fields' => [
         'name'   => ['first_name', 'last_name'],
        'avatar' => 'avatar',
    ],

    'display_user_is_typing' => true,

];
