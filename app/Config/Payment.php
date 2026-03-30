<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Household Payment Configuration
 *
 * Set your UPI ID below. This is used to generate the
 * payment QR code shown in the app's profile dropdown.
 *
 * Format:  yourname@bankname  (e.g. sulya@upi, 9876543210@paytm)
 */
class Payment extends BaseConfig
{
    /**
     * Household UPI ID for payments.
     * Change this to your actual UPI ID.
     */
    public string $upiId = 'yourname@bankname';

    /**
     * Display name shown under the QR code.
     * Typically the admin's name or the household name.
     */
    public string $payeeName = 'yourname';

    /**
     * Optional: default payment note pre-filled in the UPI app.
     * Leave empty to skip.
     */
    public string $paymentNote = 'Rent from SmartSplit App';
}