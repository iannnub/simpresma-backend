<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * SafeUrl Validation Rule
 * Ensures the given URL uses HTTP/HTTPS and prevents Server-Side Request Forgery (SSRF)
 * by rejecting localhost, loopback, and private IP ranges.
 */
class SafeUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || !filter_var($value, FILTER_VALIDATE_URL)) {
            $fail("Format tautan {$attribute} tidak valid.");
            return;
        }

        $parts = parse_url($value);
        $scheme = strtolower($parts['scheme'] ?? '');
        $host = strtolower($parts['host'] ?? '');

        // Only allow HTTP and HTTPS
        if (!in_array($scheme, ['http', 'https'], true)) {
            $fail("Tautan {$attribute} hanya boleh menggunakan protokol HTTP atau HTTPS.");
            return;
        }

        // Prevent SSRF: loopback, localhost, and reserved internal domain names
        if (
            $host === 'localhost' ||
            $host === '127.0.0.1' ||
            $host === '0.0.0.0' ||
            $host === '::1' ||
            str_ends_with($host, '.localhost') ||
            str_ends_with($host, '.local') ||
            str_ends_with($host, '.internal')
        ) {
            $fail("Tautan {$attribute} tidak boleh mengarah ke alamat internal atau localhost.");
            return;
        }

        // Check if host is a raw IP address (prevent private and reserved network addresses)
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            if (!filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                $fail("Tautan {$attribute} tidak boleh mengarah ke alamat IP privat.");
                return;
            }
        }
    }
}
