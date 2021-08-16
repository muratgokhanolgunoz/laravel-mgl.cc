<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

class BlogController extends Controller
{
    public function __construct() {
        date_default_timezone_set('Europe/Istanbul');
    }

    public function getJsonFile($language_) {
        $file = json_decode(file_get_contents(base_path('storage/app/public/language/' . $language_ . '/' . $language_ . '.json')));
        return $file;
    }

    public function index($language_) {
        $languageFile = $this->getJsonFile($language_);
        return response()->json([
            'status' => 'success',
            'result' => $languageFile->blog->body->items
        ], 200);
    }

    public function add(Request $request_, $language_) {
        $languageFile = $this->getJsonFile($language_);

        $fileName = date('YmdHis');

        $thumbnailName      = $fileName . '_thumbnail';
        $thumbnailExtension = $request_->file('thumbnail')->getClientOriginalExtension();
        $thumbnailUpload    = $request_->file('thumbnail')->storeAs('public/blog/' . $fileName, $thumbnailName . '.' . $thumbnailExtension);

        $photoName      = $fileName . '_photo';
        $photoExtension = $request_->file('photo')->getClientOriginalExtension();
        $photoUpload    = $request_->file('photo')->storeAs('public/blog/' . $fileName, $photoName . '.' . $photoExtension);

        $summaryName      = $fileName . '_summary';
        $summaryExtension = $request_->file('summary')->getClientOriginalExtension();
        $summaryUpload    = $request_->file('summary')->storeAs('public/blog/' . $fileName, $summaryName . '.' . $summaryExtension);

        $articleName      = $fileName . '_article';
        $articleExtension = $request_->file('article')->getClientOriginalExtension();
        $articleUpload    = $request_->file('article')->storeAs('public/blog/' . $fileName, $articleName . '.' . $articleExtension);

        $tempArray = [
            'BLOG_SECTION_ITEMS_ID'        => (int)$fileName,
            'BLOG_SECTION_ITEMS_THUMBNAIL' => base_path('storage/app/public/blog/') . $fileName . '/' . $thumbnailName . '.' . $thumbnailExtension,
            'BLOG_SECTION_ITEMS_PHOTO'     => base_path('storage/app/public/blog/') . $fileName . '/' . $photoName . '.' . $photoExtension,
            'BLOG_SECTION_ITEMS_TITLE'     => $request_->title,
            'BLOG_SECTION_ITEMS_SUMMARY'   => base_path('storage/app/public/blog/') . $fileName . '/' . $summaryName . '.' . $summaryExtension,
            'BLOG_SECTION_ITEMS_FULL_TEXT' => base_path('storage/app/public/blog/') . $fileName . '/' . $articleName . '.' . $articleExtension,
            'BLOG_SECTION_ITEMS_DATE'      => date("Y-m-d H:i:s"),
            'BLOG_SECTION_ITEMS_AUTHOR'    => $request_->author
        ];

        array_push($languageFile->blog->body->items, $tempArray);

        if(file_put_contents(base_path('storage/app/public/language/' . $language_ . '/' . $language_ . '.json'), json_encode($languageFile, JSON_PRETTY_PRINT))) 
            return response()->json([
                'status' => 'success',
                'result' => true
            ], 200);
        else
            return response()->json([
                'status' => 'failed',
                'result' => false
            ], 500);
    }

    public function delete(Request $request_, $language_) {
        $tempArray = [];
        $languageFile  = $this->getJsonFile($language_);
        $blogItems = $languageFile->blog->body->items;
        $languageFile->blog->body->items = [];
                
        foreach ($blogItems as $key => $value) {
            if($request_->id != $value->BLOG_SECTION_ITEMS_ID) {
                $tempArray = [
                    'BLOG_SECTION_ITEMS_ID'        => (int)$value->BLOG_SECTION_ITEMS_ID,
                    'BLOG_SECTION_ITEMS_THUMBNAIL' => $value->BLOG_SECTION_ITEMS_THUMBNAIL,
                    'BLOG_SECTION_ITEMS_PHOTO'     => $value->BLOG_SECTION_ITEMS_PHOTO,
                    'BLOG_SECTION_ITEMS_TITLE'     => $value->BLOG_SECTION_ITEMS_TITLE,
                    'BLOG_SECTION_ITEMS_SUMMARY'   => $value->BLOG_SECTION_ITEMS_SUMMARY,
                    'BLOG_SECTION_ITEMS_FULL_TEXT' => $value->BLOG_SECTION_ITEMS_FULL_TEXT,
                    'BLOG_SECTION_ITEMS_DATE'      => $value->BLOG_SECTION_ITEMS_DATE,
                    'BLOG_SECTION_ITEMS_AUTHOR'    => $value->BLOG_SECTION_ITEMS_AUTHOR
                ];

                array_push($languageFile->blog->body->items, $tempArray);
                $tempArray = [];
            } else {
                unlink(base_path('storage/app/public/blog/' . $value->BLOG_SECTION_ITEMS_ID . '/' . $value->BLOG_SECTION_ITEMS_ID . '_thumbnail.jpg'));
                unlink(base_path('storage/app/public/blog/' . $value->BLOG_SECTION_ITEMS_ID . '/' . $value->BLOG_SECTION_ITEMS_ID . '_photo.jpg'));
                unlink(base_path('storage/app/public/blog/' . $value->BLOG_SECTION_ITEMS_ID . '/' . $value->BLOG_SECTION_ITEMS_ID . '_summary.rtf'));
                unlink(base_path('storage/app/public/blog/' . $value->BLOG_SECTION_ITEMS_ID . '/' . $value->BLOG_SECTION_ITEMS_ID . '_article.rtf'));
                rmdir(base_path('storage/app/public/blog/'  . $value->BLOG_SECTION_ITEMS_ID));
            }            
        }          

        if(file_put_contents(base_path('storage/app/public/language/' . $language_ . '/' . $language_ . '.json'), json_encode($languageFile, JSON_PRETTY_PRINT))) 
            return response()->json([
                'status' => 'success',
                'result' => true
            ], 200);
        else
            return response()->json([
                'status' => 'failed',
                'result' => false
            ], 500);
    }
}
