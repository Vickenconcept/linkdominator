@extends('layout.auth')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Create New Post</h2>
    <a href="{{ route('content-creator.index') }}" 
       class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
        <i class="fas fa-arrow-left mr-2"></i>Back to Posts
    </a>
</div>

<!-- Error Messages -->
@if ($errors->any())
<div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
    <div class="flex">
        <div class="flex-shrink-0">
            <i class="fas fa-exclamation-triangle text-red-400"></i>
        </div>
        <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                Please fix the following errors:
            </h3>
            <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Success Messages -->
@if (session('success'))
<div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
    <div class="flex">
        <div class="flex-shrink-0">
            <i class="fas fa-check-circle text-green-400"></i>
        </div>
        <div class="ml-3">
            <p class="text-sm text-green-800 dark:text-green-200">
                {{ session('success') }}
            </p>
        </div>
    </div>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Left Sidebar - Templates & AI Tools -->
    <div class="lg:col-span-1">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">AI Assistant</h3>
            
            <!-- AI Generation Form -->
            <form id="aiGenerateForm" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Topic or Idea
                    </label>
                    <textarea id="aiTopic" 
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 dark:bg-gray-700 dark:text-white"
                              rows="3" 
                              placeholder="What do you want to write about?"></textarea>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Style
                        </label>
                        <select id="aiStyle" 
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 dark:bg-gray-700 dark:text-white">
                            <option value="professional">Professional</option>
                            <option value="casual">Casual</option>
                            <option value="motivational">Motivational</option>
                            <option value="educational">Educational</option>
                            <option value="storytelling">Storytelling</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Length
                        </label>
                        <select id="aiLength" 
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 dark:bg-gray-700 dark:text-white">
                            <option value="short">Short</option>
                            <option value="medium" selected>Medium</option>
                            <option value="long">Long</option>
                        </select>
                    </div>
                </div>
                
                <!-- 🔥 NEW: Multiple Drafts Option -->
                <div class="mb-4">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" id="multipleDrafts" 
                               class="w-4 h-4 text-orange-600 bg-gray-100 border-gray-300 rounded focus:ring-orange-500">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                            Generate 3 variations <span class="text-xs text-gray-500">(Taplio-style)</span>
                        </span>
                    </label>
                </div>
                
                <button type="submit" 
                        id="generateBtn"
                        class="w-full bg-orange-600 hover:bg-orange-700 text-white py-2 px-4 rounded-md transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <span id="generateText">
                        <i class="fas fa-magic mr-2"></i>Generate with AI
                    </span>
                    <span id="generateLoading" class="hidden">
                        <i class="fas fa-spinner fa-spin mr-2"></i>Generating...
                    </span>
                </button>
            </form>
            
            <!-- 🔥 NEW: Multiple Drafts Selection -->
            <div id="draftsContainer" class="mt-4 hidden">
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                    <i class="fas fa-copy mr-1 text-orange-500"></i>Choose Your Favorite Draft
                </h4>
                <div id="draftsList" class="space-y-3 max-h-96 overflow-y-auto">
                    <!-- Drafts will be inserted here -->
                </div>
            </div>
        </div>

        <!-- Templates Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Templates</h3>
                <span id="templateCount" class="text-xs text-gray-500 dark:text-gray-400">
                    {{ count($templates) }} templates
                </span>
            </div>
            
            <!-- 🔥 ENHANCED: Search & Filters -->
            <div class="space-y-3 mb-4">
                <!-- Search -->
                <div class="relative">
                    <input type="text" 
                           id="templateSearch" 
                           placeholder="Search templates..." 
                           class="w-full pl-8 pr-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 dark:bg-gray-700 dark:text-white">
                    <i class="fas fa-search absolute left-2.5 top-2.5 text-gray-400 text-xs"></i>
                </div>
                
                <!-- Category Filter -->
                <select id="templateCategory" 
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 dark:bg-gray-700 dark:text-white">
                    <option value="">All Categories</option>
                    @foreach($categories as $key => $name)
                    <option value="{{ $key }}">{{ $name }}</option>
                    @endforeach
                </select>
                
                <!-- Industry Filter -->
                <select id="templateIndustry" 
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 dark:bg-gray-700 dark:text-white">
                    <option value="">All Industries</option>
                    @foreach($industries as $key => $name)
                    <option value="{{ $key }}">{{ $name }}</option>
                    @endforeach
                </select>
                
                <!-- Engagement Score Filter -->
                <select id="templateEngagement" 
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 dark:bg-gray-700 dark:text-white">
                    <option value="">All Engagement</option>
                    <option value="90">🔥 90%+ (Viral)</option>
                    <option value="85">⚡ 85%+ (High)</option>
                    <option value="80">✨ 80%+ (Good)</option>
                </select>
                
                <!-- Clear Filters Button -->
                <button type="button" 
                        id="clearFilters"
                        class="w-full px-3 py-2 text-xs bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-md transition-colors">
                    <i class="fas fa-redo mr-1"></i>Clear Filters
                </button>
            </div>
            
            <!-- Templates List -->
            <div id="templatesList" class="space-y-3 max-h-64 overflow-y-auto">
                @foreach($templates as $template)
                <div class="p-3 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer template-item"
                     data-template-id="{{ $template->id }}"
                     data-category="{{ $template->category }}"
                     data-industry="{{ $template->industry }}"
                     data-engagement="{{ $template->engagement_score }}"
                     data-title="{{ strtolower($template->title) }}"
                     data-description="{{ strtolower($template->description ?? '') }}">
                    <div class="flex items-start justify-between mb-1">
                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $template->title }}
                        </div>
                        <!-- Engagement Badge -->
                        <span class="ml-2 px-2 py-0.5 text-xs font-bold rounded
                            @if($template->engagement_score >= 90) bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200
                            @elseif($template->engagement_score >= 85) bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-200
                            @else bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200 @endif">
                            {{ $template->engagement_score }}%
                        </span>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                        {{ Str::limit($template->description ?? $template->content, 60) }}
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="text-xs px-2 py-0.5 bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-200 rounded">
                                {{ ucfirst($template->category) }}
                            </span>
                            <span class="text-xs px-2 py-0.5 bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300 rounded">
                                {{ ucfirst($template->industry) }}
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <!-- No Results Message -->
            <div id="noTemplatesMessage" class="hidden text-center py-8">
                <i class="fas fa-search text-4xl text-gray-300 dark:text-gray-600 mb-2"></i>
                <p class="text-sm text-gray-500 dark:text-gray-400">No templates found</p>
                <button type="button" onclick="clearAllFilters()" 
                        class="mt-2 text-xs text-orange-600 hover:text-orange-700">
                    Clear filters
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="lg:col-span-2">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-6">
                <form id="postForm" action="{{ route('content-creator.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- Post Type Selection -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                            Post Type
                        </label>
                        <div class="grid grid-cols-4 gap-4 @error('post_type') border border-red-500 rounded-lg p-2 @enderror">
                            <label class="flex items-center p-3 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                                <input type="radio" name="post_type" value="text" 
                                       {{ old('post_type', 'text') == 'text' ? 'checked' : '' }}
                                       class="text-orange-600 focus:ring-orange-500">
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">Text</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Text only</div>
                                </div>
                            </label>
                            
                            <label class="flex items-center p-3 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                                <input type="radio" name="post_type" value="image" 
                                       {{ old('post_type') == 'image' ? 'checked' : '' }}
                                       class="text-orange-600 focus:ring-orange-500">
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">Image</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">1-10 images</div>
                                </div>
                            </label>
                            
                            <label class="flex items-center p-3 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                                <input type="radio" name="post_type" value="carousel" 
                                       {{ old('post_type') == 'carousel' ? 'checked' : '' }}
                                       class="text-orange-600 focus:ring-orange-500">
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">Carousel</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">PDF/PowerPoint</div>
                                </div>
                            </label>
                            
                            <label class="flex items-center p-3 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                                <input type="radio" name="post_type" value="video" 
                                       {{ old('post_type') == 'video' ? 'checked' : '' }}
                                       class="text-orange-600 focus:ring-orange-500">
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">Video</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Video content</div>
                                </div>
                            </label>
                        </div>
                        @error('post_type')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Content Editor -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Post Content
                        </label>
                        <textarea id="postContent" 
                                  name="content" 
                                  rows="8" 
                                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 dark:bg-gray-700 dark:text-white @error('content') border-red-500 @enderror"
                                  placeholder="Write your LinkedIn post here...">{{ old('content') }}</textarea>
                        @error('content')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <div class="mt-2 flex justify-between items-center">
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                <span id="wordCount">0</span> words
                            </div>
                        </div>
                        
                        <!-- 🔥 NEW: Improve Post Action Buttons (Taplio-style) -->
                        <div id="improveActions" class="mt-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hidden">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <i class="fas fa-magic text-orange-500 mr-1"></i>Improve Your Post
                                </span>
                                <button type="button" onclick="toggleImproveActions()" 
                                        class="text-xs text-gray-500 hover:text-gray-700">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" onclick="improvePost('add_hook')" 
                                        class="px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white text-xs rounded-md transition-colors flex items-center">
                                    <i class="fas fa-fish mr-1"></i>Add Hook
                                </button>
                                <button type="button" onclick="improvePost('add_cta')" 
                                        class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs rounded-md transition-colors flex items-center">
                                    <i class="fas fa-bullhorn mr-1"></i>Add CTA
                                </button>
                                <button type="button" onclick="improvePost('expand')" 
                                        class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs rounded-md transition-colors flex items-center">
                                    <i class="fas fa-expand-arrows-alt mr-1"></i>Expand
                                </button>
                                <button type="button" onclick="improvePost('make_viral')" 
                                        class="px-3 py-1.5 bg-orange-600 hover:bg-orange-700 text-white text-xs rounded-md transition-colors flex items-center">
                                    <i class="fas fa-fire mr-1"></i>Make Viral
                                </button>
                                <button type="button" onclick="improvePost('add_data')" 
                                        class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs rounded-md transition-colors flex items-center">
                                    <i class="fas fa-chart-line mr-1"></i>Add Data
                                </button>
                                <button type="button" onclick="improvePost('bullet_points')" 
                                        class="px-3 py-1.5 bg-pink-600 hover:bg-pink-700 text-white text-xs rounded-md transition-colors flex items-center">
                                    <i class="fas fa-list-ul mr-1"></i>Bullets
                                </button>
                                <button type="button" onclick="improvePost('add_story')" 
                                        class="px-3 py-1.5 bg-yellow-600 hover:bg-yellow-700 text-white text-xs rounded-md transition-colors flex items-center">
                                    <i class="fas fa-book-open mr-1"></i>Add Story
                                </button>
                                <button type="button" onclick="improvePost('controversial')" 
                                        class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs rounded-md transition-colors flex items-center">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>Controversial
                                </button>
                                <button type="button" onclick="improvePost('add_emoji')" 
                                        class="px-3 py-1.5 bg-teal-600 hover:bg-teal-700 text-white text-xs rounded-md transition-colors flex items-center">
                                    <i class="fas fa-smile mr-1"></i>Add Emoji
                                </button>
                                <button type="button" onclick="improvePost('make_concise')" 
                                        class="px-3 py-1.5 bg-gray-600 hover:bg-gray-700 text-white text-xs rounded-md transition-colors flex items-center">
                                    <i class="fas fa-compress mr-1"></i>Make Concise
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                💡 Click any action to enhance your content with AI
                            </p>
                        </div>
                        
                        <!-- Show improve actions button -->
                        <button type="button" id="showImproveBtn" onclick="toggleImproveActions()" 
                                class="mt-2 px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white text-sm rounded-md transition-all flex items-center">
                            <i class="fas fa-magic mr-2"></i>Improve This Post
                        </button>
                    </div>

                    <!-- Image Upload (for image posts - supports 1-10 images) -->
                    <div id="imageUploadSection" class="mb-6 hidden">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <i class="fas fa-images mr-1 text-orange-500"></i>Upload Images (1-10 images)
                        </label>
                        <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center @error('images.*') border-red-500 @enderror">
                            <input type="file" id="imageUpload" name="images[]" multiple accept="image/jpeg,image/png,image/jpg,image/gif,image/svg+xml" class="hidden">
                            <button type="button" onclick="document.getElementById('imageUpload').click()" 
                                    class="text-orange-600 hover:text-orange-700">
                                <i class="fas fa-images text-3xl mb-2"></i>
                                <div class="text-sm font-medium">Click to upload image(s)</div>
                                <div class="text-xs text-gray-500 mt-1">Select 1 or more images (max 10 images, 10MB each)</div>
                            </button>
                        </div>
                        @error('images.*')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <div id="imagePreview" class="mt-4 hidden">
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3" id="imagePreviewGrid">
                                <!-- Images will be displayed here -->
                            </div>
                        </div>
                    </div>

                    <!-- Carousel Document Upload (for carousel posts - PDF/PowerPoint ONLY) -->
                    <div id="carouselUploadSection" class="mb-6 hidden">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <i class="fas fa-file-pdf mr-1 text-orange-500"></i>Upload Carousel Document
                        </label>
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3 mb-3">
                            <p class="text-xs text-blue-800 dark:text-blue-200">
                                <i class="fas fa-info-circle mr-1"></i>
                                <strong>LinkedIn carousels require PDF or PowerPoint files.</strong><br>
                                Each page/slide will become a swipeable carousel slide on LinkedIn.
                            </p>
                        </div>
                        <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center @error('carousel_file') border-red-500 @enderror">
                            <input type="file" id="carouselUpload" name="carousel_file" accept=".pdf,.ppt,.pptx,application/pdf,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation" class="hidden">
                            <button type="button" onclick="document.getElementById('carouselUpload').click()" 
                                    class="text-orange-600 hover:text-orange-700">
                                <i class="fas fa-file-pdf text-3xl mb-2"></i>
                                <div class="text-sm font-medium">Click to upload PDF or PowerPoint</div>
                                <div class="text-xs text-gray-500 mt-1">Accepted: .pdf, .ppt, .pptx (max 50MB)</div>
                            </button>
                        </div>
                        @error('carousel_file')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <div id="carouselPreview" class="mt-4 hidden">
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <i class="fas fa-file-pdf text-2xl text-red-500 mr-3"></i>
                                        <div>
                                            <div id="carouselFileName" class="text-sm font-medium text-gray-900 dark:text-white"></div>
                                            <div id="carouselFileSize" class="text-xs text-gray-500 dark:text-gray-400"></div>
                                        </div>
                                    </div>
                                    <button type="button" onclick="clearCarouselFile()" class="text-red-600 hover:text-red-700">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Video Upload (for video posts) -->
                    <div id="videoUploadSection" class="mb-6 hidden">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Upload Video
                        </label>
                        <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center @error('video') border-red-500 @enderror">
                            <input type="file" id="videoUpload" name="video" accept="video/*" class="hidden">
                            <button type="button" onclick="document.getElementById('videoUpload').click()" 
                                    class="text-orange-600 hover:text-orange-700">
                                <i class="fas fa-video text-3xl mb-2"></i>
                                <div class="text-sm">Click to upload video</div>
                            </button>
                        </div>
                        @error('video')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <div id="videoPreview" class="mt-4 hidden">
                            <video id="previewVideo" controls class="max-w-full h-48 rounded-lg">
                                <source id="videoSource" src="" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        </div>
                    </div>

                    <!-- Hashtags -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Hashtags
                        </label>
                        <input type="text" 
                               name="hashtags" 
                               id="hashtags"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 dark:bg-gray-700 dark:text-white @error('hashtags') border-red-500 @enderror"
                               placeholder="#marketing #business #growth"
                               value="{{ old('hashtags') }}">
                        @error('hashtags')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Separate hashtags with spaces. Use 3-5 hashtags for best results.
                        </div>
                    </div>

                    <!-- Scheduling -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Publishing Options
                        </label>
                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input type="radio" name="publish_option" value="draft" checked 
                                       class="text-orange-600 focus:ring-orange-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Save as Draft</span>
                            </label>
                            
                            <label class="flex items-center">
                                <input type="radio" name="publish_option" value="now" 
                                       class="text-orange-600 focus:ring-orange-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Publish Now</span>
                            </label>
                            
                            <label class="flex items-center">
                                <input type="radio" name="publish_option" value="schedule" 
                                       class="text-orange-600 focus:ring-orange-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Schedule for Later</span>
                            </label>
                        </div>
                        
                        <div id="scheduleSection" class="mt-4 hidden">
                            <input type="datetime-local" 
                                   name="scheduled_at" 
                                   id="scheduledAt"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 dark:bg-gray-700 dark:text-white @error('scheduled_at') border-red-500 @enderror"
                                   min="{{ now()->format('Y-m-d\TH:i') }}"
                                   value="{{ old('scheduled_at') }}">
                            @error('scheduled_at')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-4">
                        <a href="{{ route('content-creator.index') }}" 
                           class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Cancel
                        </a>
                        <button type="submit" 
                                id="savePostBtn"
                                class="px-6 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <span id="savePostText">
                                <i class="fas fa-save mr-2"></i>Save Post
                            </span>
                            <span id="savePostLoading" class="hidden">
                                <i class="fas fa-spinner fa-spin mr-2"></i>Saving...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-600 mx-auto mb-4"></div>
            <p class="text-gray-900 dark:text-white">Generating content...</p>
        </div>
    </div>
