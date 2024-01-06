<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $casts = [
        'images' => 'array',
    ];
    protected $appends = ['images_url'];
    protected $hidden = [
        'images',
    ];

    public function getImagesUrlAttribute(){
        $images = [];
            foreach ($this->images as $image) {
                $image_url = Storage::disk('public')->url($image);
                array_push($images , $image_url);
            }
        return $images;
    }
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
        ->select('questions.id', 'questions.title','questions.grade','questions.image', 'questions.correct_option', 'questions.type', 'questions.options')
        ->withPivot(['grade','answer']);
    }
}
