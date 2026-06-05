<?php
class CSRF {
    public static function generateToken() {
        if (empty($_SESSION['csrf_token'])) {//في اول مرة المتغير لا يكون موجود وبالتالي يرجع  تروو 
         $_SESSION['csrf_token']= bin2hex(random_bytes(36));   
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateToken($token) {
        if (!isset($_SESSION['csrf_token'])) return false;// اذا مش موجود 
    
        return hash_equals($_SESSION['csrf_token'], $token);//hash_equals : تقارن نصين مع بعض 
    }

}