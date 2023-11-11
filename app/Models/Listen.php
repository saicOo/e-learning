<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Listen extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = ['video_url'];

    protected $hidden = [
        'video',
        'course_id'
    ];

    public function getVideoUrlAttribute(){
        return Storage::disk('public')->url($this->video);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
