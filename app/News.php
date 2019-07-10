<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $guarded = array('id');
    // 以下を追記
    public static $rules = array(
        'title' => 'required',
        'body' => 'required',
    );
    
    // 以下を追記
    // Newsモデルに関連付けを行う
    //newsテーブルに関連づいているhistoriesテーブルを全て取得するというメソッド
    public function histories()
    {
      return $this->hasMany('App\History');

    }
}