</div>

<script>
// CSRF token from Blade to avoid relying on a meta tag
const csrfToken = '{{ csrf_token() }}';

// 🔥 NEW: Load inspiration content from sessionStorage
window.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const fromInspiration = urlParams.get('from') === 'inspiration';
    
    if (fromInspiration) {
        const inspirationContent = sessionStorage.getItem('inspiration_content');
        const inspirationAuthor = sessionStorage.getItem('inspiration_author');
        const wasRemixed = sessionStorage.getItem('inspiration_remixed');
        
        if (inspirationContent) {
            // Load content into editor
            document.getElementById('postContent').value = inspirationContent;
            document.getElementById('wordCount').textContent = str_word_count(inspirationContent);
            
            // Extract and load hashtags
            const hashtags = extractHashtags(inspirationContent);
            document.getElementById('hashtags').value = hashtags;
            
            // Show improve actions automatically
            document.getElementById('improveActions').classList.remove('hidden');
            document.getElementById('showImproveBtn').classList.add('hidden');
            
            // Show notification
            if (wasRemixed) {
                showNotification('✨ AI-remixed content loaded! Ready to customize and post.', 'success');
            } else {
                showNotification(`💡 Inspiration from ${inspirationAuthor || 'viral post'} loaded! Customize it to make it yours.`, 'success');
            }
            
            // Clear sessionStorage
            sessionStorage.removeItem('inspiration_content');
            sessionStorage.removeItem('inspiration_author');
            sessionStorage.removeItem('inspiration_engagement');
            sessionStorage.removeItem('inspiration_remixed');
            
            // Scroll to content
            setTimeout(() => {
                document.getElementById('postContent').scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 500);
        }
    }
});

