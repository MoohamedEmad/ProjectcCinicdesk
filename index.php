<?php
session_start();
define('BASE_URL', '/clinicdesk/');// نعرف ثابت يحتوي على ملف جذر المشروع 

$page = $_GET['page'] ?? 'auth';// auth مصفوفة تستقبل المعاملات الي جاية من الرابط اذا كان يوجد رابط ياخدذه اذا لا ياخد الافتراضي  a
$action = $_GET['action'] ?? 'login';

$controllerFile = __DIR__ . "/controllers/" . ucfirst($page) . "Controller.php";// لعدم التكرار نحصل عاسم الصفخة من الرابط  ونكبر اول حرف  Controller.php يوجد ثلاث ملفات ينتهو ب  controllers داخل ملف ال  
if (file_exists($controllerFile)) {
    require_once $controllerFile;// اذا موجود نستدعيه في مجلد  المشروع عرفه لكي نتمكن من عمل اوبجيكت له   
    $className = ucfirst($page) . "Controller";
    if (class_exists($className)) {
        $controller = new $className();
        if (method_exists($controller, $action)) {// الذي يحتوي على الدالة في الرابط موجود في الكلاس الي عملنا له اوبجيكت  action اذا ال 
            $controller->$action();// نفذه 
        } else {
            die("404 - Action not found");
        }
    } else {
        die("404 - Class not found");
    }
} else {
    die("404 - Page not found");
}