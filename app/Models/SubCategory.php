<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    use HasFactory;
    protected $fillable = ['category_id','title', 'image'];

    // Create Relations With Category
    public function Category()
    {
        return $this->belongsTo(Category::class);
    }
}
