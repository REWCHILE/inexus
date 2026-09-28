<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'description',
        'margin_percentage',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'margin_percentage' => 'float',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function getIconUrlAttribute(): string
    {
        if ($this->icon && file_exists(public_path($this->icon))) {
            return asset($this->icon);
        }
        return asset('images/categories/1.png');
    }

    public static function boot()
    {
        parent::boot();
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }
}
