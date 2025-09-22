<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Traits\Environment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    use Environment;

    public function generalSetting(Request $request)
    {
        if ($request->method() == 'POST') {
            $settings = $request->except('_token');
    
            $logo = $request->file('logo');
            $favicon = $request->file('favicon');
    
            if (!empty($logo)) {
                // Validate the logo file type before uploading
                if (in_array($logo->getClientMimeType(), ['image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/webp'])) {
                    $settings['logo'] = $this->fileUploader($logo, 'settings', '250', '55', setting('logo'));
                } else {
                    // Handle invalid file type
                    return redirect()->back()->withErrors('Logo must be an image (JPG, PNG, GIF, BMP, or WebP).');
                }
            }
    
            if (!empty($favicon)) {
                // Validate the favicon file type before uploading
                if (in_array($favicon->getClientMimeType(), ['image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/webp'])) {
                    $settings['favicon'] = $this->fileUploader($favicon, 'settings', '80', '80', setting('favicon'));
                } else {
                    // Handle invalid file type
                    return redirect()->back()->withErrors('Favicon must be an image (JPG, PNG, GIF, BMP, or WebP).');
                }
            }
    
            foreach ($settings as $name => $value) {
                Setting::updateOrCreate(
                    ['name' => $name],
                    ['value' => $value]
                );
                Cache::forget('setting_' . $name);
            }
    
            successAlert('Setting updated successfully');
            return redirect()->back();
        }
    
        return view('settings.general_setting');
    }
    

    public function emailSetting(Request $request)
    {
        if ($request->method() == 'POST') {
            $request->validate([
                "MAIL_DRIVER" => 'required',
                "MAIL_HOST" => 'required',
                "MAIL_PORT" => 'required',
                "MAIL_USERNAME" => 'required',
                "MAIL_PASSWORD" => 'required',
                "MAIL_ENCRYPTION" => 'required',
                "MAIL_FROM_ADDRESS" => 'required',
                "MAIL_FROM_NAME" => 'required',
            ]);
            $settings = $request->except('_token');
            foreach ($settings as $name => $value) {
                Setting::updateOrCreate(
                    ['name' => $name],
                    ['value' => $value]
                );
                Cache::forget('setting_' . $name);
            }
            $this->setEnvironmentValue('MAIL_DRIVER',$request->MAIL_DRIVER);
            $this->setEnvironmentValue('MAIL_HOST',$request->MAIL_HOST);
            $this->setEnvironmentValue('MAIL_PORT',$request->MAIL_PORT);
            $this->setEnvironmentValue('MAIL_USERNAME',$request->MAIL_USERNAME);
            $this->setEnvironmentValue('MAIL_PASSWORD',$request->MAIL_PASSWORD);
            $this->setEnvironmentValue('MAIL_ENCRYPTION',$request->MAIL_ENCRYPTION);
            $this->setEnvironmentValue('MAIL_FROM_ADDRESS',$request->MAIL_FROM_ADDRESS);
            $this->setEnvironmentValue('MAIL_FROM_NAME',$request->MAIL_FROM_NAME);
            successAlert('Setting updated successfully');
            return redirect()->back();
        }
        return view('settings.email_setting');
    }


}