// Word count update
document.getElementById('postContent').addEventListener('input', function() {
    const wordCount = this.value.trim().split(/\s+/).filter(word => word.length > 0).length;
    document.getElementById('wordCount').textContent = wordCount;
});

// Post type change handler
document.querySelectorAll('input[name="post_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const imageSection = document.getElementById('imageUploadSection');
        const carouselSection = document.getElementById('carouselUploadSection');
        const videoSection = document.getElementById('videoUploadSection');
        const imageInput = document.getElementById('imageUpload');
        const carouselInput = document.getElementById('carouselUpload');
        const videoInput = document.getElementById('videoUpload');
        const imagePreview = document.getElementById('imagePreview');
        const carouselPreview = document.getElementById('carouselPreview');
        const videoPreview = document.getElementById('videoPreview');
        
        if (this.value === 'image') {
            imageSection.classList.remove('hidden');
            carouselSection.classList.add('hidden');
            videoSection.classList.add('hidden');
            
            // Clear other inputs and previews
            carouselInput.value = '';
            videoInput.value = '';
            carouselPreview.classList.add('hidden');
            videoPreview.classList.add('hidden');
        } else if (this.value === 'carousel') {
            carouselSection.classList.remove('hidden');
            imageSection.classList.add('hidden');
            videoSection.classList.add('hidden');
            
            // Clear other inputs and previews
            imageInput.value = '';
            videoInput.value = '';
            imagePreview.classList.add('hidden');
            videoPreview.classList.add('hidden');
        } else if (this.value === 'video') {
            videoSection.classList.remove('hidden');
            imageSection.classList.add('hidden');
            carouselSection.classList.add('hidden');
            
            // Clear other inputs and previews
            imageInput.value = '';
            carouselInput.value = '';
            imagePreview.classList.add('hidden');
            carouselPreview.classList.add('hidden');
        } else {
            imageSection.classList.add('hidden');
            carouselSection.classList.add('hidden');
            videoSection.classList.add('hidden');
            
            // Clear all inputs and previews
            imageInput.value = '';
            carouselInput.value = '';
            videoInput.value = '';
            imagePreview.classList.add('hidden');
            carouselPreview.classList.add('hidden');
            videoPreview.classList.add('hidden');
        }
    });
});

