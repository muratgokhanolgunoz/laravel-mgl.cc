<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

class CareerController extends Controller
{
    public function getJsonFile() {
        $file = json_decode(file_get_contents(base_path('storage/app/public/career/career.json')));
        return $file;
    }

    public function index() {
        $file = $this->getJsonFile();
        return response()->json([
            'status' => 'success',
            'result' => $file
        ], 200);
    }

    public function add(Request $request_) {
        $tempArray = [];
        $file = $this->getJsonFile();

        $fileName      = date('YmdHis');
        $fileExtension = $request_->file('file')->getClientOriginalExtension();
        $fileUpload    = $request_->file('file')->storeAs('public/career/files/', $fileName . '.' . $fileExtension);

        $tempArray = [
            "id"      => $fileName,
            "name"    => $request_->name,
            "surname" => $request_->surname,
            "email"   => $request_->email,
            "phone"   => $request_->phone,
            "message" => $request_->message,
            "file"    => base_path('storage/app/public/career/files/') . $fileName . '.' . $fileExtension,
            "date"    => date('Y-m-d H:i:s')
        ];

        array_push($file, $tempArray);

        if(file_put_contents(base_path('storage/app/public/career/career.json'), json_encode($file))) 
            return response()->json([
                'status' => 'success',
                'result' => true
            ], 200);
        else
            return response()->json([
                'status' => 'failed',
                'result' => false
            ], 500);

        echo json_encode($tempArray);
    }
}
