<?php

return [
    'length' => (int) env('OTP_LENGTH', 5),
    'expiry_seconds' => (int) env('OTP_EXPIRY_SECONDS', 300),
];
