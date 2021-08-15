<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

class LanguageController extends Controller
{
    public function getLanguage($language_) {
        $file = json_decode(file_get_contents(base_path('storage/app/public/language/' . $language_ . '/' . $language_ . '.json')));
        return response()->json(['message' => 'success', 'result' => $file], 200); 
    }
}
