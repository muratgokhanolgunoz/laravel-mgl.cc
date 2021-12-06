<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers;
use Log;

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
date_default_timezone_set('Europe/Istanbul');

class BlogController extends Controller
{
    public function getJsonFile($language_)
    {
        return json_decode(file_get_contents(public_path('assets/mglUploads/blog/' . $language_ . '.json')));
    }

    public function index($language_, $itemsPerPage_ = null, $page_ = null) {  
        $blogs = array_reverse($this->getJsonFile($language_));        
        $result = [];
        
       if($itemsPerPage_ == null && $page_ == null) {
            return response()->json([
                'result' => $blogs,
            ], 200);
       } else if ($itemsPerPage_ !== null && $page_ !== null) {            
            $pagination = ceil(count($blogs) / $itemsPerPage_);
            $requestPageEndPoint = $itemsPerPage_ * $page_;
            
            for ($i = $requestPageEndPoint - $itemsPerPage_; $i < $requestPageEndPoint; $i++) { 
                if($i < count($blogs)) {
                    array_push($result, $blogs[$i]);  
                }
            }
            
            if($pagination >= $page_ || $pagination == 0) {
            return response()->json([
                    'currentPage'  => (int)$page_,
                    'totalPage'    => (int)$pagination,            
                    'itemsPerPage' => (int)$itemsPerPage_,
                    'totalItem'    => (int)count($blogs), 
                    'result'       => $result
                ], 200); 
            } else {
                return response()->json([
                    'message' => 'Invalid page number',
                    'result' => []
                ], 400);
            }  
       }

       return response()->json([
            'message' => 'Missing parameters',      
            'result' => []
       ], 400);
    }

    public function getSelectedBlog($language_, $blogId_)
    {
        $blogsJson = $this->getJsonFile($language_);
        
        foreach ($blogsJson as $key => $value) {
            if($value->BLOG_SECTION_ITEMS_ID == $blogId_) {
                return response()->json([
                    'result' => $blogsJson[$key]
                ], 200);                    
            }
        }

        return response()->json([
            'message' => 'Invalid BlogID',
            'result' => null
        ], 400);   
    }

    public function add(Request $request_, $language_)
    {
        $jsonFile = $this->getJsonFile($language_);
        $fileName = $this->generateRandomString();

        $thumbnailName      = $fileName . '_thumbnail';
        $thumbnailExtension = $request_->file('thumbnail')->getClientOriginalExtension();
        $thumbnailUpload    = $request_->file('thumbnail')->move(public_path('assets/mglUploads/blog/files/' . $fileName), $thumbnailName . '.' . $thumbnailExtension);

        $photoName      = $fileName . '_photo';
        $photoExtension = $request_->file('photo')->getClientOriginalExtension();
        $photoUpload    = $request_->file('photo')->move(public_path('assets/mglUploads/blog/files/' . $fileName), $photoName . '.' . $photoExtension);

        $tempArray = [
            'BLOG_SECTION_ITEMS_ID'          => count($jsonFile) > 0 ? $jsonFile[count($jsonFile) - 1]->BLOG_SECTION_ITEMS_ID + 1 : 1,
            'BLOG_SECTION_ITEMS_FOLDER_NAME' => $fileName,
            'BLOG_SECTION_ITEMS_THUMBNAIL_EXTENSION' => $thumbnailExtension,
            'BLOG_SECTION_ITEMS_PHOTO_EXTENSION' => $photoExtension,
            'BLOG_SECTION_ITEMS_THUMBNAIL'   => 'http://127.0.0.1:8000/api/assets/mglUploads/blog/files/' . $fileName . '/' . $thumbnailName . '.' . $thumbnailExtension,
            'BLOG_SECTION_ITEMS_PHOTO'       => 'http://127.0.0.1:8000/api/assets/mglUploads/blog/files/' . $fileName . '/' . $photoName . '.' . $photoExtension,
            'BLOG_SECTION_ITEMS_TITLE'       => $request_->title,
            'BLOG_SECTION_ITEMS_SUMMARY'     => $request_->summary,
            'BLOG_SECTION_ITEMS_ARTICLE'     => $request_->article,
            'BLOG_SECTION_ITEMS_DATE'        => date("Y-m-d H:i:s"),
            'BLOG_SECTION_ITEMS_AUTHOR'      => $request_->author
        ];

        array_push($jsonFile, $tempArray);

        if (file_put_contents(public_path('assets/mglUploads/blog/' . $language_ . '.json'), json_encode($jsonFile, JSON_PRETTY_PRINT))) {
            return response()->json([
                'result' => true
            ], 200);
        } else {
            return response()->json([
                'result' => false
            ], 500);
        }            
    }

