<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'options' => 'array',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }//end of course

    public function listen()
    {
        return $this->belongsTo(Listen::class);
    }//end of listen


    // public function option()
    // {
    //     return $this->hasOne(Option::class);
    // }//end of Option

    // public function quizzes()
    // {
    //     return $this->belongsToMany(Quiz::class, 'grades')->withPivot(['score','answer']);
    // }//end of quizzes
}
