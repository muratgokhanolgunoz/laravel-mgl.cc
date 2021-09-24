<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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

    public function index($language_)
    {
        return response()->json([
            'status' => 'success',
            'result' => $this->getJsonFile($language_)
        ], 200);
    }

    public function getSelectedBlog($language_, $blogId_)
    {
        $blogsJson = $this->getJsonFile($language_);

        if (count($blogsJson) > $blogId_) {
            $selectedBlog = $blogsJson[$blogId_];
            return response()->json([
                'status' => 'success',
                'result' => $selectedBlog
            ], 200);
        } else {
            return response()->json([
                'status' => 'failed',
                'result' => null
            ], 500);
        }
    }

    public function add(Request $request_, $language_)
    {
        $jsonFile = $this->getJsonFile($language_);

        $fileName = date('YmdHis') . $this->generateRandomString();

        $thumbnailName      = $fileName . '_thumbnail';
        $thumbnailExtension = $request_->file('thumbnail')->getClientOriginalExtension();
        $thumbnailUpload    = $request_->file('thumbnail')->move(public_path('assets/mglUploads/blog/files/' . $fileName), $thumbnailName . '.' . $thumbnailExtension);

        $photoName      = $fileName . '_photo';
        $photoExtension = $request_->file('photo')->getClientOriginalExtension();
        $photoUpload    = $request_->file('photo')->move(public_path('assets/mglUploads/blog/files/' . $fileName), $photoName . '.' . $photoExtension);

        $tempArray = [
            'BLOG_SECTION_ITEMS_ID'          => (int)$fileName,
            'BLOG_SECTION_ITEMS_FOLDER_NAME' => $fileName,
            'BLOG_SECTION_ITEMS_THUMBNAIL'   => 'http://localhost:8000/assets/mglUploads/blog/files/' . $fileName . '/' . $thumbnailName . '.' . $thumbnailExtension,
            'BLOG_SECTION_ITEMS_PHOTO'       => 'http://localhost:8000/assets/mglUploads/blog/files/' . $fileName . '/' . $photoName . '.' . $photoExtension,
            'BLOG_SECTION_ITEMS_TITLE'       => $request_->title,
            'BLOG_SECTION_ITEMS_SUMMARY'     => $request_->summary,
            'BLOG_SECTION_ITEMS_ARTICLE'     => $request_->article,
            'BLOG_SECTION_ITEMS_DATE'        => date("Y-m-d H:i:s"),
            'BLOG_SECTION_ITEMS_AUTHOR'      => $request_->author
        ];

        array_push($jsonFile, $tempArray);

        if (file_put_contents(public_path('assets/mglUploads/blog/' . $language_ . '.json'), json_encode($jsonFile, JSON_PRETTY_PRINT)))
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

    public function delete(Request $request_, $language_)
    {
        $jsonFile  = $this->getJsonFile($language_);
        $blogItems = $jsonFile;
        $jsonFile = [];

        foreach ($blogItems as $key => $value) {
            if ($request_->id != $value->BLOG_SECTION_ITEMS_ID) {
                $tempArray = [
                    'BLOG_SECTION_ITEMS_ID'          => (int)$value->BLOG_SECTION_ITEMS_ID,
                    'BLOG_SECTION_ITEMS_FOLDER_NAME' => $value->BLOG_SECTION_ITEMS_FOLDER_NAME,
                    'BLOG_SECTION_ITEMS_THUMBNAIL'   => $value->BLOG_SECTION_ITEMS_THUMBNAIL,
                    'BLOG_SECTION_ITEMS_PHOTO'       => $value->BLOG_SECTION_ITEMS_PHOTO,
                    'BLOG_SECTION_ITEMS_TITLE'       => $value->BLOG_SECTION_ITEMS_TITLE,
                    'BLOG_SECTION_ITEMS_SUMMARY'     => $value->BLOG_SECTION_ITEMS_SUMMARY,
                    'BLOG_SECTION_ITEMS_ARTICLE'     => $value->BLOG_SECTION_ITEMS_ARTICLE,
                    'BLOG_SECTION_ITEMS_DATE'        => $value->BLOG_SECTION_ITEMS_DATE,
                    'BLOG_SECTION_ITEMS_AUTHOR'      => $value->BLOG_SECTION_ITEMS_AUTHOR
                ];

                array_push($jsonFile, $tempArray);
                $tempArray = [];
            } else {
                if (file_exists(public_path('assets/mglUploads/blog/files/' . $value->BLOG_SECTION_ITEMS_FOLDER_NAME . '/' . $value->BLOG_SECTION_ITEMS_FOLDER_NAME . '_thumbnail.' . explode('.', $value->BLOG_SECTION_ITEMS_THUMBNAIL)[1]))) {
                    unlink(public_path('assets/mglUploads/blog/files/' . $value->BLOG_SECTION_ITEMS_FOLDER_NAME . '/' . $value->BLOG_SECTION_ITEMS_FOLDER_NAME . '_thumbnail.' . explode('.', $value->BLOG_SECTION_ITEMS_THUMBNAIL)[1]));
                }

                if (file_exists(public_path('assets/mglUploads/blog/files/' . $value->BLOG_SECTION_ITEMS_FOLDER_NAME . '/' . $value->BLOG_SECTION_ITEMS_FOLDER_NAME . '_photo.' . explode('.', $value->BLOG_SECTION_ITEMS_PHOTO)[1]))) {
                    unlink(public_path('assets/mglUploads/blog/files/' . $value->BLOG_SECTION_ITEMS_FOLDER_NAME . '/' . $value->BLOG_SECTION_ITEMS_FOLDER_NAME . '_photo.' . explode('.', $value->BLOG_SECTION_ITEMS_PHOTO)[1]));
                }

                if (is_dir(public_path('assets/mglUploads/blog/files/'  . $value->BLOG_SECTION_ITEMS_FOLDER_NAME))) {
                    rmdir(public_path('assets/mglUploads/blog/files/'  . $value->BLOG_SECTION_ITEMS_FOLDER_NAME));
                }
            }
        }

        if (file_put_contents(public_path('assets/mglUploads/blog/' . $language_ . '.json'), json_encode($jsonFile, JSON_PRETTY_PRINT)))
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

    public function update(Request $request_, $language_)
    {
        $jsonFile  = $this->getJsonFile($language_);
        $blogItems = $jsonFile;
        $jsonFile = [];

        foreach ($blogItems as $key => $value) {
            if ($request_->id != $value->BLOG_SECTION_ITEMS_ID) {
                $tempArray = [
                    'BLOG_SECTION_ITEMS_ID'          => (int)$value->BLOG_SECTION_ITEMS_ID,
                    'BLOG_SECTION_ITEMS_FOLDER_NAME' => $value->BLOG_SECTION_ITEMS_FOLDER_NAME,
                    'BLOG_SECTION_ITEMS_THUMBNAIL'   => $value->BLOG_SECTION_ITEMS_THUMBNAIL,
                    'BLOG_SECTION_ITEMS_PHOTO'       => $value->BLOG_SECTION_ITEMS_PHOTO,
                    'BLOG_SECTION_ITEMS_TITLE'       => $value->BLOG_SECTION_ITEMS_TITLE,
                    'BLOG_SECTION_ITEMS_SUMMARY'     => $value->BLOG_SECTION_ITEMS_SUMMARY,
                    'BLOG_SECTION_ITEMS_ARTICLE'     => $value->BLOG_SECTION_ITEMS_ARTICLE,
                    'BLOG_SECTION_ITEMS_AUTHOR'      => $value->BLOG_SECTION_ITEMS_AUTHOR,
                    'BLOG_SECTION_ITEMS_DATE'        => $value->BLOG_SECTION_ITEMS_DATE
                ];
            } else {
                if ($request_->thumbnail) {
                    if (file_exists(public_path('assets/mglUploads/blog/files/' . $value->BLOG_SECTION_ITEMS_FOLDER_NAME . '/' . $value->BLOG_SECTION_ITEMS_FOLDER_NAME . '_thumbnail.' . explode('.', $value->BLOG_SECTION_ITEMS_THUMBNAIL)[1]))) {
                        unlink(public_path('assets/mglUploads/blog/files/' . $value->BLOG_SECTION_ITEMS_FOLDER_NAME . '/' . $value->BLOG_SECTION_ITEMS_FOLDER_NAME . '_thumbnail.' . explode('.', $value->BLOG_SECTION_ITEMS_THUMBNAIL)[1]));
                    }

                    $thumbnailName      = $value->BLOG_SECTION_ITEMS_FOLDER_NAME . '_thumbnail';
                    $thumbnailExtension = $request_->file('thumbnail')->getClientOriginalExtension();
                    $thumbnailUpload    = $request_->file('thumbnail')->move(public_path('assets/mglUploads/blog/files/' . $value->BLOG_SECTION_ITEMS_FOLDER_NAME), $thumbnailName . '.' . $thumbnailExtension);
                }

                if ($request_->photo) {
                    if (file_exists(public_path('assets/mglUploads/blog/files/' . $value->BLOG_SECTION_ITEMS_FOLDER_NAME . '/' . $value->BLOG_SECTION_ITEMS_FOLDER_NAME . '_photo.' . explode('.', $value->BLOG_SECTION_ITEMS_PHOTO)[1]))) {
                        unlink(public_path('assets/mglUploads/blog/files/' . $value->BLOG_SECTION_ITEMS_FOLDER_NAME . '/' . $value->BLOG_SECTION_ITEMS_FOLDER_NAME . '_photo.' . explode('.', $value->BLOG_SECTION_ITEMS_PHOTO)[1]));
                    }

                    $photoName      = $value->BLOG_SECTION_ITEMS_FOLDER_NAME . '_photo';
                    $photoExtension = $request_->file('photo')->getClientOriginalExtension();
                    $photoUpload    = $request_->file('photo')->move(public_path('assets/mglUploads/blog/files/' . $value->BLOG_SECTION_ITEMS_FOLDER_NAME), $photoName . '.' . $photoExtension);
                }

                $tempArray = [
                    'BLOG_SECTION_ITEMS_ID'          => (int)$value->BLOG_SECTION_ITEMS_ID,
                    'BLOG_SECTION_ITEMS_FOLDER_NAME' => $value->BLOG_SECTION_ITEMS_FOLDER_NAME,
                    'BLOG_SECTION_ITEMS_THUMBNAIL'   => $request_->thumbnail ? 'http://localhost:8000/assets/mglUploads/blog/files/' . $value->BLOG_SECTION_ITEMS_FOLDER_NAME . '/' . $thumbnailName . '.' . $thumbnailExtension : $value->BLOG_SECTION_ITEMS_THUMBNAIL,
                    'BLOG_SECTION_ITEMS_PHOTO'       => $request_->photo     ? 'http://localhost:8000/assets/mglUploads/blog/files/' . $value->BLOG_SECTION_ITEMS_FOLDER_NAME . '/' . $photoName . '.' . $photoExtension         : $value->BLOG_SECTION_ITEMS_PHOTO,
                    'BLOG_SECTION_ITEMS_TITLE'       => $request_->title,
                    'BLOG_SECTION_ITEMS_SUMMARY'     => $request_->summary,
                    'BLOG_SECTION_ITEMS_ARTICLE'     => $request_->article,
                    'BLOG_SECTION_ITEMS_AUTHOR'      => $request_->author,
                    'BLOG_SECTION_ITEMS_DATE'        => $value->BLOG_SECTION_ITEMS_DATE,
                ];
            }

            array_push($jsonFile, $tempArray);
            $tempArray = [];
        }

        if (file_put_contents(public_path('assets/mglUploads/blog/' . $language_ . '.json'), json_encode($jsonFile, JSON_PRETTY_PRINT)))
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
