<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

class HomeController extends Controller
{
    private function getHomeJsonFile() {
        return json_decode(file_get_contents(public_path('assets/mglUploads/home/home.json')));
    }

    public function userLogs() {
        $ip = $_SERVER['REMOTE_ADDR'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $time = date('d.m.Y H:i:s') . " UTC";
        $data = "";

        $file = fopen(public_path('assets/mglUploads/mgl_logs.txt'), "a+") or die("Unable to open file !");
        $data = $data . "[ " . $time . " ] [ " . $ip . "] [ " . $userAgent . " ] \n";
        $data = $data . "------------------------------------------------------- \n";
        fwrite($file, $data);        
        fclose($file);
    }

    public function index() { 
        $activePhoto= "";
        $timezone = $this->getLocationTimezoneOnIp();      
        $homeJsonFile = $this->getHomeJsonFile();
        
        date_default_timezone_set($timezone);

        // 1 (Monday) , 7 (Sunday)
        $currentDayNumberInWeek = date('N');
        $currentDayNumberInMonth = date('j');
        $currentMonth = date('n');

        if($currentDayNumberInWeek == 6 || $currentDayNumberInWeek == 7)
            $days = $homeJsonFile->photos->weekends;            
        else 
            $days = $homeJsonFile->photos->weekdays; 
            
        foreach ($days as $key => $value) {
            if($value->active == true)
                $activePhoto = $value->photo;
        }

        $result = [
            'photo'                   => $activePhoto,
            'color'                   => $homeJsonFile->colors[$currentDayNumberInWeek]->color,
            'timezone'                => $timezone,
            'currentDayNumberInWeek'  => $currentDayNumberInWeek,
            'currentDayNumberInMonth' => $currentDayNumberInMonth,
            'currentMonth'            => $currentMonth,
            'currentWeek'             => date('W') 
        ];
            
        return response()->json([
            'result' => $result
        ]);
    }

    public function selectDailyPhoto() {
        $this->clearPhotos();
        $jsonFile = $this->getHomeJsonFile();

        // 1 (Monday) , 7 (Sunday)
        $currentDayNumberInWeek = date('N'); 

        if($currentDayNumberInWeek == 6 || $currentDayNumberInWeek == 7)
            $jsonFile->photos->weekends[rand(0, count($jsonFile->photos->weekends) - 1)]->active = true;            
        else 
            $jsonFile->photos->weekdays[rand(0, count($jsonFile->photos->weekdays) - 1)]->active = true;  

        if(file_put_contents(public_path('assets/mglUploads/home/home.json'), json_encode($jsonFile, JSON_PRETTY_PRINT)) == TRUE)
            return response()->json([
                'result' => true
            ]);
        else 
            return response()->json([
                'result' => false
            ]);
    }

    private function clearPhotos() {
        $jsonFile = $this->getHomeJsonFile();

        foreach ($jsonFile->photos->weekdays as $key => $value) {
            $value->active = false;
        }

        foreach ($jsonFile->photos->weekends as $key => $value) {
            $value->active = false;
        }

        file_put_contents(public_path('assets/mglUploads/home/home.json'), json_encode($jsonFile, JSON_PRETTY_PRINT));
    }

    private function getLocationTimezoneOnIp() {        
        $ip = '94.55.209.135';
        // $ip = $_SERVER['REMOTE_ADDR'];
        $ipInfo = file_get_contents('http://ip-api.com/json/' . $ip);
        $ipInfo = json_decode($ipInfo);
    
        return $ipInfo->timezone;
    }
}
