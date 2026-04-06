<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class PayhereSettings extends Model
{
    protected $fillable = [
        'merchant_id_live',
        'merchant_secret_live',
        'merchant_id_sandbox',
        'merchant_secret_sandbox',
        'mode',
    ];

    /**
     * Retrieve the single settings row, creating defaults if needed.
     */
    public static function instance(): static
    {
        return static::firstOrCreate([], [
            'mode' => 'sandbox',
        ]);
    }

    /**
     * Active merchant ID based on current mode.
     */
    public function getMerchantId(): string
    {
        return $this->mode === 'live'
            ? ($this->merchant_id_live ?? '')
            : ($this->merchant_id_sandbox ?? '');
    }

    /**
     * Active merchant secret (decrypted) based on current mode.
     */
    public function getMerchantSecret(): string
    {
        $encrypted = $this->mode === 'live'
            ? $this->merchant_secret_live
            : $this->merchant_secret_sandbox;

        if (! $encrypted) return '';

        try {
            return Crypt::decryptString($encrypted);
        } catch (\Exception) {
            return '';
        }
    }

    /**
     * Active checkout URL.
     */
    public function getCheckoutUrl(): string
    {
        return $this->mode === 'live'
            ? 'https://www.payhere.lk/pay/checkout'
            : 'https://sandbox.payhere.lk/pay/checkout';
    }

    public function isLive(): bool
    {
        return $this->mode === 'live';
    }
}
