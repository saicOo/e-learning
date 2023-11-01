<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function listens(){
        return $this->hasMany(Listen::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class)->withPivot(['enrollment_date','completion_date']);
    }
}