    public function delete(Request $request_, $language_)
    {
        $jsonFile  = $this->getJsonFile($language_);
        $blogItems = $jsonFile;
        $jsonFile = [];
        $findBlog = false;

        foreach ($blogItems as $key => $value) {
            if ($request_->id != $value->BLOG_SECTION_ITEMS_ID) {
                $tempArray = [
                    'BLOG_SECTION_ITEMS_ID'                  => (int)$value->BLOG_SECTION_ITEMS_ID,
                    'BLOG_SECTION_ITEMS_FOLDER_NAME'         => $value->BLOG_SECTION_ITEMS_FOLDER_NAME,
                    'BLOG_SECTION_ITEMS_THUMBNAIL_EXTENSION' => $value->BLOG_SECTION_ITEMS_THUMBNAIL_EXTENSION,
                    'BLOG_SECTION_ITEMS_PHOTO_EXTENSION'     => $value->BLOG_SECTION_ITEMS_PHOTO_EXTENSION,
                    'BLOG_SECTION_ITEMS_THUMBNAIL'           => $value->BLOG_SECTION_ITEMS_THUMBNAIL,
                    'BLOG_SECTION_ITEMS_PHOTO'               => $value->BLOG_SECTION_ITEMS_PHOTO,
                    'BLOG_SECTION_ITEMS_TITLE'               => $value->BLOG_SECTION_ITEMS_TITLE,
                    'BLOG_SECTION_ITEMS_SUMMARY'             => $value->BLOG_SECTION_ITEMS_SUMMARY,
                    'BLOG_SECTION_ITEMS_ARTICLE'             => $value->BLOG_SECTION_ITEMS_ARTICLE,
                    'BLOG_SECTION_ITEMS_DATE'                => $value->BLOG_SECTION_ITEMS_DATE,
                    'BLOG_SECTION_ITEMS_AUTHOR'              => $value->BLOG_SECTION_ITEMS_AUTHOR
                ];

                array_push($jsonFile, $tempArray);
                $tempArray = [];
            } else {
                $findBlog = true;

                if (file_exists(public_path('assets/mglUploads/blog/files/' . $value->BLOG_SECTION_ITEMS_FOLDER_NAME . '/' . $value->BLOG_SECTION_ITEMS_FOLDER_NAME . '_thumbnail.' . $value->BLOG_SECTION_ITEMS_THUMBNAIL_EXTENSION))) {
                    unlink(public_path('assets/mglUploads/blog/files/' . $value->BLOG_SECTION_ITEMS_FOLDER_NAME . '/' . $value->BLOG_SECTION_ITEMS_FOLDER_NAME . '_thumbnail.' . $value->BLOG_SECTION_ITEMS_THUMBNAIL_EXTENSION));
                }

                if (file_exists(public_path('assets/mglUploads/blog/files/' . $value->BLOG_SECTION_ITEMS_FOLDER_NAME . '/' . $value->BLOG_SECTION_ITEMS_FOLDER_NAME . '_photo.' . $value->BLOG_SECTION_ITEMS_PHOTO_EXTENSION))) {
                    unlink(public_path('assets/mglUploads/blog/files/' . $value->BLOG_SECTION_ITEMS_FOLDER_NAME . '/' . $value->BLOG_SECTION_ITEMS_FOLDER_NAME . '_photo.' . $value->BLOG_SECTION_ITEMS_PHOTO_EXTENSION));
                }

                if (is_dir(public_path('assets/mglUploads/blog/files/'  . $value->BLOG_SECTION_ITEMS_FOLDER_NAME))) {
                    rmdir(public_path('assets/mglUploads/blog/files/'  . $value->BLOG_SECTION_ITEMS_FOLDER_NAME));
                }
            }
        }    

        if($findBlog == true) {
            if (file_put_contents(public_path('assets/mglUploads/blog/' . $language_ . '.json'), json_encode($jsonFile, JSON_PRETTY_PRINT))) {
                return response()->json([
                    'result' => true
                ], 200);
            } else {
                return response()->json([
                    'result' => false
                ], 500);
            }                
        } else {
            return response()->json([
                'message' => "Invalid BlogID",
                'result' => false
            ], 400);
        }  
    }

