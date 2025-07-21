<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Yajra\DataTables\Html\Editor\Fields\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $guarded = [];


    public function getCreatedAtAttribute($value)
    {
        // Parse the 'created_at' value as a Carbon instance
        $createdAt = Carbon::parse($value);

        // Calculate the difference between the created_at date and the current date
        $diff = $createdAt->diffForHumans();

        // Return the human-readable representation of the difference
        return $diff;
    }

    public function sender()
    {
        return $this->belongsTo(Customer::class,'sender_id','id');
    }
}
