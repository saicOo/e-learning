<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class Student extends Authenticatable
{
    use HasApiTokens, HasFactory,Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'image',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(){
        if($this->image){
            return Storage::disk('public')->url($this->image);
        }
        return null;
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'subscriptions')->select('courses.id', 'courses.name', 'courses.image', 'courses.user_id','courses.publish')
        ->withPivot('start_date', 'end_date')->where('end_date', '>', now());
    }

    public function sessions()
    {
        return $this->belongsToMany(Session::class, 'attendances')->withPivot('status');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function hasSessions($courseId)
    {
        $sessions = $this->sessions()->with('offlineExam')
            ->where('course_id', $courseId)
            ->get();

        return $sessions;
    }

    public function hasCurrentLesson($currentLessonId)
    {
        // Check if the user has a attempt for the test associated with the current lesson
        $attempt = $this->attempts()
            ->where('lesson_id', $currentLessonId)
            ->whereNotNull('quiz_id')
            ->first();

        // Check if the attempt exists and the grade is above a passing threshold
        return $attempt;
    }

    public function hasCurrentCourse($currentCourseId)
    {
        // Check if the user has a attempt for the test associated with the current lesson
        $attempt = $this->attempts()
            ->where('course_id', $currentCourseId)
            ->whereNull('lesson_id')
            ->whereNotNull('quiz_id')
            ->first();

        // Check if the attempt exists and the grade is above a passing threshold
        return $attempt;
    }

}
