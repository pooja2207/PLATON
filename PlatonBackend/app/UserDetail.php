<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;

class UserDetail extends Model
{
     protected $fillable = [
        'user_id', 'first_name' ,'last_name','adhar_card_no','profie_pic','skill_set','category'
    ];

  public function user(){
    return $this->belongsTo(User::class);
}
   
}
