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
    protected $appends = ['images_url','is_passed','status_passed'];
    protected $hidden = [
        'images',
    ];

    public function getImagesUrlAttribute(){
        $images = [];
        if($this->images){
            foreach ($this->images as $image) {
                $image_url = Storage::disk('public')->url($image);
                array_push($images , $image_url);
            }
        }
        return $images;
    }

    public function getIsPassedAttribute(){
        $is_passed = false;
        if($this->status == "successful" && $this->is_visited){
            $is_passed = true;
        }
        return $is_passed;
    }

    public function getStatusPassedAttribute(){
        $status_passed = "started";
        if($this->is_visited && $this->status == "failed"){
            $status_passed = "repetition";
        }
        if($this->is_visited && $this->status == "successful"){
            $status_passed = "compleated";
        }
        if(!$this->is_visited && $this->is_submit){
            $status_passed = "review";
        }
        return $status_passed;
    }

    public function student()
    {
        return $this->belongsTo(Student::class)->select('students.id','students.name','students.email');
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
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
