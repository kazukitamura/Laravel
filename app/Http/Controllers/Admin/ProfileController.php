<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
// 以下を追記することでProfiles Modelが扱えるようになる
use App\Profile;

use App\Profilehistory;

use Carbon\Carbon;

class ProfileController extends Controller
{
    //
    

  public function add()
  {
      return view('admin.profile.create');
  }

  public function create(Request $request)
  {
      // Varidationを行う
      $this->validate($request, Profile::$rules);

      $profiles = new Profile;
      $form = $request->all();
      


      // フォームから送信されてきた_tokenを削除する
      unset($form['_token']);
      // フォームから送信されてきたimageを削除する
      unset($form['image']);

      // データベースに保存する
      $profile->fill($form);
      $profile->save();
      
      return redirect('admin/profile/create');

  }
  
  // 以下を追記
  public function index(Request $request)
  {
      $cond_name = $request->cond_name;
      if ($cond_name != '') {
          // もしcond_titleが空欄でない場合は、検索された結果を取得する
          //$cond_titleはブラウザからの情報で、name="cond_title"にてinput要素の名前を指定する。
          //index.blade.phpのinputタグ、name属性にて使用。
          //whereはデータベース検索の条件。MySQLとPosgreとかの差をなくすため（データベース間の文法差異）に抽象化している。
          //第１引数はカラム名、第2引数は検索する値
          //->get()でデータの中身を取得。
          $posts = Profile::where('name', $cond_name)->get();
      } else {
          // それ以外はすべてのニュースを取得する
          //条件を一切つけずに全てのデータを取得するには、all()メソッドを使います。
          $posts = Profile::all();
      }
      //viewヘルパに渡している最初の引数は、resources/viewsディレクトリー中の
      //ビューファイル名(index.blade.php)に対応しています。
      //２つ目の引数は、ビューで使用するデータの配列です。下記の例では、
      //ビューにposts,cond_title変数を渡し、それはBlade記法を使用しているビューの中に表示されます。
      return view('admin.profile.index', ['posts' => $posts, 'cond_name' => $cond_name]);
  }

  public function edit(Request $request)
  {
      // Profiles Modelからデータを取得する
      $profile = Profile::find($request->id); 
      if (empty($profile)) {
         abort(404);    
      }
      return view('admin.profile.edit', ['profile_form' => $profile]);
      
  }

  public function update(Request $request)
  {
      // Validationをかける
      $this->validate($request, Profile::$rules);
      // profiles Modelからデータを取得する
      $profile = Profile::find($request->id);
      // 送信されてきたフォームデータを格納する
      $profile_form = $request->all();

      unset($profile_form['_token']);
      // 該当するデータを上書きして保存する
      $profile->fill($profile_form)->save();
      
        // 以下を追記
        $profilehistory = new Profilehistory;
        $profilehistory->profile_id = $profile->id;
        $profilehistory->edited_at = Carbon::now();
        $profilehistory->save();
      
      return redirect('admin/profile');
  }
  
    public function delete(Request $request)
  {
      // 該当するNews Modelを取得
      $profile = Profile::find($request->id);
      // 削除する
      $profile->delete();
      return redirect('admin/profile');
  }
  
  
}
