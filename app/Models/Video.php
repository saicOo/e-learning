<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $appends = ['shared_count'];
    protected $hidden = [
        'video','video_type'
    ];
    
    public $timestamps = false;

    public function getSharedCountAttribute(){
        return $this->lesson()->count();
    }
    public function lesson()
    {
        return $this->hasMany(Lesson::class);
    }
}
