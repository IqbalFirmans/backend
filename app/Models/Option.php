<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    use HasFactory;
    protected $table = 'options';
    protected $guarded = [];

    protected $hidden = ['created_at', 'updated_at', 'id', 'question_id'];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
