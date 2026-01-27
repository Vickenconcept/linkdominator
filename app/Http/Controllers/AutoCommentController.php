<?php

namespace App\Http\Controllers;

use App\Models\AutoCommentPreference;
use App\Models\AutoCommentPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AutoCommentController extends Controller
{
    /**
     * Display the preferences page
     */
    public function index()
    {
        $user = Auth::user();
        $preference = AutoCommentPreference::where('user_id', $user->id)->first();
        $posts = AutoCommentPost::where('user_id', $user->id)
            ->latest()
            ->paginate(20);
        
        return view('auto-comment.index', compact('preference', 'posts'));
    }

    /**
     * Show the form for creating/editing preferences
     */
    public function preferences()
    {
        $user = Auth::user();
        $preference = AutoCommentPreference::where('user_id', $user->id)->first();
        
        return view('auto-comment.preferences', compact('preference'));
    }

    /**
     * Store or update preferences
     */
    public function storePreferences(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'is_active' => 'boolean',
            'keywords' => 'nullable|string',
            'followed_accounts' => 'nullable|string',
            'industries' => 'nullable|string',
            'min_engagement' => 'integer|min:0',
            'comment_style' => 'string',
            'comment_tone' => 'string',
            'comment_instructions' => 'nullable|string',
            'avoid_topics' => 'nullable|string',
            'posting_times' => 'nullable|string',
            'timezone' => 'nullable|string',
            'max_comments_per_day' => 'integer|min:1|max:100',
            'min_time_between_comments' => 'integer|min:0',
            'skip_already_commented' => 'boolean',
            'skip_posts_older_than_days' => 'integer|min:0',
            'only_fresh_posts' => 'boolean',
        ]);

        // Convert comma-separated strings to arrays
        if (!empty($validated['keywords'])) {
            $validated['keywords'] = array_map('trim', explode(',', $validated['keywords']));
        } else {
            $validated['keywords'] = [];
        }
        
        if (!empty($validated['followed_accounts'])) {
            $validated['followed_accounts'] = array_map('trim', explode("\n", $validated['followed_accounts']));
        } else {
            $validated['followed_accounts'] = [];
        }
        
        if (!empty($validated['industries'])) {
            $validated['industries'] = array_map('trim', explode(',', $validated['industries']));
        } else {
            $validated['industries'] = [];
        }
        
        if (!empty($validated['posting_times'])) {
            $validated['posting_times'] = array_map('intval', explode(',', $validated['posting_times']));
        } else {
            $validated['posting_times'] = [9, 14, 18]; // Default posting times
        }

        $validated['user_id'] = $user->id;

        $preference = AutoCommentPreference::updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        notify()->success('Preferences saved successfully!');

        return redirect()->route('auto-comment.index');
    }

    /**
     * Get posts for display
     */
    public function getPosts()
    {
        $user = Auth::user();
        $posts = AutoCommentPost::where('user_id', $user->id)
            ->latest()
            ->paginate(20);
        
        return response()->json($posts);
    }

    /**
     * Delete a post record
     */
    public function deletePost($id)
    {
        $user = Auth::user();
        $post = AutoCommentPost::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();
        
        $post->delete();
        
        notify()->success('Post deleted successfully');
        return redirect()->route('auto-comment.index');
    }
}
