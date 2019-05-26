<?php
namespace App\Http\Service;

use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\File\UploadedFile;


class UploadService 
{
    /**
     * handle image upload
     */
    public function uploadImage($path,UploadedFile $image) {
        $uniqueName = $this->createUniqueName();
        while(file_exists(public_path()."\\".$path.$uniqueName.$image->guessExtension())) {
            $uniqueName = $this->createUniqueName();
        }
        if(!file_exists(public_path()."\\".$path)) {
            mkdir(public_path()."\\".$path, 0777, true);
        }
        //get fileName
        $fileName = $path."\\".$uniqueName.".".$image->guessExtension();
        if(!$image->move(public_path()."\\".$path,$uniqueName.'.'.$image->guessExtension())) {
            dd('error');
        }
        return $fileName;
    }




    /**
     * generate unique name
     */
    public function createUniqueName() {
        //create unique name for image
        $letters = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $uniqueName = "";
        for($i=0; $i<=8; $i++) {
            $uniqueName .= $letters[(int)floor(rand(0,61))];
        }
        return $uniqueName;
    }
}