<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'supplier_id', 'name', 'wholesale_rate', 'retail_rate', 'stock', 'images', 'status',
    ];

    protected $casts = [
        'images' => 'array',
    ];

    public function supplier()
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }
}