// Publish option change handler
document.querySelectorAll('input[name="publish_option"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const scheduleSection = document.getElementById('scheduleSection');
        if (this.value === 'schedule') {
            scheduleSection.classList.remove('hidden');
        } else {
            scheduleSection.classList.add('hidden');
        }
    });
});

// 🔥 NEW: Toggle Improve Actions Panel
function toggleImproveActions() {
    const improveActions = document.getElementById('improveActions');
    const showBtn = document.getElementById('showImproveBtn');
    
    if (improveActions.classList.contains('hidden')) {
        improveActions.classList.remove('hidden');
        showBtn.classList.add('hidden');
    } else {
        improveActions.classList.add('hidden');
        showBtn.classList.remove('hidden');
    }
}

// 🔥 NEW: Improve Post Function
function improvePost(action) {
    const content = document.getElementById('postContent').value.trim();
    
    if (!content) {
        alert('Please enter some content first.');
        return;
    }
    
    showLoading();
    
    // Disable all improve buttons
    const improveButtons = document.querySelectorAll('#improveActions button[onclick^="improvePost"]');
    improveButtons.forEach(btn => btn.disabled = true);
    
    fetch('/content-creator/improve', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            content: content,
            action: action
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        improveButtons.forEach(btn => btn.disabled = false);
        
        if (data.success) {
            document.getElementById('postContent').value = data.content;
            document.getElementById('wordCount').textContent = data.word_count;
            
            // Show success notification
            showNotification('✨ Content improved successfully!', 'success');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        hideLoading();
        improveButtons.forEach(btn => btn.disabled = false);
        console.error('Error:', error);
        alert('An error occurred while improving content.');
    });
}

