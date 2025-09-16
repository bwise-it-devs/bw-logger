<?php
namespace BwiseMedia\BWLogger\Helpers;

final class ContextSanitizer
{
    /**
     * Maschera chiavi sensibili e ripulisce valori noti (email, Bearer token).
     *
     * @param array $context
     * @param array $keysToMask  es. ['password','token','authorization','cookie']
     * @param string $mask
     * @return array
     */
    public static function sanitize(array $context, array $keysToMask = [], string $mask = '***'): array
    {
        $lowerKeys = array_map(static fn($k) => mb_strtolower((string) $k), $keysToMask);

        $walk = function ($value) use (&$walk, $lowerKeys, $mask) {
            if (is_array($value)) {
                $out = [];
                foreach ($value as $k => $v) {
                    $lk = is_string($k) ? mb_strtolower($k) : $k;
                    if (is_string($k) && in_array($lk, $lowerKeys, true)) {
                        $out[$k] = $mask;
                    } else {
                        $out[$k] = $walk($v);
                    }
                }
                return $out;
            }

            if (is_object($value)) {
                // serializza l'oggetto a array e continua
                return $walk((array) $value);
            }

            if (is_string($value)) {
                // Bearer token
                if (preg_match('/^Bearer\\s+\\S+/i', $value)) {
                    return 'Bearer ' . $mask;
                }
                // email (maschera utente)
                if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    [$u, $d] = explode('@', $value, 2);
                    return (mb_substr($u, 0, 1) . '***@' . $d);
                }
            }

            return $value;
        };

        return $walk($context);
    }
}
