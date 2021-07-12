<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Review;
use App\Models\Discount;
use App\Models\Category;
use App\Models\Author;
use Illuminate\Support\Facades\DB;

class Book extends Model
{
    use HasFactory;

    protected $hidden = ['id', 'category_id', 'author_id'];
    public $timestamps = false;
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function discount()
    {
        return $this->hasOne(Discount::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function scopeGetSubPrice($query)
    {
        return $query->addSelect([
            'sub_price' => Discount::select(DB::raw('books.book_price - discounts.discount_price'))
                ->whereColumn('book_id', 'books.id')
        ]);
    }

    public function scopeGetDiscountPrice($query)
    {
        return $query->addSelect([
            'discount_price' => Discount::select('discount_price')
                ->whereColumn('book_id', 'books.id')
                ->where(function ($query) {
                    $query->where('discount_start_date', '<=', now())
                        ->where(function ($query) {
                            $query->whereDate('discount_end_date', '>=', now())
                                ->orWhereNull('discount_end_date');
                        });
                })
        ]);
    }


    public function scopeGetBookOnSale($query)
    {
        //dd(now());
        return $query->getDiscountPrice()
                ->getSubPrice()
            ->orderByRaw('sub_price DESC NULLS LAST');
        
    }


    public function scopeGetFinalPrice($query)
    {
        return $query->addSelect([
            'final_price' => Discount::select(DB::raw('coalesce(max(discounts.discount_price), books.book_price)'))
                ->whereColumn('book_id', 'books.id')
                ->whereBetween(now(), ['discount_start_date', 'discount_end_date'])
                ->orWhere(function ($query) {
                    $query->whereDate('discount_end_date', '>=', now())
                        ->WhereNull('discount_end_date');
                })
        ]);
    }

    public function scopeGetBookByPopular($query){
        return $query->withCount('reviews')
        ->getFinalPrice()
        ->getDiscountPrice()
        ->orderByDesc('reviews_count')
        ->orderBy('final_price');
    }
}
