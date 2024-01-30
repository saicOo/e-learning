<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lesson extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = ['video_url','attached_url'];

    protected $hidden = [
        'video',
        'attached',
    ];


    public function getVideoUrlAttribute(){
        if ($this->video) {
            if ($this->video_type == 'file') {
                return Storage::disk('public')->url($this->video);
            } else {
                return $this->video;
            }
        }
        return null;
    }
    public function getAttachedUrlAttribute(){
        if($this->attached){
            return Storage::disk('public')->url($this->attached);
        }
        return null;
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function quizzes()
    {
        return $this->belongsToMany(Quiz::class, 'quiz_lesson');
    }

    // public function progress()
    // {
    //     return $this->hasMany(QuizProcess::class);
    // }

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function reorderLessons($newOrder, $oldOrder)
    {
        if ($oldOrder < $newOrder) {
            Lesson::where('course_id', $this->course_id)
                ->where('id', '!=', $this->id)
                ->whereBetween('order', [$oldOrder, $newOrder])
                ->decrement('order');
        } elseif ($oldOrder > $newOrder) {
            Lesson::where('course_id', $this->course_id)
                ->where('id', '!=', $this->id)
                ->whereBetween('order', [$newOrder, $oldOrder])
                ->increment('order');
        }
    }
}
