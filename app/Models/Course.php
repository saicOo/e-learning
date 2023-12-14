<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $appends = ['listens_count'];

    public function getListensCountAttribute(){
        return $this->listens->count();
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

    public function listens(){
        return $this->hasMany(Listen::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
