<?php
class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        require_once __DIR__ . '/../config/database.php';//  ('/../') الان نقول له ارجع خطوة للخلف من المسار  (__DIR__)  وهذه هي قيمة  C:\wamp64\www\clinicdesk\core هذا الملف موجود في 
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);// conn ونخزنه في المتغير  database  ينشئ قاعدة بيانات    
        if ($this->conn->connect_error) {// اذا لم ينشئه يعطي اكسبشن 
            throw new RuntimeException("Database connection failed");
        }
        $this->conn->set_charset("utf8mb4");
    }

    public static function getInstance() {//واحد نستخدم السينقلتون باترين  obj لكي نحصل على 
        if (self::$instance === null) {//اذا كان المتغير فارغ 
            self::$instance = new Database();//null بعد هيك كل ما يستدعي الدالة راح يكون المتغير لا يساوي  instance انشئ الاوبجيكت في اول مرة سيكون فارغ فهينشئ اوبجيكت ويضعه في المغير الثابت 
        }
        return self::$instance;//obj فيرجع نفس ال 
    }

    public function getConnection() {//conn قاعدة البيانات مخزنة داحل ال 
        return $this->conn;
    }
}