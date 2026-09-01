<?php

/**
 * Bank / KHQR payment profiles for invoices.
 * Select one when creating a Sale or Order → invoice shows that bank QR only.
 *
 * Put QR images in: public/images/
 *   - aba-payment-qr.png
 *   - aclida-payment-qr.png
 */
return [
    'aba' => [
        'name' => 'ABA',
        'label' => 'ABA PAYMENT KHQR',
        'label_km' => 'ទូទាត់តាម ABA',
        'account_name' => 'HENGPOR SOTH',
        'mid'=>"004788362",
        'qr_image' => 'images/aba-payment-qr.png',
        'pay_url' => 'https://pay.ababank.com/oRF8/m9xru676',
        'button' => 'Pay with ABA →',
        'color' => '#0369a1',
    ],
    'aclida' => [
        'name' => 'ACLIDA',
        'label' => 'ACLIDA PAYMENT / KHQR',
        'label_km' => 'ទូទាត់តាម ACLIDA',
        'account_name' => 'HENGPOR SOTH',
        'qr_image' => 'images/aclida-payment-qr.png',
        'pay_url' => '#', // replace with your ACLIDA pay link
        'button' => 'Pay with ACLIDA →',
        'color' => '#0f766e',
    ],
];
