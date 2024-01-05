<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function course()
    {
        return $this->belongsTo(Course::class)->select('courses.id', 'courses.name', 'courses.image');
    }

    public function student()
    {
        return $this->belongsTo(Student::class)->select('students.id','students.name','students.email');
    }
}
