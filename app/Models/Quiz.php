<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'quiz_question');
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'quiz_course')->withPivot('duration');
    }

    public function lessons()
    {
        return $this->belongsToMany(Lesson::class, 'quiz_lesson')->withPivot('duration');
    }

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