// 🔥 NEW: Show Notification Function
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg text-white ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    } animate-slide-in`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// 🔥 UPDATED: AI Generation with Multiple Drafts Support
document.getElementById('aiGenerateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const topic = document.getElementById('aiTopic').value.trim();
    if (!topic) {
        alert('Please enter a topic or idea.');
        return;
    }
    
    const multipleDrafts = document.getElementById('multipleDrafts').checked;
    
    showLoading();
    
    fetch('/content-creator/generate', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            topic: topic,
            style: document.getElementById('aiStyle').value,
            length: document.getElementById('aiLength').value,
            multiple_drafts: multipleDrafts
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            if (data.drafts && data.drafts.length > 0) {
                // Show multiple drafts
                displayMultipleDrafts(data.drafts);
            } else {
                // Single draft (backward compatibility)
                document.getElementById('postContent').value = data.content;
                document.getElementById('hashtags').value = data.hashtags;
                document.getElementById('wordCount').textContent = data.word_count;
                
                // Show improve actions automatically
                document.getElementById('improveActions').classList.remove('hidden');
                document.getElementById('showImproveBtn').classList.add('hidden');
            }
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        alert('An error occurred while generating content.');
    });
});

// 🔥 NEW: Display Multiple Drafts
function displayMultipleDrafts(drafts) {
    const draftsList = document.getElementById('draftsList');
    const draftsContainer = document.getElementById('draftsContainer');
    
    // Clear previous drafts
    draftsList.innerHTML = '';
    
    // Create draft cards
    drafts.forEach((draft, index) => {
        const draftCard = document.createElement('div');
        draftCard.className = 'p-4 border-2 border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:border-orange-500 hover:bg-orange-50 dark:hover:bg-gray-700 transition-all';
        draftCard.onclick = () => selectDraft(draft);
        
        draftCard.innerHTML = `
            <div class="flex items-start justify-between mb-2">
                <div class="flex items-center">
                    <span class="inline-flex items-center justify-center w-6 h-6 bg-orange-100 dark:bg-orange-900 text-orange-600 dark:text-orange-400 rounded-full text-xs font-bold mr-2">
                        ${index + 1}
                    </span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                        Draft ${index + 1}
                    </span>
                </div>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    ${draft.word_count} words
                </span>
            </div>
            <p class="text-sm text-gray-700 dark:text-gray-300 line-clamp-3 mb-2">
                ${draft.content.substring(0, 150)}${draft.content.length > 150 ? '...' : ''}
            </p>
            <div class="flex items-center justify-between">
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    ${draft.hashtags || 'No hashtags'}
                </div>
                <button class="text-xs text-orange-600 hover:text-orange-700 font-medium">
                    Use this draft →
                </button>
            </div>
        `;
        
        draftsList.appendChild(draftCard);
    });
    
    // Show drafts container
    draftsContainer.classList.remove('hidden');
    
    // Scroll to drafts
    draftsContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

// 🔥 NEW: Select Draft
function selectDraft(draft) {
    document.getElementById('postContent').value = draft.content;
    document.getElementById('hashtags').value = draft.hashtags || '';
    document.getElementById('wordCount').textContent = draft.word_count;
    
    // Hide drafts container
    document.getElementById('draftsContainer').classList.add('hidden');
    
    // Show improve actions automatically
    document.getElementById('improveActions').classList.remove('hidden');
    document.getElementById('showImproveBtn').classList.add('hidden');
    
    // Scroll to content editor
    document.getElementById('postContent').scrollIntoView({ behavior: 'smooth', block: 'center' });
    
    // Show success notification
    showNotification('✅ Draft selected! Now improve it with AI actions below.', 'success');
}

// 🔥 FIXED: Template selection - now works instantly
function attachTemplateClickHandlers() {
    document.querySelectorAll('.template-item').forEach(item => {
        // Remove old listener if any
        const newItem = item.cloneNode(true);
        item.parentNode.replaceChild(newItem, item);
        
        newItem.addEventListener('click', function() {
            const templateId = this.dataset.templateId;
            
            console.log('🎯 Template clicked:', templateId);
            
            // Show loading
            showLoading();
            
            // Fetch template content
            fetch(`/content-creator/templates?template_id=${templateId}`)
                .then(response => {
                    console.log('📡 Response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('📦 Template data received:', data);
                    hideLoading();
                    
                    if (data.success !== false && data.templates && data.templates.length > 0) {
                        const template = data.templates[0];
                        
                        console.log('✅ Loading template:', template.title);
                        
                        // Load template content
                        document.getElementById('postContent').value = template.content;
                        document.getElementById('hashtags').value = extractHashtags(template.content);
                        document.getElementById('wordCount').textContent = str_word_count(template.content);
                        
                        // Auto-show improve actions
                        document.getElementById('improveActions').classList.remove('hidden');
                        document.getElementById('showImproveBtn').classList.add('hidden');
                        
                        // Scroll to content
                        document.getElementById('postContent').scrollIntoView({ behavior: 'smooth', block: 'center' });
                        
                        // Show success notification
                        showNotification('✅ Template loaded! Now customize it with variables or improve actions.', 'success');
                        
                        console.log('🎉 Template loaded successfully');
                    } else {
                        throw new Error(data.message || 'Template not found in response');
                    }
                })
                .catch(error => {
                    hideLoading();
                    console.error('❌ Template loading error:', error);
                    alert('Error loading template: ' + error.message + '\n\nPlease check the console for details.');
                });
        });
    });
}

// Initialize template handlers on page load
attachTemplateClickHandlers();

// 🔥 ENHANCED: Template Filtering System
function filterTemplates() {
    const searchTerm = document.getElementById('templateSearch').value.toLowerCase();
    const category = document.getElementById('templateCategory').value;
    const industry = document.getElementById('templateIndustry').value;
    const engagement = document.getElementById('templateEngagement').value;
    
    let visibleCount = 0;
    
    document.querySelectorAll('.template-item').forEach(item => {
        const matchesSearch = !searchTerm || 
            item.dataset.title.includes(searchTerm) || 
            item.dataset.description.includes(searchTerm);
        
        const matchesCategory = !category || item.dataset.category === category;
        const matchesIndustry = !industry || item.dataset.industry === industry;
        const matchesEngagement = !engagement || parseInt(item.dataset.engagement) >= parseInt(engagement);
        
        if (matchesSearch && matchesCategory && matchesIndustry && matchesEngagement) {
            item.style.display = 'block';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });
    
    // Update count
    document.getElementById('templateCount').textContent = `${visibleCount} template${visibleCount !== 1 ? 's' : ''}`;
    
    // Show/hide no results message
    const noResultsMsg = document.getElementById('noTemplatesMessage');
    const templatesList = document.getElementById('templatesList');
    if (visibleCount === 0) {
        noResultsMsg.classList.remove('hidden');
        templatesList.classList.add('hidden');
    } else {
        noResultsMsg.classList.add('hidden');
        templatesList.classList.remove('hidden');
    }
    
    // 🔥 FIX: Reattach click handlers after filtering
    attachTemplateClickHandlers();
}

// Clear all filters
function clearAllFilters() {
    document.getElementById('templateSearch').value = '';
    document.getElementById('templateCategory').value = '';
    document.getElementById('templateIndustry').value = '';
    document.getElementById('templateEngagement').value = '';
    filterTemplates();
}

// Attach filter event listeners
document.getElementById('templateSearch').addEventListener('input', filterTemplates);
document.getElementById('templateCategory').addEventListener('change', filterTemplates);
document.getElementById('templateIndustry').addEventListener('change', filterTemplates);
document.getElementById('templateEngagement').addEventListener('change', filterTemplates);
document.getElementById('clearFilters').addEventListener('click', clearAllFilters);

// Rewrite and Shorten are now part of improve actions (add_hook, make_concise, etc.)

// Utility functions
function showLoading() {
    document.getElementById('loadingOverlay').classList.remove('hidden');
}

function hideLoading() {
    document.getElementById('loadingOverlay').classList.add('hidden');
}

function extractHashtags(text) {
    const hashtags = text.match(/#\w+/g);
    return hashtags ? hashtags.join(' ') : '';
}

function str_word_count(str) {
    return str.trim().split(/\s+/).filter(word => word.length > 0).length;
}

// Image upload preview
// Image upload preview (supports multiple images)
document.getElementById('imageUpload').addEventListener('change', function(e) {
    const files = Array.from(e.target.files);
    if (files.length > 0) {
        // Validate number of images (1-10)
        if (files.length > 10) {
            alert('Please select maximum 10 images.');
            this.value = '';
            return;
        }
        
        // Clear other inputs and previews when images are selected
        const carouselInput = document.getElementById('carouselUpload');
        const videoInput = document.getElementById('videoUpload');
        const carouselPreview = document.getElementById('carouselPreview');
        const videoPreview = document.getElementById('videoPreview');
        carouselInput.value = '';
        videoInput.value = '';
        carouselPreview.classList.add('hidden');
        videoPreview.classList.add('hidden');
        
        // Clear and show preview grid
        const previewGrid = document.getElementById('imagePreviewGrid');
        previewGrid.innerHTML = '';
        
        // Display each image
        files.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const imgContainer = document.createElement('div');
                imgContainer.className = 'relative group';
                imgContainer.innerHTML = `
                    <img src="${e.target.result}" alt="Image ${index + 1}" 
                         class="w-full h-32 object-cover rounded-lg border-2 border-gray-200 dark:border-gray-600">
                    <div class="absolute top-1 right-1 bg-orange-500 text-white text-xs px-2 py-1 rounded-full">
                        ${index + 1}
                    </div>
                `;
                previewGrid.appendChild(imgContainer);
            };
            reader.readAsDataURL(file);
        });
        
        document.getElementById('imagePreview').classList.remove('hidden');
    }
});

// Carousel document upload preview (PDF/PowerPoint)
document.getElementById('carouselUpload').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Validate file type
        const validTypes = ['application/pdf', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'];
        const validExtensions = ['.pdf', '.ppt', '.pptx'];
        const fileName = file.name.toLowerCase();
        const isValidType = validTypes.includes(file.type) || validExtensions.some(ext => fileName.endsWith(ext));
        
        if (!isValidType) {
            alert('Please select a PDF or PowerPoint file (.pdf, .ppt, .pptx)');
            this.value = '';
            return;
        }
        
        // Validate file size (50MB max)
        if (file.size > 52428800) { // 50MB in bytes
            alert('File size must be less than 50MB.');
            this.value = '';
            return;
        }
        
        // Clear other inputs and previews when carousel file is selected
        const imageInput = document.getElementById('imageUpload');
        const videoInput = document.getElementById('videoUpload');
        const imagePreview = document.getElementById('imagePreview');
        const videoPreview = document.getElementById('videoPreview');
        imageInput.value = '';
        videoInput.value = '';
        imagePreview.classList.add('hidden');
        videoPreview.classList.add('hidden');
        
        // Show file info
        const fileSize = (file.size / 1024 / 1024).toFixed(2) + ' MB';
        
        document.getElementById('carouselFileName').textContent = file.name;
        document.getElementById('carouselFileSize').textContent = fileSize;
        document.getElementById('carouselPreview').classList.remove('hidden');
    }
});

// Clear carousel file function
function clearCarouselFile() {
    document.getElementById('carouselUpload').value = '';
    document.getElementById('carouselPreview').classList.add('hidden');
}

// Video upload preview
document.getElementById('videoUpload').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Clear other inputs and previews when video is selected
        const imageInput = document.getElementById('imageUpload');
        const carouselInput = document.getElementById('carouselUpload');
        const imagePreview = document.getElementById('imagePreview');
        const carouselPreview = document.getElementById('carouselPreview');
        imageInput.value = '';
        carouselInput.value = '';
        imagePreview.classList.add('hidden');
        carouselPreview.classList.add('hidden');
        
        const url = URL.createObjectURL(file);
        document.getElementById('videoSource').src = url;
        document.getElementById('previewVideo').load();
        document.getElementById('videoPreview').classList.remove('hidden');
    }
});

// AI Generate form submission handler with loading state
document.getElementById('aiGenerateForm').addEventListener('submit', function(e) {
    const generateBtn = document.getElementById('generateBtn');
    const generateText = document.getElementById('generateText');
    const generateLoading = document.getElementById('generateLoading');
    
    // Show loading state
    generateBtn.disabled = true;
    generateText.classList.add('hidden');
    generateLoading.classList.remove('hidden');
    
    // Reset loading state after 30 seconds if something goes wrong
    setTimeout(() => {
        if (generateBtn.disabled) {
            generateBtn.disabled = false;
            generateText.classList.remove('hidden');
            generateLoading.classList.add('hidden');
        }
    }, 30000);
});

// Main form submission handler with loading state
document.getElementById('postForm').addEventListener('submit', function(e) {
    const saveBtn = document.getElementById('savePostBtn');
    const saveText = document.getElementById('savePostText');
    const saveLoading = document.getElementById('savePostLoading');
    
    // Show loading state
    saveBtn.disabled = true;
    saveText.classList.add('hidden');
    saveLoading.classList.remove('hidden');
    
    // Reset loading state after 30 seconds if something goes wrong
    setTimeout(() => {
        if (saveBtn.disabled) {
            saveBtn.disabled = false;
            saveText.classList.remove('hidden');
            saveLoading.classList.add('hidden');
        }
    }, 30000);
});
</script>
@endsection
