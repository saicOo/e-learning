<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function student()
    {
        return $this->belongsTo(Student::class)->select('students.id','students.name','students.email');
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function answers()
    {
        return $this->hasMany(StudentAnswer::class);
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'student_answers')
        ->select('questions.id', 'questions.title','questions.image', 'questions.correct_option', 'questions.type', 'questions.options')
        ->withPivot(['grade','answer','image']);
    }
}
