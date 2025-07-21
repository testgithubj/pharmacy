<?php

use App\Models\Shop;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\File;

if (!function_exists('uniqueOrderId')) {
    function uniqueOrderId($offerNo, $prefix, $table, $column)
    {
        $offerNo = $prefix;
        $numberLen = 10 - strlen($offerNo);
        $order_id = $offerNo . strtoupper(substr(str_shuffle("0123456789"), -$numberLen));

        $check_path = DB::table($table)->select($column)->where($column, 'like', $order_id . '%')->get();
        if (count($check_path) > 0) {
            //find slug until find not used.
            for ($i = 1; $i <= 99; $i++) {
                $new_order_id = $offerNo . strtoupper(substr(str_shuffle("0123456789"), -$numberLen));
                if (!$check_path->contains($column, $new_order_id)) {
                    return $new_order_id;
                }
            }
        } else {
            return $order_id;
        }
    }
}


function active_if_full_match($path)
{
    return Request::is($path) ? 'active' : '';
}

function active_if_match($paths)
{
    if (is_array($paths)){
        foreach ($paths ?? [] as $path) {
            if (Request::is($path . '*')) {
                return 'sidebar-group-active open';
            }
        }
    }else {
        return Request::is($paths . '*') ? 'sidebar-group-active open' : '';
    }
    return '';
}


function list_days_old($date_from, $date_to)
{
    $period = new DatePeriod(
        new DateTime('2010-10-01'),
        new DateInterval('P1D'),
        new DateTime('2010-10-05')
    );
    return $period;
}

function list_days($first, $last, $step = '+1 day', $output_format = 'Y-m-d')
{

    $dates = array();
    $current = strtotime($first);
    $last = strtotime($last);

    while ($current <= $last) {

        $dates[] = date($output_format, $current);
        $current = strtotime($step, $current);
    }

    return $dates;
}

function remove_invalid_charcaters($str)
{
    return str_ireplace(['\'', '"', ',', ';', '<', '>', '?'], ' ', $str);
}

function generateRandomString($length = 5)
{
    $characters = '0123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}


if (!function_exists('overWriteEnvFile')) {
    function overWriteEnvFile($type, $val)
    {
        $path = base_path('.env');
        if (file_exists($path)) {
            $val = '"' . trim($val) . '"';
            if (is_numeric(strpos(file_get_contents($path), $type)) && strpos(file_get_contents($path), $type) >= 0) {
                file_put_contents($path, str_replace(
                    $type . '="' . env($type) . '"', $type . '=' . $val, file_get_contents($path)
                ));
            } else {
                file_put_contents($path, file_get_contents($path) . "\r\n" . $type . '=' . $val);
            }
        }
    }
}

if (!function_exists('setting')) {
    function appSetting($name, $default = null)
    {
        $setting = Shop::query()->first();
        if ($setting) {
            return $setting[$name];
        }
        return $default;
    }
}

if (!function_exists('priceFormat')){
    function priceFormat($price){
        return setting('currency').''.number_format($price, 2);
    }
}


if (!function_exists('successAlert')){
    function successAlert($message = 'Success'){
        Toastr::success($message, '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
    }
}

if (!function_exists('errorAlert')){
    function errorAlert($message = 'Something Went Wrong'){
        Toastr::error($message, '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
    }
}



if (!function_exists('setting')){
    function setting($name, $default = null){
        return \Illuminate\Support\Facades\Cache::remember('setting_' . $name, now()->addHours(24), function () use ($name, $default) {
            $setting = DB::table('settings')->where('name', $name)->first();
            if ($setting) {
                return $setting->value;
            }
            return $default;
        });
    }
}


if (!function_exists('globalAsset')){
    function globalAsset($file, $folderName = null){
        $folder = isset($folderName) ? $folderName.'/' :'';
        if (!empty($file)){
            return  asset('storage/'.$folder.$file);
        }
        return asset('images/noimage.png');
    }
}

if (!function_exists('hasPermission')){
    function hasPermission($route){
        return auth()->user()->hasAnyPermission($route) ?? false;
    }
}


if (!function_exists('translate')){} {
    function translate($key)
    {
        try {
            $local = auth()->user()->lang ?? app()->getLocale();
            $langPath = resource_path('lang/' . $local . '/');
            if (!file_exists($langPath)) {
                mkdir($langPath, 0777, true);
            }
            $parts = explode('.', $key);
            if (count($parts) === 2) {
                $file = base_path("resources/lang/{$local}/{$parts[0]}.json");
                $translationKey = strtolower(str_replace([' ', '/', '', '-'], '_', $parts[1]));
                return putContent($file, $translationKey);
            } else {
                $file = base_path("resources/lang/{$local}/{$local}.json");
                $translationKey = strtolower(str_replace([' ', '/', '', '-'], '_', $key));
                return putContent($file, $translationKey);
            }
            return $key;
        } catch (\Exception $exception) {
            return $key;
        }
    }
}

function putContent($file, $translationKey)
{
    if (!File::exists($file)) {
        File::put($file, '{}');
    }

    $translations = json_decode(File::get($file), true);
    if (!isset($translations[$translationKey])) {
        $translations[$translationKey] = ucwords(str_replace(['/', ' ', '-','_'], ' ', $translationKey));
        File::put($file, json_encode($translations, JSON_PRETTY_PRINT));
    }
    return $translations[$translationKey];
}

function updateContent($file, $jsonString)
{
    if (!File::exists($file)) {
        File::put($file, '{}');
    }
    if (!empty($jsonString)) {
        File::put($file, json_encode($jsonString, JSON_PRETTY_PRINT));
    }
    return true;
}