<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

class HomeController extends Controller
{
    private function getHomeJsonFile() {
        $file = json_decode(file_get_contents(base_path('storage/app/public/home/home.json')));
        return $file;
    }

    private function getLanguageJsonFile($language_) {
        $file = json_decode(file_get_contents(base_path('storage/app/public/language/' . $language_ . '/' . $language_ . '.json')));
        return $file; 
    }

    public function index($language_) {
        $languageJsonFile = $this->getLanguageJsonFile($language_);
        $homeJsonFile = $this->getHomeJsonFile();

        date_default_timezone_set($languageJsonFile->timezone);

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
            'timezone'                => $languageJsonFile->timezone,
            'currentDayNumberInWeek'  => $currentDayNumberInWeek,
            'currentDayNumberInMonth' => $currentDayNumberInMonth,
            'currentMonth'            => $currentMonth,
            'currentWeek'             => date('W') 
        ];
            
        return response()->json([
            'status' => 'success',
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

        if(file_put_contents(base_path('storage/app/public/home/home.json'), json_encode($jsonFile, JSON_PRETTY_PRINT)) == TRUE)
            return response()->json([
                'status' => 'success',
                'result' => true
            ]);
        else 
            return response()->json([
                'status' => 'failed',
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

        file_put_contents(base_path('storage/app/public/home/home.json'), json_encode($jsonFile, JSON_PRETTY_PRINT));
    }

    public function asd() {
        $ip = "94.55.209.135";
        $ipInfo = file_get_contents('http://ip-api.com/json/' . $ip);
        $ipInfo = json_decode($ipInfo);
        echo json_encode($ipInfo);
    }
}
