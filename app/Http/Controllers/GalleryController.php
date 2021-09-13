<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function getJsonFile($language_) {
        return json_decode(file_get_contents(public_path('assets/uploads/gallery/' . $language_ . '.json')));
    }

    public function index($language_) {
        header('Content-Type: application/json; charset=UTF-8');
        return response()->json([
            'status' => 'success',
            'result' => $this->getJsonFile($language_)
        ], 200);
    }
}
