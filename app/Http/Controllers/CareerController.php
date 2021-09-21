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
    public function getJsonFile() {
        return json_decode(file_get_contents(public_path('assets/uploads/career/career.json')));
    }

    public function index() {
        return response()->json([
            'status' => 'success',
            'result' => $this->getJsonFile()
        ], 200);
    }

    private function sendEmail($array_) {
        $company = "Midas Global Logistic";
        $subject = "Career - Midas Global Logistic";

        if($array_['language'] == 'tr') {
            $company = "Midas Global Lojistik";
            $subject = "Kariyer - Midas Global Lojistik";
        }

        $data = [
            'name'    => $array_['name'],
            'surname' => $array_['surname']
        ];

        Mail::send('email/mail_career_' . $array_['language'], $data, function($message) use ($array_, $company, $subject) {
           $message->to($array_['to'], $company)
                   ->bcc('info@mgl.cc')
                   ->subject($subject)
                   ->from('no-reply@mgl.cc', $company);
        });
    }

    public function add(Request $request_) {
        $file = $this->getJsonFile();

        $fileName      = date('YmdHis');
        $fileExtension = $request_->file('file')->getClientOriginalExtension();
        $fileUpload    = $request_->file('file')->move(public_path('assets/uploads/career/files'), $fileName . '.' . $fileExtension);

        $tempArray = [
            'id'      => (int)$fileName,
            'name'    => ucwords($request_->name),
            'surname' => strtoupper($request_->surname),
            'email'   => strtolower($request_->email),
            'phone'   => $request_->phone,
            'message' => $request_->message,
            'file'    => 'http://localhost:8000/assets/uploads/career/files/' . $fileName . '.' . $fileExtension,
            'date'    => date('Y-m-d H:i:s')
        ];

        array_push($file, $tempArray);

        if(file_put_contents(public_path('assets/uploads/career/career.json'), json_encode($file, JSON_PRETTY_PRINT))) {
            $emailData = [
                'name'     => $request_->name,
                'surname'  => $request_->surname,
                'to'       => $request_->email,
                'language' => $request_->language
            ];

            $this->sendEmail($emailData);
            return response()->json([
                'status' => 'success',
                'result' => true
            ], 200);
        } else
        {
            return response()->json([
                'status' => 'failed',
                'result' => false
            ], 500);
        }
    }
}
