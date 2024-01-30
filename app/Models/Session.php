<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;
    public $guarded = [];
    // public $timestamps = false;
    public function course()
    {
        return $this->belongsTo(Course::class)->select('courses.id', 'courses.name', 'courses.image');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'attendances')->select('students.id', 'students.name', 'students.email')
        ->withPivot('status');
    }
}
