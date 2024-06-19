<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    use HasFactory;
    protected $table = 'forms';
    protected $guarded = [];

    protected $hidden = ['created_at', 'updated_at'];

    public function form_domain()
    {
        return $this->hasMany(FormDomain::class, 'form_id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'form_id');
    }

    public function respondens()
    {
        return $this->hasMany(Response::class, 'form_id');
    }
}

