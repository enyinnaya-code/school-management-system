<?php

use App\Models\SchoolSetting;
use Illuminate\Support\Facades\Cache;

if (!function_exists('school_settings')) {
    function school_settings()
    {
        return Cache::remember('school_settings', 3600, function () {
            return SchoolSetting::first();
        });
    }
}

if (!function_exists('school_name')) {
    function school_name()
    {
        $settings = school_settings();
        return $settings?->school_name ?? 'School Management System';
    }
}

if (!function_exists('school_logo')) {
    function school_logo()
    {
        $settings = school_settings();
        if ($settings && $settings->logo) {
            return asset('storage/logos/' . $settings->logo);
        }
        return asset('images/default-logo.png'); // fallback
    }
}