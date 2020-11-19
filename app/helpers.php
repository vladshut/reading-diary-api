<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Ramsey\Uuid\Uuid;


if (!function_exists('validate_date_format')) {
    function validate_date_format($date, string $format)
    {
        if (!is_string($date)) {
            return false;
        }

        $d = DateTime::createFromFormat($format, $date);

        return $d && $d->format($format) === $date;
    }
}

if (!function_exists('date_utc')) {
    function date_utc(): string
    {
        return date(get_utc_date_format());
    }
}

if (!function_exists('validate_date_utc_format')) {
    function validate_date_utc_format($date)
    {
        return validate_date_format($date, get_utc_date_format());
    }
}

if (!function_exists('format_date_utc')) {
    function format_date_utc($date)
    {
        if (!$date) {
            return null;
        }

        return $date->format(get_utc_date_format());
    }
}

if (!function_exists('date_from_utc_format_to_db_format')) {
    function date_from_utc_format_to_mysql_format(string $date): string
    {
        return DateTime::createFromFormat(get_utc_date_format(), $date)->format(get_db_date_format());
    }
}

if (!function_exists('get_utc_date_format')) {
    function get_utc_date_format(): string
    {
        return 'Y-m-d\TH:i:s\Z';
    }
}

if (!function_exists('get_db_date_format')) {
    function get_db_date_format(): string
    {
        return 'Y-m-d H:i:s';
    }
}

if (!function_exists('random_string')) {
    /**
     * @param int $length
     * @param string $characters
     * @return string
     */
    function random_string(int $length = 10, string $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
    {
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}

if (!function_exists('random_string_num')) {
    /**
     * @param int $length
     * @return string
     */
    function random_string_num(int $length = 10)
    {
        return random_string($length, '0123456789');
    }
}


if (!function_exists('uuid4')) {
    function uuid4()
    {
        return Uuid::uuid4();
    }
}

if (!function_exists('rename_key')) {
    function rename_key(string $old, string $new, array &$arr): void
    {
        $value = null;

        if (isset($arr[$old])) {
            $value = $arr[$old];
            unset($arr[$old]);
        }

        $arr[$new] = $value;
    }
}


if (!function_exists('array_only')) {
    function array_only(array $array, array $keys, array $keysToRename = []): array
    {
        $keys = array_merge($keys, array_keys($keysToRename));

        $result = Arr::only($array, $keys);

        foreach ($keysToRename as $old => $new) {
            rename_key($old, $new, $result);
        }

        return $result;
    }
}

if (!function_exists('is_array_assoc')) {
    function is_array_assoc($input): bool
    {
        if (!is_array($input)) {
            return false;
        }

        $arr = $input;

        if ([] === $arr) {
            return false;
        }

        ksort($arr);

        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}

if (!function_exists('validate_phone_e14')) {
    function validate_phone_e14($phone): bool
    {
        if (!is_string($phone)) {
            return false;
        }

        return preg_match('/^\+[1-9]\d{1,14}$/', $phone) > 0;
    }
}

if (!function_exists('validate_ip')) {
    function validate_ip($ip): bool
    {
        if (!is_string($ip)) {
            return false;
        }

        return (bool) filter_var($ip, FILTER_VALIDATE_IP);
    }
}

if (!function_exists('is_json')) {
    function is_json($input)
    {
        if ($input === '') {
            return false;
        }

        json_decode($input);

        if (json_last_error()) {
            return false;
        }

        return true;
    }
}



if (!function_exists('get_script_name')) {
    function get_script_name()
    {
        return 'RVA_' . strtoupper(config('app.env'));
    }
}

if (!function_exists('get_media_id_by_public_url')) {
    function get_media_id_by_public_url(string $publicUrl): int
    {
        $parts = explode('/', $publicUrl);

        return (int)($parts[count($parts) - 2] ?? 0);
    }
}

if (!function_exists('is_image')) {

    function is_image(string $path): bool
    {
        $contentType = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path);

        return starts_with($contentType, 'image/');
    }
}

if (!function_exists('is_video')) {

    function is_video(string $path): bool
    {
        $contentType = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path);

        return starts_with($contentType, 'video/');
    }
}

if (!function_exists('is_pdf')) {

    function is_pdf(string $path): bool
    {
        return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path) === 'application/pdf';
    }
}

if (!function_exists('get_file_type')) {

    function get_file_type(string $path): ?string
    {
        if (is_image($path)) {
            return 'image';
        }

        if (is_video($path)) {
            return 'video';
        }

        if (is_pdf($path)) {
            return 'pdf';
        }

        return null;
    }
}
