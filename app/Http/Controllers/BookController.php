<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Book::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $book = Book::find($id);
        return $book;
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function showOnSale(){
        //dd(Book::getBookOnSale());
        return Book::getBookOnSale()->get();
    }
     

    public function showOnSale1()
    {
        $db = DB::table("books")
        ->innerJoin("discounts", function($join){
            $join->on("books.id", "=", "discounts.book_id");
        })
        ->select("books.id", "books.book_title", "books.book_summary", DB::raw('(books.book_price - discounts.discount_price) as giadagiam'))
        ->whereBetween("current_date", ["discounts.discount_start_date", "discounts.discount_end_date"])
        ->orderBy("giadagiam","desc")
        ->get();
        return response()->json($db);      
    }

    public function getRecommendedBooks()
    {
        return Book::getAverageStar()
            ->getFinalPrice()
            ->getDiscountPrice()
            ->orderByDesc('AR')
            ->orderBy('final_price')
            ->limit(8)
            ->get();
    }

    public function getPopularBooks()
    {
        return Book::sortByPopular()
            ->limit(8)
            ->get();
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
