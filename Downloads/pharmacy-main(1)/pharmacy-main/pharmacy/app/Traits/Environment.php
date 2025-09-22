<?php


namespace App\Traits;


use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

trait Environment
{
    public function setEnvironmentValue($envKey, $envValue)
    {
        try {
            $envFile = app()->environmentFilePath();

            // Read the contents of the .env file
            $contents = File::get($envFile);

            // Create the new line for the environment variable
            $newLine = "{$envKey}=\"{$envValue}\"";

            // Check if the environment variable already exists
            if (strpos($contents, "{$envKey}=") !== false) {
                // Update the existing value
                $contents = preg_replace("/{$envKey}=.*/", $newLine, $contents);
            } else {
                // Add the new environment variable
                $contents .= "\n" . $newLine;
            }

            // Write the updated contents back to the .env file
            File::put($envFile, $contents);

            return true;
        } catch (\Exception $e) {
            // Log any errors that occur
            Log::error("Failed to set environment variable {$envKey}: {$e->getMessage()}");
            return false;
        }
    }
}