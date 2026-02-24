<?php

if (! function_exists('settings')) {
    function settings()
    {
        return cache()->remember('settings', 24 * 60, function () {
            // return \Modules\Setting\Entities\Setting::firstOrFail();
        });
    }
}

if (! function_exists('format_currency')) {
    function format_currency($value, bool $format = true): string|int|float
    {
        if (! $format) {
            return $value;
        }

        $settings = settings();
        $position = $settings->default_currency_position ?? '2';
        $symbol = $settings->currency->symbol ?? ' $';
        $decimalSeparator = $settings->currency->decimal_separator ?? '.';
        $thousandSeparator = $settings->currency->thousand_separator ?? ',';

        $formatted = number_format((float) $value, 2, $decimalSeparator, $thousandSeparator);

        return $position === 'prefix'
            ? $symbol.$formatted
            : $formatted.$symbol;
    }
}

if (! function_exists('make_reference_id')) {
    function make_reference_id(string $prefix, int|string $number): string
    {
        return $prefix.'-'.str_pad($number, 5, '0', STR_PAD_LEFT);
    }
}

if (! function_exists('array_merge_numeric_values')) {
    function array_merge_numeric_values(array ...$arrays): array
    {
        $merged = [];

        foreach ($arrays as $array) {
            foreach ($array as $key => $value) {
                if (! is_numeric($value)) {
                    continue;
                }

                $merged[$key] = ($merged[$key] ?? 0) + $value;
            }
        }

        return $merged;
    }
}
