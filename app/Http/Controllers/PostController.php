<?php

namespace App\Http\Controllers;

use App\Jobs\SendNewPostEmail;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function search($term){
        $posts = Post::search($term)->get();
        $posts->load('user:id,username,avatar');
        return $posts;
    }

    public function showEditForm(Post $post){
        return view('edit-post', ['post'=>$post]);
    }

    public function actuallyUpdated(Post $post, Request $request){
        $incomingFields = $request->validate([
           'title' => 'required',
           'body' => 'required'
        ]);

        $incomingFields['title']=strip_tags($incomingFields['title']);
        $incomingFields['body']=strip_tags(strval($incomingFields['body']));

        $post->update($incomingFields);

        return back()->with('success', 'Post successfully updated.');
    }

    public function showCreateForm(){
        return view('create-post');
    }

    public function storeNewPost(Request $request){
        $incomingFields = $request->validate([
            'title'=>'required',
            'body'=>'required'
        ]);

        $incomingFields['title']=strip_tags($incomingFields['title']);
        $incomingFields['body']=strip_tags(strval($incomingFields['body']));
        $incomingFields['user_id']=auth()->id();

        $newPost = Post::create($incomingFields);

        dispatch(new SendNewPostEmail(['sendTo' => auth()->user()->email, 'name'=>auth()->user()->username,'title'=>$newPost->title]));


        return redirect("/post/{$newPost->id}")->with('success','New Post Created');
    }

    public function viewSinglePost(Post $post){
        $post['body'] = strip_tags(Str::markdown($post->body), '<p><ul><ol><li><strong><em><br>');;
        return view('single-post', ['post' => $post]);
    }

    public function delete(Post $post){
//        if(auth()->user()->cannot('delete',$post)){
//            return 'You cannot do that';
//        }
        $post->delete();

        return redirect('/profile/'.auth()->user()->username)->with('success', 'Successfully Deleted');
    }
}
