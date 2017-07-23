<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App;
use Log;
use Illuminate\Support\Facades\DB;
class ArticleController extends Controller
{
    //获取文章列表
    public function index()
    {
        //注意主页需按created_at排序
        $articles = App\Article::select('id','title','updated_at')->latest('updated_at')->get();
        foreach($articles as &$article){
          $article['type'] = $article->categories()->first()['type'] ? 'code' : 'essay';
          $article['tag'] = $article->categories()->select('id','name','type')->get();
        }
        return response()->json($articles);
    }

    //新建
    public function store(Request $request)
    {
        $this->validate($request,[
            'title'=>'required|filled|max:50',
            'coding_tags'=>'filled|array',
            'essay_tags'=>'filled|array',
            'outline'=>'required|filled|max:100',
            'body'=>'required|filled'
        ]);
        DB::transaction(function () use ($request){
          $article = new App\Article;
          $article->title = $request->input('title');
          $article->outline = $request->input('outline');
          $article->body = $request->input('body');
          $article->save();
          if($request->input('coding_tags')){
            $tags = $request->input('coding_tags');
          }else{
            $tags = $request->input('essay_tags');
          }
          foreach($tags as $tag_id){
            $article->categories()->attach($tag_id);
          }
        });
        return response()->json('');
    }

    //详情
    public function show(App\Article $article)
    {
      unset($article['created_at']);
      $article['type'] = $article->categories()->first()['type'] ? 'coding' : 'essay';
      $article['tag'] = $article->categories()->select('id','name')->get();
      return response()->json($article);
    }

    //编辑
    public function edit(App\Article $article)
    {
      unset($article['created_at']);
      unset($article['updated_at']);
      $article['type'] = $article->categories()->first()['type'] ? 'coding' : 'essay';
      $article['tagIds'] = $article->categories()->pluck('id');
      return response()->json($article);
    }

    //更新
    public function update(Request $request, App\Article $article)
    {
      $this->validate($request,[
          'title'=>'required|filled|max:50',
          'coding_tags'=>'filled|array',
          'essay_tags'=>'filled|array',
          'outline'=>'required|filled|max:100',
          'body'=>'required|filled'
      ]);
      DB::transaction(function () use ($request,$article){
        $article->title = $request->input('title');
        $article->outline = $request->input('outline');
        $article->body = $request->input('body');
        $article->save();
        if($request->input('coding_tags')){
          $tags = $request->input('coding_tags');
        }else{
          $tags = $request->input('essay_tags');
        }
        $article->categories()->detach();
        foreach($tags as $tag_id){
          $article->categories()->attach($tag_id);
        }
      });
      return response()->json($article);
    }

    //删除
    public function destroy(App\Article $article)
    {
        $article->delete();
        $article->categories()->detach();
        return response()->json('');
    }

    //上传文章图片
    public function uploadFile(Request $request){
      $path = $request->file('image')->store('uploads/images','public');
      return response()->json(['errno'=>0,'data'=>['/storage/'.$path]]);
    }
}
