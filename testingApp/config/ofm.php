<?php

return [
  'databases' => [
    env('FM_FILE_KEY', 'database') => [
      'server' => env('FM_SERVER', ''),
      'file' => env('FM_FILE', ''),
      'user' => env('FM_USER', ''),
      'password' => env('FM_PASSWORD', ''),
    ],
  ],
  'mode' => 'production',
  'debug' => FALSE,
];