    public function update(Request $request_, $language_)
    {
        $jsonFile  = $this->getJsonFile($language_);
        $blogItems = $jsonFile;
        $jsonFile = [];
        $findBlog = false;

        foreach ($blogItems as $key => $value) {
            if ($request_->id != $value->BLOG_SECTION_ITEMS_ID) {
                $tempArray = [
                    'BLOG_SECTION_ITEMS_ID'                  => (int)$value->BLOG_SECTION_ITEMS_ID,
                    'BLOG_SECTION_ITEMS_FOLDER_NAME'         => $value->BLOG_SECTION_ITEMS_FOLDER_NAME,
                    'BLOG_SECTION_ITEMS_THUMBNAIL_EXTENSION' => $value->BLOG_SECTION_ITEMS_THUMBNAIL_EXTENSION,
                    'BLOG_SECTION_ITEMS_PHOTO_EXTENSION'     => $value->BLOG_SECTION_ITEMS_PHOTO_EXTENSION,
                    'BLOG_SECTION_ITEMS_THUMBNAIL'           => $value->BLOG_SECTION_ITEMS_THUMBNAIL,
                    'BLOG_SECTION_ITEMS_PHOTO'               => $value->BLOG_SECTION_ITEMS_PHOTO,
                    'BLOG_SECTION_ITEMS_TITLE'               => $value->BLOG_SECTION_ITEMS_TITLE,
                    'BLOG_SECTION_ITEMS_SUMMARY'             => $value->BLOG_SECTION_ITEMS_SUMMARY,
                    'BLOG_SECTION_ITEMS_ARTICLE'             => $value->BLOG_SECTION_ITEMS_ARTICLE,
                    'BLOG_SECTION_ITEMS_DATE'                => $value->BLOG_SECTION_ITEMS_DATE,
                    'BLOG_SECTION_ITEMS_AUTHOR'              => $value->BLOG_SECTION_ITEMS_AUTHOR
                ];
            } else {
                $findBlog = true;
                if ($request_->thumbnail) {
                    if (file_exists(public_path('assets/mglUploads/blog/files/' . $value->BLOG_SECTION_ITEMS_FOLDER_NAME . '/' . $value->BLOG_SECTION_ITEMS_FOLDER_NAME . '_thumbnail.' . $value->BLOG_SECTION_ITEMS_THUMBNAIL_EXTENSION))) {
                        unlink(public_path('assets/mglUploads/blog/files/' . $value->BLOG_SECTION_ITEMS_FOLDER_NAME . '/' . $value->BLOG_SECTION_ITEMS_FOLDER_NAME . '_thumbnail.' . $value->BLOG_SECTION_ITEMS_THUMBNAIL_EXTENSION));
                    }

                    $thumbnailName      = $value->BLOG_SECTION_ITEMS_FOLDER_NAME . '_thumbnail';
                    $thumbnailExtension = $request_->file('thumbnail')->getClientOriginalExtension();
                    $thumbnailUpload    = $request_->file('thumbnail')->move(public_path('assets/mglUploads/blog/files/' . $value->BLOG_SECTION_ITEMS_FOLDER_NAME), $thumbnailName . '.' . $thumbnailExtension);
                }

                if ($request_->photo) {
                    if (file_exists(public_path('assets/mglUploads/blog/files/' . $value->BLOG_SECTION_ITEMS_FOLDER_NAME . '/' . $value->BLOG_SECTION_ITEMS_FOLDER_NAME . '_photo.' . $value->BLOG_SECTION_ITEMS_PHOTO_EXTENSION))) {
                        unlink(public_path('assets/mglUploads/blog/files/' . $value->BLOG_SECTION_ITEMS_FOLDER_NAME . '/' . $value->BLOG_SECTION_ITEMS_FOLDER_NAME . '_photo.' . $value->BLOG_SECTION_ITEMS_PHOTO_EXTENSION));
                    }

                    $photoName      = $value->BLOG_SECTION_ITEMS_FOLDER_NAME . '_photo';
                    $photoExtension = $request_->file('photo')->getClientOriginalExtension();
                    $photoUpload    = $request_->file('photo')->move(public_path('assets/mglUploads/blog/files/' . $value->BLOG_SECTION_ITEMS_FOLDER_NAME), $photoName . '.' . $photoExtension);
                }

                $tempArray = [
                    'BLOG_SECTION_ITEMS_ID'                  => (int)$value->BLOG_SECTION_ITEMS_ID,
                    'BLOG_SECTION_ITEMS_FOLDER_NAME'         => $value->BLOG_SECTION_ITEMS_FOLDER_NAME,
                    'BLOG_SECTION_ITEMS_THUMBNAIL_EXTENSION' => $request_->thumbnail ? $thumbnailExtension : $value->BLOG_SECTION_ITEMS_THUMBNAIL_EXTENSION,
                    'BLOG_SECTION_ITEMS_PHOTO_EXTENSION'     => $request_->photo ? $photoExtension : $value->BLOG_SECTION_ITEMS_PHOTO_EXTENSION,
                    'BLOG_SECTION_ITEMS_THUMBNAIL'           => $request_->thumbnail ? 'http://127.0.0.1:8000/api/assets/mglUploads/blog/files/' . $value->BLOG_SECTION_ITEMS_FOLDER_NAME . '/' . $thumbnailName . '.' . $thumbnailExtension : $value->BLOG_SECTION_ITEMS_THUMBNAIL,
                    'BLOG_SECTION_ITEMS_PHOTO'               => $request_->photo     ? 'http://127.0.0.1:8000/api/assets/mglUploads/blog/files/' . $value->BLOG_SECTION_ITEMS_FOLDER_NAME . '/' . $photoName . '.' . $photoExtension         : $value->BLOG_SECTION_ITEMS_PHOTO,
                    'BLOG_SECTION_ITEMS_TITLE'               => $request_->title,
                    'BLOG_SECTION_ITEMS_SUMMARY'             => $request_->summary,
                    'BLOG_SECTION_ITEMS_ARTICLE'             => $request_->article,
                    'BLOG_SECTION_ITEMS_AUTHOR'              => $request_->author,
                    'BLOG_SECTION_ITEMS_DATE'                => $value->BLOG_SECTION_ITEMS_DATE,
                ];
            }

            array_push($jsonFile, $tempArray);
            $tempArray = [];
        }

        if($findBlog == true) {
            if (file_put_contents(public_path('assets/mglUploads/blog/' . $language_ . '.json'), json_encode($jsonFile, JSON_PRETTY_PRINT))) {
                return response()->json([
                    'result' => true
                ], 200);
            } else {
                return response()->json([
                    'result' => false
                ], 500);
            }         
        } else {            
            return response()->json([
                'message' => 'Invalid blog id',
                'result' => false
            ], 400);
        }            
    }
}