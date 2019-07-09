<?php

//namespaceは名前空間とも呼ばれ、項目をカプセル化するときに使用します。
//名前空間とは、例えば通常同じファイルに同じクラスや関数名、
//定数名が存在することはできませんが、名前空間を使用することにより、
//関連するクラスや、インターフェイス、関数、定数などをグループ化することが可能です。
//そのため、名前空間を指定しておけば自分が作ったクラスが、
//サードパーティのクラスや関数などと名前が衝突することを防ぐことができます。
namespace App\Http\Controllers\Admin;

//vender > laravel > framework > src > Illuminateに位置している。
//Illuminate\HttpにあるRequestクラスを使いますよという宣言。
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

// 以下を追記することでNews Modelが扱えるようになる
use App\News;

// 以下を追記
use App\History;
use Carbon\Carbon;

class NewsController extends Controller
{
  public function add()
  {
      return view('admin.news.create');
  }
//hoge(Request $request)はLaravelに既存で搭載されているサービスプロバイダという
//機能を使って自動でインスタンス化を行う。Requestはブラウザを通して送られてくる
//ユーザー情報をすべて含んでいる。
  public function create(Request $request)
  {     
   

      // 以下を追記
      // Varidationを行う
      $this->validate($request, News::$rules);

      $news = new News;
      $form = $request->all();

      // フォームから画像が送信されてきたら、保存して、$news->image_path に画像のパスを保存する
      if (isset($form['image'])) {
        $path = $request->file('image')->store('public/image');
        $news->image_path = basename($path);
      } else {
          $news->image_path = null;
      }

      // フォームから送信されてきた_tokenを削除する
      unset($form['_token']);
      // フォームから送信されてきたimageを削除する
      unset($form['image']);

      // データベースに保存する
      $news->fill($form);
      $news->save();

      return redirect('admin/news/create');
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
          $posts = News::where('title', $cond_title)->get();
      } else {
          // それ以外はすべてのニュースを取得する
          //条件を一切つけずに全てのデータを取得するには、all()メソッドを使います。
          $posts = News::all();
      }
      //viewヘルパに渡している最初の引数は、resources/viewsディレクトリー中の
      //ビューファイル名(index.blade.php)に対応しています。
      //２つ目の引数は、ビューで使用するデータの配列です。下記の例では、
      //ビューにposts,cond_title変数を渡し、それはBlade記法を使用しているビューの中に表示されます。
      return view('admin.news.index', ['posts' => $posts, 'cond_title' => $cond_title]);
  }

 // 以下を追記

  public function edit(Request $request)
  {
      // News Modelからデータを取得する
      $news = News::find($request->id); //admin/news/edit?id=1が入ってくる。
      if (empty($news)) {
        abort(404);    
      }
      return view('admin.news.edit', ['news_form' => $news]);
  }

  public function update(Request $request)
  {
      // Validationをかける
      $this->validate($request, News::$rules);
      // News Modelからデータを取得する
      $news = News::find($request->id);
      // 送信されてきたフォームデータを格納する
      $news_form = $request->all();
      if (isset($news_form['image'])) {
        $path = $request->file('image')->store('public/image');
        $news->image_path = basename($path);
        unset($news_form['image']);
      } elseif (isset($request->remove)) {
        $news->image_path = null;
        unset($news_form['remove']);
      }
      unset($news_form['_token']);
      // 該当するデータを上書きして保存する
      $news->fill($news_form)->save();
      
        // 以下を追記
        //News Modelを保存するタイミングで、同時に History Modelにも編集履歴を追加するよう実装
        $history = new History;
        $history->news_id = $news->id;
        $history->edited_at = Carbon::now();
        $history->save();

      return redirect('admin/news');
  }

  public function delete(Request $request)
  {
      // 該当するNews Modelを取得
      $news = News::find($request->id);
      // 削除する
      $news->delete();
      return redirect('admin/news/');
  }  

}


