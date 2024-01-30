<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'options' => 'array',
    ];

    protected $appends = ['image_url'];

    protected $hidden = [
        'image',
    ];

    public function getImageUrlAttribute(){
        if($this->image){
            return Storage::disk('public')->url($this->image);
        }
        return null;
    }
    public function course()
    {
        return $this->belongsTo(Course::class);
    }//end of course

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }//end of lesson


    // public function option()
    // {
    //     return $this->hasOne(Option::class);
    // }//end of Option

    // public function quizzes()
    // {
    //     return $this->belongsToMany(Quiz::class, 'grades')->withPivot(['score','answer']);
    // }//end of quizzes
}
