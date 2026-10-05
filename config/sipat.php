<?php

return [

    'throttle' => [
        'pendaftaran' => env('THROTTLE_PENDAFTARAN', 20),
        'status' => env('THROTTLE_STATUS', 120),
        'login' => env('THROTTLE_PASIEN_LOGIN', 10),
    ],

];
