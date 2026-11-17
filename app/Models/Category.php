<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /** Ikon emoji-free (huruf) untuk placeholder visual. */
    public function initials(): string
    {
        return collect(preg_split('/[\s&-]+/', $this->name))
            ->filter()
            ->map(fn ($w) => mb_substr($w, 0, 1))
            ->take(2)
            ->implode('');
    }
}
