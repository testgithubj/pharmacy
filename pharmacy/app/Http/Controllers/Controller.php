<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use function App\CPU\purchaseVarification;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public static function setEnv($key, $value)
    {
        $path = base_path('.env');
        if (file_exists($path)) {
            $lines = explode("\n", file_get_contents($path));
            $settings = collect($lines)
                ->filter()
                ->transform(function ($item) {
                    return explode("=", $item, 2);
                })->pluck(1, 0);
            $settings[$key] = $value;
            $rebuilt = $settings->map(function ($value, $key) {
                return "$key=$value";
            })->implode("\n");
            file_put_contents($path, $rebuilt);
        }
    }

    public function fileUploader($image, $path, $width, $height, $oldImage = null)
    {
        $currentDate = Carbon::now()->toDateString();
        $imageName = $currentDate . '-' . uniqid() . '.' . $image->getClientOriginalExtension();
        if (!Storage::disk('public')->exists($path)) {
            Storage::disk('public')->makeDirectory($path);
        }
        if (!empty($oldImage)) {
            if (Storage::disk('public')->exists($path . '/' . $oldImage)) {
                Storage::disk('public')->delete($path . '/' . $oldImage);
            }
        }
        $uploadedImage = Image::make($image)->resize($width, $height)->stream();
        Storage::disk('public')->put($path . '/' . $imageName, $uploadedImage);
        return $imageName;
    }

    protected function deleteFile($path, $image)
    {
        if (!empty($image)) {
            if (Storage::disk('public')->exists($path . '/' . $image)) {
                Storage::disk('public')->delete($path . '/' . $image);
            }
            return true;
        }
        return false;
    }
}