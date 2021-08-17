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
        $file = json_decode(file_get_contents(base_path('storage/app/public/blog/' . $language_ . '.json')));
        return $file;
    }

    public function index($language_) {
        $jsonFile = $this->getJsonFile($language_);
        return response()->json([
            'status' => 'success',
            'result' => $jsonFile
        ], 200);
    }

    public function add(Request $request_, $language_) {
        $jsonFile = $this->getJsonFile($language_);

        $fileName = date('YmdHis');

        $thumbnailName      = $fileName . '_thumbnail';
        $thumbnailExtension = $request_->file('thumbnail')->getClientOriginalExtension();
        $thumbnailUpload    = $request_->file('thumbnail')->storeAs('public/blog/files/' . $fileName, $thumbnailName . '.' . $thumbnailExtension);

        $photoName      = $fileName . '_photo';
        $photoExtension = $request_->file('photo')->getClientOriginalExtension();
        $photoUpload    = $request_->file('photo')->storeAs('public/blog/files/' . $fileName, $photoName . '.' . $photoExtension);

        $summaryName      = $fileName . '_summary';
        $summaryExtension = $request_->file('summary')->getClientOriginalExtension();
        $summaryUpload    = $request_->file('summary')->storeAs('public/blog/files/' . $fileName, $summaryName . '.' . $summaryExtension);

        $articleName      = $fileName . '_article';
        $articleExtension = $request_->file('article')->getClientOriginalExtension();
        $articleUpload    = $request_->file('article')->storeAs('public/blog/files/' . $fileName, $articleName . '.' . $articleExtension);

        $tempArray = [
            'BLOG_SECTION_ITEMS_ID'        => (int)$fileName,
            'BLOG_SECTION_ITEMS_THUMBNAIL' => base_path('storage/app/public/blog/files/') . $fileName . '/' . $thumbnailName . '.' . $thumbnailExtension,
            'BLOG_SECTION_ITEMS_PHOTO'     => base_path('storage/app/public/blog/files/') . $fileName . '/' . $photoName . '.' . $photoExtension,
            'BLOG_SECTION_ITEMS_TITLE'     => $request_->title,
            'BLOG_SECTION_ITEMS_SUMMARY'   => base_path('storage/app/public/blog/files/') . $fileName . '/' . $summaryName . '.' . $summaryExtension,
            'BLOG_SECTION_ITEMS_ARTICLE' => base_path('storage/app/public/blog/files/') . $fileName . '/' . $articleName . '.' . $articleExtension,
            'BLOG_SECTION_ITEMS_DATE'      => date("Y-m-d H:i:s"),
            'BLOG_SECTION_ITEMS_AUTHOR'    => $request_->author
        ];

        array_push($jsonFile, $tempArray);

        if(file_put_contents(base_path('storage/app/public/blog/' . $language_ . '.json'), json_encode($jsonFile, JSON_PRETTY_PRINT ))) 
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
        $jsonFile  = $this->getJsonFile($language_);
        $blogItems = $jsonFile;
        $jsonFile = [];
                
        foreach ($blogItems as $key => $value) {
            if($request_->id != $value->BLOG_SECTION_ITEMS_ID) {
                $tempArray = [
                    'BLOG_SECTION_ITEMS_ID'        => (int)$value->BLOG_SECTION_ITEMS_ID,
                    'BLOG_SECTION_ITEMS_THUMBNAIL' => $value->BLOG_SECTION_ITEMS_THUMBNAIL,
                    'BLOG_SECTION_ITEMS_PHOTO'     => $value->BLOG_SECTION_ITEMS_PHOTO,
                    'BLOG_SECTION_ITEMS_TITLE'     => $value->BLOG_SECTION_ITEMS_TITLE,
                    'BLOG_SECTION_ITEMS_SUMMARY'   => $value->BLOG_SECTION_ITEMS_SUMMARY,
                    'BLOG_SECTION_ITEMS_ARTICLE'   => $value->BLOG_SECTION_ITEMS_ARTICLE,
                    'BLOG_SECTION_ITEMS_DATE'      => $value->BLOG_SECTION_ITEMS_DATE,
                    'BLOG_SECTION_ITEMS_AUTHOR'    => $value->BLOG_SECTION_ITEMS_AUTHOR
                ];

                array_push($jsonFile, $tempArray);
                $tempArray = [];
            } else {
                unlink(base_path('storage/app/public/blog/files/' . $value->BLOG_SECTION_ITEMS_ID . '/' . $value->BLOG_SECTION_ITEMS_ID . '_thumbnail.jpg'));
                unlink(base_path('storage/app/public/blog/files/' . $value->BLOG_SECTION_ITEMS_ID . '/' . $value->BLOG_SECTION_ITEMS_ID . '_photo.jpg'));
                unlink(base_path('storage/app/public/blog/files/' . $value->BLOG_SECTION_ITEMS_ID . '/' . $value->BLOG_SECTION_ITEMS_ID . '_summary.rtf'));
                unlink(base_path('storage/app/public/blog/files/' . $value->BLOG_SECTION_ITEMS_ID . '/' . $value->BLOG_SECTION_ITEMS_ID . '_article.rtf'));
                rmdir(base_path('storage/app/public/blog/files/'  . $value->BLOG_SECTION_ITEMS_ID));
            }            
        }          

        if(file_put_contents(base_path('storage/app/public/blog/' . $language_ . '.json'), json_encode($jsonFile, JSON_PRETTY_PRINT))) 
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
