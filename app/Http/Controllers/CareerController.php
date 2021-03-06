<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mail;

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');
date_default_timezone_set('Europe/Istanbul');

class CareerController extends Controller
{
    private function getJsonFile()
    {
        return json_decode(file_get_contents(public_path('assets/mglUploads/career/career.json')));
    }

    public function index()
    {
        return response()->json([
            'result' => array_reverse($this->getJsonFile())
        ], 200);
    }

    private function sendEmail($array_)
    {
        $company = "Midas Global Logistic";
        $subject = "Career - Midas Global Logistic";

        if ($array_['language'] == 'tr') {
            $company = "Midas Global Lojistik";
            $subject = "Kariyer - Midas Global Lojistik";
        }

        $data = [
            'name'    => $array_['name'],
            'surname' => $array_['surname']
        ];

        Mail::send('email/mail_career_' . $array_['language'], $data, function ($message) use ($array_, $company, $subject) {
            $message->to($array_['to'], $company)
                ->bcc('info@mgl.cc')
                ->subject($subject)
                ->from('no-reply@mgl.cc', $company);
        });
    }

    public function add(Request $request_)
    {
        $file = $this->getJsonFile();

        $fileName      = $this->generateRandomString();
        $fileExtension = $request_->file('file')->getClientOriginalExtension();
        $fileUpload    = $request_->file('file')->move(public_path('assets/mglUploads/career/files'), $fileName . '.' . $fileExtension);

        $tempArray = [
            'id'        => count($file) > 0 ? $file[count($file) - 1]->id + 1 : 1,
            'name'      => ucwords($request_->name),
            'surname'   => strtoupper($request_->surname),
            'email'     => strtolower($request_->email),
            'phone'     => $request_->phone,
            'message'   => $request_->message,
            "filename"  => $fileName,
            "extension" => $fileExtension,
            'location'  => 'http://127.0.0.1:8000/assets/mglUploads/career/files/' . $fileName . '.' . $fileExtension,
            'date'      => date('Y-m-d H:i:s')
        ];

        array_push($file, $tempArray);

        if (file_put_contents(public_path('assets/mglUploads/career/career.json'), json_encode($file, JSON_PRETTY_PRINT))) {
            $emailData = [
                'name'     => $request_->name,
                'surname'  => $request_->surname,
                'to'       => $request_->email,
                'language' => $request_->language
            ];

            $this->sendEmail($emailData);
            return response()->json([
                'result' => true
            ], 200);
        } else {
            return response()->json([
                'result' => false
            ], 500);
        }
    }

    public function delete(Request $request_) {
        $jsonFile  = $this->getJsonFile();
        $careerItems = $jsonFile;
        $jsonFile = [];
        $findCareer = false;

        foreach ($careerItems as $key => $value) {
            if ($request_->id != $value->id) {
                $tempArray = [
                    'id'        => $value->id,
                    'name'      => $value->name,
                    'surname'   => $value->surname,
                    'email'     => $value->email,
                    'phone'     => $value->phone,
                    'message'   => $value->message,
                    "filename"  => $value->filename,
                    "extension" => $value->extension,
                    'location'  => $value->location,
                    'date'      => $value->date
                ];

                array_push($jsonFile, $tempArray);
                $tempArray = [];
            } else {
                $findCareer = true;

                if (file_exists(public_path('assets/mglUploads/career/files/' . $value->filename . '.' . $value->extension))) {
                    unlink(public_path('assets/mglUploads/career/files/' . $value->filename . '.' . $value->extension));
                }
            }
        }    

        if($findCareer == true) {
            if (file_put_contents(public_path('assets/mglUploads/career/career.json'), json_encode($jsonFile, JSON_PRETTY_PRINT))) {
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
                'message' => "Invalid id",
                'result' => false
            ], 400);
        }  
    }
}
