<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormDomain extends Model
{
    use HasFactory;
    protected $table = 'form_domains';
    protected $guarded = [];


    public function form()
    {
        return $this->belongsTo(Form::class);
    }

}
