<?php
class Auth {
    public static function login($user) {//  SESSION نخزن المستخدم داخل ال 
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'   => $user['id'],
            'name' => $user['name'],
            'role' => $user['role']
        ];
    }

    public static function logout() {
        $_SESSION = [];// نفرغها بلكامل عندما نخرج
        if (ini_get("session.use_cookies")) {//نتحقق هل بي اتش بي يستخدم الكوكيز 
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
        session_destroy();// نحذف الجلسة 
    }

    public static function check() {// تتحقق هل السمتخدم سجل دخول لان عند تسديل الدخول نخزن البيانات 
        return isset($_SESSION['user']);
    }

    public static function user() {
        return isset($_SESSION['user']) ?$_SESSION['user']:null;
    }

    public static function role() {
      return isset($_SESSION['user']['role'])?$_SESSION['user']['role']:null;//فيه قيمة رجعه او رجع فارغ  role  اذ ال 
    }

    public static function requireRole(...$roles) {// $roles[]تعني ان المتغير يستقبل اكثر من قيمة ويتم تخزينهم في مصفوفة 
        if (!self::check()) {//تتحق هل تم تسديل الدخول اذا لم يدخل 
            header('Location: index.php?page=auth&action=login');// رجعو على صفخحة تسجيل الدخول 
            exit;// وقف اي تنفيذ بعد نقله للتسجيل الدخول 
        }
        if (!in_array(self::role(), $roles)) {// نجيب دور المسجل حاليا هل هوا مش مساوي لدور المدخل في البراميتر فيعرض رسالة خطاء
            http_response_code(403);//يظهر خطا اذا لم يكن ضمن الادوار المسموح بها 
            die('403 Forbidden - You do not have permission to access this page.');
        }
    }
}