<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'image'];

      // Create Relations With SubCategory (one to many)
      public function SubCategory()
      {
          return $this->hasMany(SubCategory::class);
      }
}
