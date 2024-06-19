<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;
    protected $table = 'questions';
    protected $guarded = [];

    protected $hidden = ['answers','created_at', 'updated_at', 'form_id'];

    public function answers()
    {
        return $this->hasMany(Answer::class, 'question_id');
    }

    public function options()
    {
        return $this->hasMany(Option::class, 'question_id');
    }

    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}
