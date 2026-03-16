<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CustombookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $books = [
        [
            'author' => 'Jane Doe',
            'title' => 'My first book',
            'genre' => 'Fantasy'
        ],
        [
            'author' => 'Jane Doe',
            'title' => 'My second book',
            'genre' => 'Fantasy'
        ]
    ];
 
    return view('customBook/home', ['customBooks' => $books]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
