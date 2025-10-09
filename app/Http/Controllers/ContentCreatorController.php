<?php

namespace App\Http\Controllers;

use App\Models\LinkedInPost;
use App\Models\PostTemplate;
use App\Services\ChatGPT;
use App\Services\LinkedInContentService;
use App\Helpers\CampaignHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ContentCreatorController extends Controller
{
    use CampaignHelper;

    /**
     * Display the content creator dashboard
     */
    public function index(Request $request)
    {
        $userId = auth()->user()->id;
        $status = $request->query('status', 'all');
        
        $query = LinkedInPost::where('user_id', $userId);
        
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        
        $posts = $query->orderBy('created_at', 'desc')->paginate(12);
        
        // Get statistics
        $stats = [
            'total_posts' => LinkedInPost::where('user_id', $userId)->count(),
            'draft_posts' => LinkedInPost::where('user_id', $userId)->where('status', 'draft')->count(),
            'scheduled_posts' => LinkedInPost::where('user_id', $userId)->where('status', 'scheduled')->count(),
            'published_posts' => LinkedInPost::where('user_id', $userId)->where('status', 'published')->count(),
        ];
        
        return view('content-creator.index', compact('posts', 'stats', 'status'));
    }

    /**
     * Show the form for creating a new post
     */
    public function create()
    {
        $templates = PostTemplate::active()
            ->orderBy('engagement_score', 'desc')
            ->limit(20)
            ->get();
            
        $categories = PostTemplate::getCategories();
        $industries = PostTemplate::getIndustries();
        
        return view('content-creator.create', compact('templates', 'categories', 'industries'));
    }

    /**
     * Store a newly created post
     */
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:3000',
            'post_type' => 'required|in:text,image,carousel,video',
            'scheduled_at' => 'nullable|date|after:now',
            'hashtags' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10240', // 10MB max
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10240', // For carousel
            'video' => 'nullable|mimes:mp4,avi,mov,wmv|max:102400' // 100MB max for video
        ]);

        // Validate that only one media type is selected based on post_type
        if ($request->post_type === 'image' && $request->hasFile('video')) {
            return back()->withErrors(['video' => 'Cannot upload video for image post type. Please select image instead.']);
        }
        
        if ($request->post_type === 'video' && ($request->hasFile('image') || $request->hasFile('images'))) {
            return back()->withErrors(['image' => 'Cannot upload images for video post type. Please select video instead.']);
        }

        $imageUrl = null;
        $videoUrl = null;
        $carouselImages = null;

        // Initialize Cloudinary service
        $cloudinaryService = new LinkedInContentService();

        // Handle single image upload (for image post type)
        if ($request->post_type === 'image' && $request->hasFile('image')) {
            $imageUrl = $cloudinaryService->uploadImage($request->file('image'));
        }

        // Handle multiple images upload (for carousel post type)
        if ($request->post_type === 'carousel' && $request->hasFile('images')) {
            $carouselImages = $cloudinaryService->uploadCarouselImages($request->file('images'));
        }

        // Handle video upload (only for video post type)
        if ($request->post_type === 'video' && $request->hasFile('video')) {
            $videoUrl = $cloudinaryService->uploadVideo($request->file('video'));
        }

        // Determine status based on publish option
        $status = 'draft';
        $scheduledAt = null;

        if ($request->publish_option === 'now') {
            $status = 'ready_to_publish';
            $scheduledAt = now();
        } elseif ($request->publish_option === 'schedule' && $request->scheduled_at) {
            $status = 'scheduled';
            $scheduledAt = Carbon::parse($request->scheduled_at);
        }

        $post = LinkedInPost::create([
            'user_id' => auth()->id(),
            'content' => $request->content,
            'image_url' => $imageUrl,
            'video_url' => $videoUrl,
            'carousel_images' => $carouselImages,
            'post_type' => $request->post_type,
            'status' => $status,
            'scheduled_at' => $scheduledAt,
            'hashtags' => $request->hashtags,
            'word_count' => str_word_count($request->content)
        ]);

        if ($status === 'scheduled') {
            // Dispatch job for scheduling
            \App\Jobs\PublishLinkedInPost::dispatch($post)->delay($scheduledAt);
        } elseif ($status === 'ready_to_publish') {
            // Dispatch job immediately for "Publish Now"
            \App\Jobs\PublishLinkedInPost::dispatch($post);
        }

        notify()->success('Post saved successfully!');
        return redirect()->route('content-creator.index');
    }

    /**
     * Generate content using AI (now returns multiple drafts)
     */
    public function generate(Request $request)
    {
        $request->validate([
            'topic' => 'required|string|max:500',
            'style' => 'required|in:professional,casual,motivational,educational,storytelling',
            'length' => 'required|in:short,medium,long',
            'template_id' => 'nullable|exists:post_templates,id',
            'multiple_drafts' => 'nullable|boolean'
        ]);

        try {
            $data = [
                'topic' => $request->topic,
                'style' => $request->style,
                'length' => $request->length,
                'template_id' => $request->template_id
            ];

            $chatGPT = new ChatGPT($data);
            
            // Check if multiple drafts are requested
            if ($request->multiple_drafts) {
                $drafts = $chatGPT->generateMultipleDrafts();
                
                return response()->json([
                    'success' => true,
                    'drafts' => $drafts
                ]);
            } else {
                // Single draft (backward compatibility)
                $result = $chatGPT->generateLinkedInPost();

                return response()->json([
                    'success' => true,
                    'content' => $result['content'],
                    'hashtags' => $result['hashtags'] ?? '',
                    'word_count' => $result['word_count'] ?? 0
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Improve existing post content with specific action
     */
    public function improvePost(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
            'action' => 'required|in:add_hook,add_cta,expand,make_viral,add_data,bullet_points,add_story,controversial,add_emoji,make_concise'
        ]);

        try {
            $chatGPT = new ChatGPT();
            $result = $chatGPT->improvePost($request->action, $request->content);

            return response()->json([
                'success' => true,
                'content' => $result['content'],
                'word_count' => $result['word_count']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Rewrite existing content
     */
    public function rewrite(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
            'tone' => 'nullable|in:professional,casual,motivational,educational,storytelling',
            'mode' => 'nullable|in:shorten,expand'
        ]);

        try {
            $data = [
                'content' => $request->content,
                'tone' => $request->tone ?? 'professional',
                'mode' => $request->mode
            ];

            $chatGPT = new ChatGPT($data);
            $result = $chatGPT->rewritePost();

            return response()->json([
                'success' => true,
                'content' => $result['content'],
                'word_count' => $result['word_count'] ?? 0
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get templates by category, industry, or specific template by ID
     */
    public function getTemplates(Request $request)
    {
        // 🔥 FIX: Handle template_id parameter for single template fetch
        $templateId = $request->query('template_id');
        
        if ($templateId) {
            $template = PostTemplate::active()->find($templateId);
            
            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template not found'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'templates' => [$template]
            ]);
        }
        
        // Handle category and industry filters
        $category = $request->query('category');
        $industry = $request->query('industry');
        
        $query = PostTemplate::active();
        
        if ($category) {
            $query->where('category', $category);
        }
        
        if ($industry) {
            $query->where('industry', $industry);
        }
        
        $templates = $query->orderBy('engagement_score', 'desc')->get();
        
        return response()->json(['templates' => $templates]);
    }

    /**
     * Schedule a post
     */
    public function schedule(Request $request, $id)
    {
        $post = LinkedInPost::where('user_id', auth()->id())->findOrFail($id);
        
        $request->validate([
            'scheduled_at' => 'required|date|after:now'
        ]);

        $post->update([
            'status' => 'scheduled',
            'scheduled_at' => Carbon::parse($request->scheduled_at)
        ]);

        // Dispatch job for scheduling
        \App\Jobs\PublishLinkedInPost::dispatch($post)->delay($post->scheduled_at);

        return response()->json([
            'success' => true,
            'message' => 'Post scheduled successfully!'
        ]);
    }

    /**
     * Publish a post immediately
     */
    public function publish($id)
    {
        $post = LinkedInPost::where('user_id', auth()->id())->findOrFail($id);
        
        if ($post->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Only draft posts can be published immediately!'
            ], 400);
        }

        $post->update([
            'status' => 'ready_to_publish',
            'scheduled_at' => now()
        ]);

        // Dispatch job immediately
        \App\Jobs\PublishLinkedInPost::dispatch($post);

        // Log that a post was published immediately for extension to pick up
        \Log::info('🚀 Post published immediately', [
            'post_id' => $post->id,
            'user_id' => auth()->id(),
            'linkedin_id' => auth()->user()->linkedin_id,
            'content_preview' => substr($post->content, 0, 100) . '...'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Post is being published!'
        ]);
    }

    /**
     * Delete a post
     */
    public function destroy($id)
    {
        $post = LinkedInPost::where('user_id', auth()->id())->findOrFail($id);
        
        if ($post->status === 'published') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete published posts!'
            ], 400);
        }

        $post->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully!'
        ]);
    }

    /**
     * Get analytics for a post
     */
    public function analytics($id)
    {
        $post = LinkedInPost::where('user_id', auth()->id())->findOrFail($id);
        
        return response()->json([
            'post' => $post,
            'engagement' => $post->engagement,
            'engagement_rate' => $post->engagement_rate
        ]);
    }

    /**
     * API endpoint for extension to get scheduled posts
     */
    public function getScheduledPosts(Request $request)
    {
        try {
            $this->checkAuthorization($request);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
                "status" => 400
            ], 400);
        }

        $lkId = $request->header('lk-id');
        $user = \App\Models\User::where('linkedin_id', $lkId)->first();

        if (!$user) {
            return response()->json([
                "message" => "User not found",
                "status" => 404
            ], 404);
        }

        $posts = LinkedInPost::where('user_id', $user->id)
            ->where(function($query) {
                $query->where('status', 'scheduled')
                      ->where('scheduled_at', '<=', now());
            })
            ->orWhere('status', 'ready_to_publish')
            ->orderBy('scheduled_at', 'asc')
            ->get();

        \Log::info('🔍 Extension requested scheduled posts', [
            'user_id' => $user->id,
            'linkedin_id' => $lkId,
            'posts_found' => $posts->count(),
            'posts' => $posts->pluck('id', 'content')
        ]);

        return response()->json([
            'data' => $posts,
            'status' => 200
        ]);
    }

    /**
     * API endpoint for extension to update post status
     */
    public function updatePostStatus(Request $request, $id)
    {
        try {
            $this->checkAuthorization($request);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
                "status" => 400
            ], 400);
        }

        $lkId = $request->header('lk-id');
        $user = \App\Models\User::where('linkedin_id', $lkId)->first();

        if (!$user) {
            return response()->json([
                "message" => "User not found",
                "status" => 404
            ], 404);
        }

        $post = LinkedInPost::where('user_id', $user->id)->findOrFail($id);

        $request->validate([
            'status' => 'required|in:published,failed',
            'linkedin_post_id' => 'nullable|string',
            'analytics' => 'nullable|array'
        ]);

        if ($request->status === 'published') {
            $post->markAsPublished($request->linkedin_post_id);
        } else {
            $post->markAsFailed();
        }

        if ($request->analytics) {
            $post->updateAnalytics($request->analytics);
        }

        return response()->json([
            'message' => 'Post status updated successfully',
            'status' => 200
        ]);
    }
}
