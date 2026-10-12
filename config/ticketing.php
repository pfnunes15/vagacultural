<?php

declare(strict_types=1);

return [
    // Default platform commission (percentage) when neither the event nor the
    // promoter define one.
    'default_commission_rate' => (float) env('VAGA_DEFAULT_COMMISSION', 8.0),

    // Minutes an unpaid order holds its inventory before expiring.
    'hold_minutes' => (int) env('VAGA_TICKET_HOLD_MINUTES', 15),

    'currency' => env('VAGA_TICKET_CURRENCY', 'EUR'),

    // 'fake' (default/dev/tests) or 'stripe'
    'gateway' => env('PAYMENT_GATEWAY', 'fake'),
];
