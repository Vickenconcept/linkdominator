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
        </div>

        <!-- Templates Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Templates</h3>
            
            <!-- Template Categories -->
            <div class="mb-4">
                <select id="templateCategory" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 dark:bg-gray-700 dark:text-white">
                    <option value="">All Categories</option>
                    @foreach($categories as $key => $name)
                    <option value="{{ $key }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            
            <!-- Templates List -->
            <div id="templatesList" class="space-y-3 max-h-64 overflow-y-auto">
                @foreach($templates as $template)
                <div class="p-3 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer template-item"
                     data-template-id="{{ $template->id }}"
                     data-category="{{ $template->category }}">
                    <div class="text-sm font-medium text-gray-900 dark:text-white mb-1">
                        {{ $template->title }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                        {{ Str::limit($template->content, 80) }}
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300 rounded">
                            {{ ucfirst($template->category) }}
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $template->engagement_score }}% engagement
                        </span>
                    </div>
                </div>
                @endforeach
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
                                    <div class="text-xs text-gray-500 dark:text-gray-400">With image</div>
                                </div>
                            </label>
                            
                            <label class="flex items-center p-3 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                                <input type="radio" name="post_type" value="carousel" 
                                       {{ old('post_type') == 'carousel' ? 'checked' : '' }}
                                       class="text-orange-600 focus:ring-orange-500">
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">Carousel</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Multiple images</div>
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
                            <div class="flex space-x-2">
                                <button type="button" id="rewriteBtn" 
                                        class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-md transition-colors">
                                    <i class="fas fa-edit mr-1"></i>Rewrite
                                </button>
                                <button type="button" id="shortenBtn" 
                                        class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-sm rounded-md transition-colors">
                                    <i class="fas fa-compress mr-1"></i>Shorten
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Image Upload (for image posts) -->
                    <div id="imageUploadSection" class="mb-6 hidden">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Upload Image
                        </label>
                        <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center @error('image') border-red-500 @enderror">
                            <input type="file" id="imageUpload" name="image" accept="image/*" class="hidden">
                            <button type="button" onclick="document.getElementById('imageUpload').click()" 
                                    class="text-orange-600 hover:text-orange-700">
                                <i class="fas fa-cloud-upload-alt text-3xl mb-2"></i>
                                <div class="text-sm">Click to upload image</div>
                            </button>
                        </div>
                        @error('image')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <div id="imagePreview" class="mt-4 hidden">
                            <img id="previewImg" src="" alt="Preview" class="max-w-full h-48 object-cover rounded-lg">
                        </div>
                    </div>

                    <!-- Carousel Images Upload (for carousel posts) -->
                    <div id="carouselUploadSection" class="mb-6 hidden">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Upload Images for Carousel (2-10 images)
                        </label>
                        <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center @error('images.*') border-red-500 @enderror">
                            <input type="file" id="carouselUpload" name="images[]" accept="image/*" multiple class="hidden">
                            <button type="button" onclick="document.getElementById('carouselUpload').click()" 
                                    class="text-orange-600 hover:text-orange-700">
                                <i class="fas fa-images text-3xl mb-2"></i>
                                <div class="text-sm">Click to upload multiple images</div>
                                <div class="text-xs text-gray-500 mt-1">Select 2-10 images for your carousel</div>
                            </button>
                        </div>
                        @error('images.*')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <div id="carouselPreview" class="mt-4 hidden">
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4" id="carouselImagesGrid">
                                <!-- Carousel images will be displayed here -->
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

