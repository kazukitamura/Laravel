<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
// 以下を追記することでProfiles Modelが扱えるようになる
use App\Profiles;

use App\Profiles_History;

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
      $this->validate($request, Profiles::$rules);

      $profiles = new Profiles;
      $form = $request->all();
      


      // フォームから送信されてきた_tokenを削除する
      unset($form['_token']);
      // フォームから送信されてきたimageを削除する
      unset($form['image']);

      // データベースに保存する
      $profiles->fill($form);
      $profiles->save();
      
      return redirect('admin/profile/create');

  }
  
  // 以下を追記
  public function index(Request $request)
  {
      $cond_title = $request->cond_title;
      if ($cond_title != '') {
          // もしcond_titleが空欄でない場合は、検索された結果を取得する
          //$cond_titleはブラウザからの情報で、name="cond_title"にてinput要素の名前を指定する。
          //index.blade.phpのinputタグ、name属性にて使用。
          //whereはデータベース検索の条件。MySQLとPosgreとかの差をなくすため（データベース間の文法差異）に抽象化している。
          //->get()でデータの中身を取得。
          $posts = Profiles::where('title', $cond_title)->get();
      } else {
          // それ以外はすべてのニュースを取得する
          //条件を一切つけずに全てのデータを取得するには、all()メソッドを使います。
          $posts = Profiles::all();
      }
      //viewヘルパに渡している最初の引数は、resources/viewsディレクトリー中の
      //ビューファイル名(index.blade.php)に対応しています。
      //２つ目の引数は、ビューで使用するデータの配列です。下記の例では、
      //ビューにposts,cond_title変数を渡し、それはBlade記法を使用しているビューの中に表示されます。
      return view('admin.profile.index', ['posts' => $posts, 'cond_title' => $cond_title]);
  }

  public function edit(Request $request)
  {
      // Profiles Modelからデータを取得する
      $profiles = new Profiles;
      //if (empty($profiles)) {
        //abort(404);    
      //}
      return view('admin.profile.edit', ['profiles_form' => $profiles]);
      
  }

  public function update(Request $request)
  {
      // Validationをかける
      $this->validate($request, Profiles::$rules);
      // profiles Modelからデータを取得する
      $profiles = Profiles::find($request->id);
      // 送信されてきたフォームデータを格納する
      $profiles_form = $request->all();
      if (isset($profiles_form['image'])) {
        $path = $request->file('image')->store('public/image');
        $profiles->image_path = basename($path);
        unset($profiles_form['image']);
      } elseif (isset($request->remove)) {
        $profiles->image_path = null;
        unset($profiles_form['remove']);
      }
      unset($profiles_form['_token']);
      // 該当するデータを上書きして保存する
      $profiles->fill($profiles_form)->save();
      
        // 以下を追記
        $history = new Profiles_History;
        $history->profiles_id = $profiles->id;
        $history->edited_at = Carbon::now();
        $history->save();
      
      return redirect('admin/profile/edit');
  }
  
    public function delete(Request $request)
  {
      // 該当するNews Modelを取得
      $profiles = Profiles::find($request->id);
      // 削除する
      $profiles->delete();
      return redirect('admin/profile/');
  }
  
  
}
