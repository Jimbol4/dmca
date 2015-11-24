<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
      'name',
      'copyright_email'
    ];
}
