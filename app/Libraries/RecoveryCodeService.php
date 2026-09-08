<?php

namespace App\Libraries;

/**
 * Generates and verifies one-time account-recovery codes.
 *
 * A recovery code is shown once (at user creation or on regeneration) and
 * is never stored in plain text — only its hash is persisted, same as
 * passwords. Redeeming it via forgot-password invalidates it; a fresh one
 * must be generated afterwards.
 */
class RecoveryCodeService
{
    // Excludes visually ambiguous characters (0/O, 1/I/L).
    private const ALPHABET  = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
    private const GROUP_LEN = 4;
    private const GROUPS    = 4;

    /** e.g. "7XQK-9F3M-2WJH-PLR8" */
    public function generate(): string
    {
        $groups = [];
        for ($g = 0; $g < self::GROUPS; $g++) {
            $chars = '';
            for ($i = 0; $i < self::GROUP_LEN; $i++) {
                $chars .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
            }
            $groups[] = $chars;
        }

        return implode('-', $groups);
    }

    public function hash(string $plainCode): string
    {
        return password_hash($this->normalize($plainCode), PASSWORD_DEFAULT);
    }

    public function verify(string $plainCode, ?string $hash): bool
    {
        if ($hash === null || $hash === '') {
            return false;
        }

        return password_verify($this->normalize($plainCode), $hash);
    }

    /** Codes are shown uppercase with dashes; people retype inconsistently. */
    private function normalize(string $code): string
    {
        return strtoupper(trim($code));
    }
}