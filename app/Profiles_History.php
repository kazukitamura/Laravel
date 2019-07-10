<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Profiles_History extends Model
{
    protected $guarded = array('id');

    public static $rules = array(
        'profiles_id' => 'required',
        'edited_at' => 'required',
    );
}
