<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Course extends Model
{
    use HasFactory;

    protected $guarded = [];
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

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lessons(){
        return $this->hasMany(Lesson::class);
    }

    public function quizzes()
    {
        return $this->belongsToMany(Quiz::class, 'quiz_course');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'subscriptions')->select('students.id', 'students.name', 'students.email')
        ->withPivot('start_date', 'end_date')
        ->where('end_date', '>', now());
    }

    public function sessions()
    {
        return $this->hasMany(Session::class);
    }

    public function progress()
    {
        return $this->hasOne(QuizProcess::class);
    }

}
