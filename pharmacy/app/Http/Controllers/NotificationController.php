<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $collection = Notification::with('sender')->latest()->paginate(10);
        return view('notification.index', compact('collection'));
    }

    public function show($id)
    {
        $notification = Notification::with('sender')->findOrFail($id);
        $notification->seen = true;
        $notification->save();
        return view('notification.view', compact('notification'));
    }
}
