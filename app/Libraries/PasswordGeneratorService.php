<?php

namespace App\Libraries;

use InvalidArgumentException;

class PasswordGeneratorService
{
    /**
     * Words used to construct memorable passwords.
     *
     * Keep words:
     * - easy to spell
     * - easy to pronounce
     * - reasonably short
     * - free from offensive/ambiguous terms
     */
    private const WORDS = [
        'apple',
        'arrow',
        'autumn',
        'beach',
        'berry',
        'bird',
        'blue',
        'bridge',
        'breeze',
        'candle',
        'castle',
        'cedar',
        'cloud',
        'coffee',
        'coral',
        'crystal',
        'daisy',
        'dawn',
        'desert',
        'eagle',
        'earth',
        'falcon',
        'forest',
        'garden',
        'gold',
        'harbor',
        'island',
        'jasmine',
        'lemon',
        'maple',
        'meadow',
        'moon',
        'ocean',
        'olive',
        'orange',
        'pearl',
        'pine',
        'planet',
        'rain',
        'rainbow',
        'river',
        'rocket',
        'rose',
        'silver',
        'sky',
        'snow',
        'spring',
        'star',
        'stone',
        'summer',
        'sunset',
        'tiger',
        'valley',
        'violet',
        'water',
        'winter',
    ];

    private const DEFAULT_WORD_COUNT = 4;

    private const MIN_WORD_COUNT = 3;

    private const MAX_WORD_COUNT = 8;

    private const DEFAULT_SEPARATOR = '-';

    /**
     * Generate a memorable password.
     *
     * Example:
     *   River-Mango-47-Forest
     *
     * @param int|null    $wordCount Number of random words.
     * @param string      $separator Separator between words/numbers.
     * @param bool        $includeNumber Whether to include a random number.
     * @param bool        $capitalize Whether to capitalize the first letter
     *                              of each word.
     */
    public function generate(
        ?int $wordCount = null,
        string $separator = self::DEFAULT_SEPARATOR,
        bool $includeNumber = true,
        bool $capitalize = true
    ): string {
        $wordCount ??= self::DEFAULT_WORD_COUNT;

        $this->validateWordCount($wordCount);
        $this->validateSeparator($separator);

        $parts = [];

        for ($i = 0; $i < $wordCount; $i++) {
            $word = $this->randomWord();

            if ($capitalize) {
                $word = ucfirst($word);
            }

            $parts[] = $word;
        }

        if ($includeNumber) {
            // Put the number at a random position rather than always
            // putting it at the end.
            $number = (string) random_int(10, 99);

            $position = random_int(0, count($parts));

            array_splice($parts, $position, 0, [$number]);
        }

        return implode($separator, $parts);
    }

    /**
     * Generate a password with a guaranteed minimum length.
     *
     * This is useful when your application's password policy
     * requires a minimum number of characters.
     */
    public function generateWithMinimumLength(
        int $minimumLength = 16,
        string $separator = self::DEFAULT_SEPARATOR
    ): string {
        if ($minimumLength < 1) {
            throw new InvalidArgumentException(
                'Minimum password length must be greater than zero.'
            );
        }

        $wordCount = self::DEFAULT_WORD_COUNT;

        do {
            $password = $this->generate(
                wordCount: $wordCount,
                separator: $separator
            );

            if (strlen($password) >= $minimumLength) {
                return $password;
            }

            $wordCount++;

        } while ($wordCount <= self::MAX_WORD_COUNT);

        throw new InvalidArgumentException(
            'Unable to generate a password satisfying the requested minimum length.'
        );
    }

    /**
     * Return a cryptographically secure random word.
     */
    private function randomWord(): string
    {
        $index = random_int(0, count(self::WORDS) - 1);

        return self::WORDS[$index];
    }

    private function validateWordCount(int $wordCount): void
    {
        if (
            $wordCount < self::MIN_WORD_COUNT ||
            $wordCount > self::MAX_WORD_COUNT
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    'Word count must be between %d and %d.',
                    self::MIN_WORD_COUNT,
                    self::MAX_WORD_COUNT
                )
            );
        }
    }

    private function validateSeparator(string $separator): void
    {
        if ($separator === '') {
            throw new InvalidArgumentException(
                'Password separator cannot be empty.'
            );
        }

        if (strlen($separator) > 3) {
            throw new InvalidArgumentException(
                'Password separator cannot be longer than 3 characters.'
            );
        }
    }
}