<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$clientKey = config('midtrans.client_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function createTransaction($orderId, $amount, $customerDetails = null, $itemDetails = null)
    {
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $amount,
            ],
        ];

        if ($customerDetails) {
            $params['customer_details'] = $customerDetails;
        }

        if ($itemDetails) {
            $params['item_details'] = $itemDetails;
        }

        try {
            $snapToken = Snap::getSnapToken($params);
            return $snapToken;
        } catch (\Exception $e) {
            throw new \Exception('Error creating Midtrans transaction: ' . $e->getMessage());
        }
    }

    public function getStatus($orderId)
    {
        // Dalam implementasi nyata, Anda akan menghubungi API Midtrans untuk mendapatkan status
        // Untuk demo, kita akan mengembalikan status pending
        return [
            'status_code' => '200',
            'transaction_status' => 'pending',
            'fraud_status' => 'accept',
        ];
    }
}