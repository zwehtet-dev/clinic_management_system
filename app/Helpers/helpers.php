<?php

if (!function_exists('setting')) {
    function setting(string $key, $default = null) {
        return \App\Models\Setting::get($key, $default);
    }
}

if (!function_exists('mb_split')) {
    function mb_split($pattern, $string, $limit = -1) {
        return preg_split('/' . $pattern . '/u', $string, $limit);
    }
}

