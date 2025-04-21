<?php

namespace App\Services\Sms;

class Utils
{
    public static function array_to_object_recursive(array $data)
    {
        $result = [];
        foreach ($data as $key => $value) {
            $result[$key] = is_array($value) ? static::array_to_object_recursive($value) : $value;
        }
        return (object)$result;
    }

    public static function deep_merge_array(array $reference, array $data, $keep_extras = false)
    {
        $result = [];
        foreach ($reference as $key => $value) {
            if (!empty($data[$key])) {
                if (is_array($data[$key])) {
                    $result[$key] = static::deep_merge_array_to_object($value, $data[$key], $keep_extras);
                } else {
                    $result[$key] = $data[$key];
                }
            } else {
                $result[$key] = $value;
            }
        }
        if ($keep_extras) {
            foreach ($data as $key => $value) {
                if (empty($reference[$key])) {
                    if (is_array(value: $value)) {
                        $result[$key] = static::deep_merge_array_to_object([], $value, $keep_extras);
                    } else {
                        $result[$key] = $value;
                    }
                }
            }
        }
        return $result;
    }

    public static function deep_merge_array_to_object(array $reference, array $data, $keep_extras = false)
    {
        return static::array_to_object_recursive(static::deep_merge_array($reference, $data, $keep_extras));
    }

    public static function standardize_arabic($text)
    {
        $arabicToPersianMap = [
            'ك' => 'ک',
            'ي' => 'ی',
            'ة' => 'ه',
            'ى' => 'ی',
            'ؤ' => 'و',
            'إ' => 'ا',
            'أ' => 'ا',
            'ٱ' => 'ا',
            'ئ' => 'ی'
        ];
        return strtr($text, $arabicToPersianMap);;
    }

    public static function change_number_format($text, $number_format = "english")
    {
        /// REPLACE NUMBERS BASED ON DESIRED NUMBER FORMAT
        $persian_numbers = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic_numbers = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $english_numbers = range(0, 9);
        if ($number_format === "english") {
            $text = str_replace($persian_numbers, $english_numbers, $text);
            $text = str_replace($arabic_numbers, $english_numbers, $text);
        } else {
            $text = str_replace($english_numbers, $persian_numbers, $text);
            $text = str_replace($arabic_numbers, $persian_numbers, $text);
        }
        return $text;
    }

    public static function clean_up_sms_text($text, $number_format = "english", $append_suffix = true)
    {
        /// TRIM THE TEXT
        $text = trim($text);

        if ($append_suffix) {
            /// APPEND OBLIGATORY SUFFIX
            //TODO: GET END STRING FROM SETTINGS
            $suffix = "لغو11";
            if (!str_ends_with($text, $suffix)) {
                $text .= "\n" . $suffix;
            }
        }

        $text = static::change_number_format($text, $number_format);

        return $text;
    }

    public static function generate_sms_content_hash($request_id, $text)
    {
        return crc32($request_id . "|" . $text);
    }
}
