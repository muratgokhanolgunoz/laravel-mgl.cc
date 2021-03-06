<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function getJsonFile() {
        return json_decode(file_get_contents(public_path('assets/mglUploads/gallery/gallery.json')));
    }

    public function index() {
        header('Content-Type: application/json; charset=UTF-8');
        return response()->json([
            'result' => $this->getJsonFile()
        ], 200);
    }
}
