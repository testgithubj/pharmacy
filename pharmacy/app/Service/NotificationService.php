<?php


namespace App\Service;

use App\Models\Notification;

class NotificationService
{
    public static function send($sender_id, $reciever_id = null, $title = "New Notification", $description = "null")
    {
        try {
            return Notification::create([
                'sender_id' => $sender_id,
                'receiver_id' => $reciever_id,
                'title' => $title,
                'description' => $description,
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}