// AI Generation
document.getElementById('aiGenerateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const topic = document.getElementById('aiTopic').value.trim();
    if (!topic) {
        alert('Please enter a topic or idea.');
        return;
    }
    
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
            length: document.getElementById('aiLength').value
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            document.getElementById('postContent').value = data.content;
            document.getElementById('hashtags').value = data.hashtags;
            document.getElementById('wordCount').textContent = data.word_count;
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

// Template selection
document.querySelectorAll('.template-item').forEach(item => {
    item.addEventListener('click', function() {
        const templateId = this.dataset.templateId;
        showLoading();
        
        fetch(`/content-creator/templates?template_id=${templateId}`)
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.templates && data.templates.length > 0) {
                const template = data.templates[0];
                document.getElementById('postContent').value = template.content;
                document.getElementById('hashtags').value = extractHashtags(template.content);
                document.getElementById('wordCount').textContent = str_word_count(template.content);
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            alert('An error occurred while loading the template.');
        });
    });
});

// Template filtering
document.getElementById('templateCategory').addEventListener('change', function() {
    const category = this.value;
    document.querySelectorAll('.template-item').forEach(item => {
        if (!category || item.dataset.category === category) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});

// Rewrite functionality
document.getElementById('rewriteBtn').addEventListener('click', function() {
    const content = document.getElementById('postContent').value.trim();
    if (!content) {
        alert('Please enter some content to rewrite.');
        return;
    }
    
    showLoading();
    
    fetch('/content-creator/rewrite', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            content: content,
            tone: 'professional',
            mode: null
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            document.getElementById('postContent').value = data.content;
            document.getElementById('wordCount').textContent = data.word_count;
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        alert('An error occurred while rewriting content.');
    });
});

// Shorten functionality
document.getElementById('shortenBtn').addEventListener('click', function() {
    const content = document.getElementById('postContent').value.trim();
    if (!content) {
        alert('Please enter some content to shorten.');
        return;
    }

    showLoading();

    fetch('/content-creator/rewrite', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            content: content,
            tone: 'professional',
            mode: 'shorten'
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            document.getElementById('postContent').value = data.content;
            document.getElementById('wordCount').textContent = data.word_count;
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        alert('An error occurred while shortening content.');
    });
});

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
document.getElementById('imageUpload').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Clear other inputs and previews when image is selected
        const carouselInput = document.getElementById('carouselUpload');
        const videoInput = document.getElementById('videoUpload');
        const carouselPreview = document.getElementById('carouselPreview');
        const videoPreview = document.getElementById('videoPreview');
        carouselInput.value = '';
        videoInput.value = '';
        carouselPreview.classList.add('hidden');
        videoPreview.classList.add('hidden');
        
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('imagePreview').classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }
});

// Carousel images upload preview
document.getElementById('carouselUpload').addEventListener('change', function(e) {
    const files = Array.from(e.target.files);
    if (files.length > 0) {
        // Validate number of images (2-10)
        if (files.length < 2) {
            alert('Please select at least 2 images for carousel.');
            this.value = '';
            return;
        }
        if (files.length > 10) {
            alert('Please select maximum 10 images for carousel.');
            this.value = '';
            return;
        }
        
        // Clear other inputs and previews when carousel images are selected
        const imageInput = document.getElementById('imageUpload');
        const videoInput = document.getElementById('videoUpload');
        const imagePreview = document.getElementById('imagePreview');
        const videoPreview = document.getElementById('videoPreview');
        imageInput.value = '';
        videoInput.value = '';
        imagePreview.classList.add('hidden');
        videoPreview.classList.add('hidden');
        
        // Clear previous carousel preview
        const carouselGrid = document.getElementById('carouselImagesGrid');
        carouselGrid.innerHTML = '';
        
        // Display each image
        files.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const imgContainer = document.createElement('div');
                imgContainer.className = 'relative group';
                imgContainer.innerHTML = `
                    <img src="${e.target.result}" alt="Carousel Image ${index + 1}" 
                         class="w-full h-32 object-cover rounded-lg border-2 border-gray-200">
                    <div class="absolute top-1 right-1 bg-orange-500 text-white text-xs px-2 py-1 rounded-full">
                        ${index + 1}
                    </div>
                `;
                carouselGrid.appendChild(imgContainer);
            };
            reader.readAsDataURL(file);
        });
        
        document.getElementById('carouselPreview').classList.remove('hidden');
    }
});

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
