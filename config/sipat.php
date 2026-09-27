<?php

return [

    'throttle' => [
        'pendaftaran' => env('THROTTLE_PENDAFTARAN', 20),
        'status' => env('THROTTLE_STATUS', 120),
    ],

];
