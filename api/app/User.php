<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
	protected $fillable = ['phone','password','gender','nickname','birthday','height','weight','email'];
}
