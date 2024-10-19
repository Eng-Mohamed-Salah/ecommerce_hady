<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'product_id'];

    public function Products()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    public function Users()
    {
        return $this->belongsTo(User::class);
    }

}
