<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostTemplate extends Model
{
    protected $fillable = [
        'title',
        'content',
        'category',
        'industry',
        'engagement_score',
        'variables',
        'description',
        'is_active'
    ];

    protected $casts = [
        'variables' => 'array'
    ];

    /**
     * Scope for active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope by industry
     */
    public function scopeByIndustry($query, $industry)
    {
        return $query->where('industry', $industry);
    }

    /**
     * Scope for high performing templates
     */
    public function scopeHighPerforming($query)
    {
        return $query->where('engagement_score', '>', 50)
                    ->orderBy('engagement_score', 'desc');
    }

    /**
     * Get available variables in the template
     */
    public function getAvailableVariables()
    {
        return $this->variables ?? [];
    }

    /**
     * Replace variables in template content
     */
    public function replaceVariables(array $data)
    {
        $content = $this->content;
        
        foreach ($data as $key => $value) {
            $content = str_replace('{' . $key . '}', $value, $content);
        }
        
        return $content;
    }

    /**
     * Get template categories
     */
    public static function getCategories()
    {
        return [
            'story' => 'Story Post',
            'listicle' => 'List Post',
            'value_drop' => 'Value Drop',
            'question' => 'Question Post',
            'tip' => 'Tip Post',
            'behind_scenes' => 'Behind the Scenes',
            'achievement' => 'Achievement Post',
            'controversial' => 'Controversial Post'
        ];
    }

    /**
     * Get industries
     */
    public static function getIndustries()
    {
        return [
            'tech' => 'Technology',
            'marketing' => 'Marketing',
            'finance' => 'Finance',
            'healthcare' => 'Healthcare',
            'education' => 'Education',
            'sales' => 'Sales',
            'entrepreneurship' => 'Entrepreneurship',
            'general' => 'General'
        ];
    }
}
