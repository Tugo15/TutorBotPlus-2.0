<?php

namespace App\Support;

use Carbon\Carbon;

class DateHelper
{
    /**
     * Safely parse a date value (raw attribute or formatted string) to a Unix timestamp.
     * Returns 0 if empty or unparseable instead of throwing an exception.
     */
    public static function timestamp($value, $fallback = null): int
    {
        foreach ([$value, $fallback] as $candidate) {
            if ($candidate === null || $candidate === '') {
                continue;
            }
            if ($candidate instanceof \DateTimeInterface) {
                return $candidate->getTimestamp();
            }
            if (!is_string($candidate)) {
                continue;
            }
            $trimmed = trim($candidate);
            $lower = mb_strtolower($trimmed);
            if ($trimmed === '' || $trimmed === '-' || $lower === 'no definido' || $lower === 'indefinida' || $lower === 'inmediata' || $lower === 'desconocido') {
                continue;
            }
            foreach (['Y-m-d H:i:s', 'Y-m-d H:i', 'd-m-Y H:i:s', 'd-m-Y H:i', 'd/m/Y H:i:s', 'd/m/Y H:i', 'Y-m-d'] as $fmt) {
                try {
                    return Carbon::createFromFormat($fmt, $trimmed)->timestamp;
                } catch (\Throwable $e) {
                }
            }
            try {
                return Carbon::parse($trimmed)->timestamp;
            } catch (\Throwable $e) {
            }
        }
        return 0;
    }
}

