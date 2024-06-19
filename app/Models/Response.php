<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Response extends Model
{
    use HasFactory;
    protected $table = 'responses';
    protected $guarded = [];

    protected $hidden = ['created_at', 'updated_at'];

    public function answers() {
        return $this->hasMany(Answer::class, 'response_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function form() {
        return $this->belongsTo(Form::class);
    }
}
