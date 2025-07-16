<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\AiContent;
use App\Models\Integration;
use App\Services\ChatGPT;
use App\Services\LinkedInService;
use App\Helpers\FileHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use Log;

class SchedulePostController extends Controller
{
    const POSTTYPES = ['text only','article only','image only','text with image','text with article'];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->query('post');
        $post = new Post;

        if(isset($post)){
            $posts = $post->where('content','like','%'.$search.'%')
                ->where('user_id', auth()->user()->id)
                ->latest()
                ->paginate(20);
        }else {
            $posts = $post->where('user_id', auth()->user()->id)
                ->latest()
                ->paginate(20);
        }

        return view('schedule-post.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $aicontents = AiContent::where('user_id', auth()->user()->id)->get();

        return view('schedule-post.create', compact('aicontents'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $scheduled_time = $request->schedule_time;

        if ($request->save_mode == 'instant'){
            $publish_status = 'pending';
            $scheduled_time = Carbon::now()->toDateTimeString();
        }elseif ($request->save_mode == 'schedule'){
            $publish_status = 'scheduled';
        }else{
            $publish_status = 'draft';
            $scheduled_time = Carbon::now()->toDateTimeString();
        }

        if($request->content && $request->file('image'))
            $post_type = self::POSTTYPES[3];
        else if($request->content && $request->article)
            $post_type = self::POSTTYPES[4];
        else if($request->content && !$request->file('image') && !$request->article)
            $post_type = self::POSTTYPES[0];
        else if($request->file('image') && !$request->article && !$request->content)
            $post_type = self::POSTTYPES[2];
        else
            $post_type = self::POSTTYPES[1];

        // save image to path and save path to db
        $file = null;
        if($request->file('image')){
            try {
                $fileHandler = new FileHandler();
                $file = $fileHandler->handle($request->file('image'), 'linkedin', 'save')['relativePath'];

            } catch (\Throwable $th) {
                Log::info($th);

                notify()->error($th->getMessage());
                return redirect()->route('post.create');
            }
        }

        $post = Post::create([
            'content' => $request->content,
            'word_counts' => $request->words,
            'image' => $file,
            'article' => $request->article,
            'save_mode' => $request->save_mode,
            'schedule_time' => $scheduled_time,
            'publish_status' => $publish_status,
            'post_type' => $post_type,
            'user_id' => auth()->user()->id
        ]);

        // check for publish_status 
        if ($request->save_mode == 'instant'){
            try {
                $this->publishPost($post, 'create');
            } catch (\Throwable $th) {
                notify()->success($th->getMessage());
                return redirect()->route('post.create');
            }
        }

        notify()->success('Post submitted successfully.');
        return redirect()->route('post.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $post = Post::findOrFail($id);
        $aicontents = AiContent::where('user_id', auth()->user()->id)->get();

        return view('schedule-post.edit', compact('post','aicontents'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $post = Post::findOrFail($id);

        $scheduled_time = $request->schedule_time;

        if ($request->save_mode == 'instant'){
            $publish_status = 'pending';
            $scheduled_time = Carbon::now()->toDateTimeString();
        }elseif ($request->save_mode == 'schedule'){
            $publish_status = 'scheduled';
        }else{
            $publish_status = 'draft';
            $scheduled_time = Carbon::now()->toDateTimeString();
        }

        if($request->content && $request->file('image'))
            $post_type = self::POSTTYPES[3];
        else if($request->content && $request->article)
            $post_type = self::POSTTYPES[4];
        else if($request->content && !$request->file('image') && !$request->article)
            $post_type = self::POSTTYPES[0];
        else if($request->file('image') && !$request->article && !$request->content)
            $post_type = self::POSTTYPES[2];
        else
            $post_type = self::POSTTYPES[1];

        // save image to path and save path to db
        $file = $post->image;

        if($request->file('image')){
            try {
                $fileHandler = new FileHandler();

                if($post->image)
                    $fileHandler->handle($post->image, null, 'delete');

                $file = $fileHandler->handle($request->file('image'), 'linkedin', 'save')['relativePath'];

            } catch (\Throwable $th) {
                Log::info($th);

                notify()->error($th->getMessage());
                return redirect()->route('post.edit', ['id' => $id]);
            }
        }

        $post = $post->update([
            'content' => $request->content,
            'word_counts' => $request->words,
            'image' => $file,
            'article' => $request->article,
            'save_mode' => $request->save_mode,
            'schedule_time' => $scheduled_time,
            'publish_status' => $publish_status,
            'post_type' => $post_type,
        ]);

        // check for publish_status 
        if ($request->save_mode == 'instant'){
            try {
                $this->publishPost($post, 'update');
            } catch (\Throwable $th) {
                notify()->success($th->getMessage());
                return redirect()->route('post.create');
            }
        }

        notify()->success('Post submitted successfully.');
        return redirect()->route('post.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $post = Post::findOrFail($id);

        if($post->image){
            $fileHandler = new FileHandler;
            $fileHandler->handle($post->image, null, 'delete');
        }

        $post->delete();

        notify()->success('Post deleted successfully');
        return redirect()->route('post.index');
    }

    public function generateAiContent(Request $request)
    {
        $data = [
            'idea' => $request->topic,
            'language' => 'english',
            'aitype' => 'linkedin_post'
        ];

        try {
            $gpt = new ChatGPT($data);

            return response()->json([
                'data' => $gpt->generate(),
                'status' => 200
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage()
            ],422);
        }
    }

    public function publishPost($data, $referrer)
    {
        $oauth = Integration::where('user_id', auth()->user()->id)
            ->orderBy('id', 'desc')
            ->first();

        if($oauth){
            $data['oauth_uid'] = $oauth->oauth_uid;

            try {
                $linkedin = new LinkedInService;
                $linkedin->publishPost($data, $oauth->access_token);
            } catch (\Throwable $th) {
                Log::info($th);

                Post::where('id', $data['id'])->update([
                    'publish_status' => 'failed',
                    'comment' => $th->getMessage(),
                ]);

                notify()->error($th->getMessage());

                return redirect()->route('post.edit', ['id' => $data['id']]);
            }
        }else{
            throw new Exception("LinkedIn account not found. Kindly authenticate with LinkedIn to post", 1);
        }
    }   
}
