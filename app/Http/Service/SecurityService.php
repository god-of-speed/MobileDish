<?php
namespace App\Service;

class SecurityService {
    /**
     * check if user is an admin
     *
     */
    public function checkUserIsAdmin($userId,$admins) {
        //get the id's of the admins
        $adminArr = [];
        foreach($admins as $admin) {
            $adminArr[] = $admin->user()->first()->id;
        }
        if(in_array($userId,$adminArr)) {
            return true;
        }else{
            return false;
        }
    }
}