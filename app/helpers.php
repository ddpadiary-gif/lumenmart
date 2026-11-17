<?php

if (! function_exists('rupiah')) {
    /**
     * Format angka menjadi Rupiah, contoh: Rp 27.500
     */
    function rupiah(float|int|string|null $value, bool $withPrefix = true): string
    {
        $formatted = number_format((float) $value, 0, ',', '.');

        return $withPrefix ? 'Rp '.$formatted : $formatted;
    }
}
