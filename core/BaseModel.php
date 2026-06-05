<?php
require_once 'Database.php';// getinstance ننزل الكلاس هذا حتى نستطيع استخدام دالة ال 

abstract class BaseModel {
    protected $db;//singlton  المتغير الذي سنضع في الاوبجيكت ال 

    public function __construct() {
        $this->db = Database::getInstance();
    }

    protected function execute($sql, $types = "", ...$params) {
        // sql:هوا الاستعلام الذي سكون في كل مودل للبحث عن مطابقة الايميل مثلا 
    // params :where email=params  مصفوفة تخزن فيها القيمة التي سيكون بناءا عليها الاستعلام مثل  

        $conn = $this->db->getConnection();  // نتصل بقاعدة البيانات 
        $stmt = $conn->prepare($sql);//prepare(SELECT * FROM table WHERE id =params)  نحضر الاستعلام وترسله الى قاعدة البيانات الي انشئناها  
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        if (!empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
        $stmt->execute();//stmt نفذ الاستعلام ونتيجته توضع في 
        if ($stmt->error) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        $result = $stmt->get_result();// result نحضر النتيجة ونضعها في 
        $stmt->close();
        return $result;
    }

    protected function lastInsertId() {
        return $this->db->getConnection()->insert_id;// ترجع صح اذا قاعدة البيانات تم انشائها   
    }// insert_id هي خاصية جاهزةتنشئ اي دي 
}