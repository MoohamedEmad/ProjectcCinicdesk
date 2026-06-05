<?php
require_once __DIR__ . '/../core/BaseModel.php';// DIR :  models/model.php  مسار الملف هوا   
// '/../ :تعني ارجع خطوة للخلف ثم المسار و ننزل الكلاس المراد 
class UserModel extends BaseModel {
    public function findByEmail($email) {
        $sql = "SELECT id, name, email, password, role, phone, avatar, is_active, created_at 
                FROM users WHERE email = ?";
        $result = $this->execute($sql, "s", $email);
        return $result->fetch_assoc();//نرجعه النتيجة كمتغيرات
    }

    public function findById($id) {
        $sql = "SELECT id, name, email, role, phone, avatar, is_active, created_at 
                FROM users WHERE id = ?";
        $result = $this->execute($sql, "i", $id);
        return $result->fetch_assoc();
    }

    public function create($data){
         $sql = "INSERT INTO users (name, email, password, role, phone) VALUES (?, ?, ?, ?, ?)";// ? : data تعني القيم التي ستاتي من ال 
        $this->execute($sql, "sssss", $data['name'], $data['email'], $data['password'], $data['role'], $data['phone'] ?? null);
        return $this->lastInsertId();
    }
    

    public function update($id, $data) {
        $fields = [];
        $types="";
        $values = [];
        if(isset($data['name'])){
            $fields[] = "name = ?";// sql اذا يحتوي الاوبجيكت على اسم خزن النص هذه في المصفوفة تمهيدا لتمريراها في  
            $types.="s";
            $values[]=$data['name'];//هذه القيمة التي مررها المستخدم 


        }
        if(isset($data['phone'])){
            $fields[]='phone = ?';
            $types .="s";
            $values []=$data['phone'];

        }
 
        if (isset($data['avatar'])) {
            $fields[] = "avatar = ?";
            $types .= "s";
            $values[] = $data['avatar'];
        }
        if (empty($fields)) return false;
        $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";//implode :  SET name = ? , phone = ?    : الى نص لاننا نريد ان يصبح fieldsتقوم بتحويل عناصر ال 
        $types .= "i";
        $values[] = $id;// الان خزنا القيم الي دخلها المستخدم الي بدو يعدها بنضيف الاي دي تاعو عشان ينفذ الاستعلام بناءا على الاي دي 
        $this->execute($sql, $types, ...$values);// Where=?  لتحل مكان ؟ في الاستعلام  bind قيم هذا المتغير تمرر في  values بناءا على القيم التي ادخلها المستخدم وموجودة في ال  BaseModle  نفذ هذه الدالة في كلاس 
        return true;
    }



public function updateFull($id, $data) {
    $sql = "UPDATE users SET name = ?, email = ?, role = ?, phone = ? WHERE id = ?";
    $this->execute($sql, "ssssi", 
        $data['name'], 
        $data['email'], 
        $data['role'], 
        $data['phone'] ?? null, 
        $id
    );
    return true;
}



    public function updatePassword($id, $hash) {
        $sql = "UPDATE users SET password = ? WHERE id = ?";
        $this->execute($sql, "si", $hash, $id);
        return true;
    }

    public function toggleActive($id) {
        $sql = "UPDATE users SET is_active = NOT is_active WHERE id = ?";
        $this->execute($sql, "i", $id);
        return true;
    }


    //تحضر كل المستخدمين لكن تقسمهم الى صفحات بمعدل 10 مستخدمين في كل صفحة 
    public function getAllPaginated($page, $perPage = 10, $role = "") {
        $offset=($page-1)*$perPage;  //لو حط رقم الصفحة 2ف الازاحة هتساوي    2-1*10=10 يعني هيزيح عشر مستخدمين ويعرض من11 
        $params = [];
        $where = "";
        if (!empty($role)) {// للفلترة حسب الدور 
            $where = "WHERE role = ?";
            $params[] = $role;
        }
        $sql = "SELECT id, name, email, role, phone, is_active, created_at 
                FROM users $where ORDER BY created_at DESC LIMIT ? OFFSET ?";// limit =perpage /// offset تعني عدد الصفوف التي سيتم تخطيها  
        $params[] = $perPage;
        $params[] = $offset;
        $types = str_repeat("s", count($params) - 2) . "ii";//perPage and offset لكل من ال i=integer  يعني نص مرتين ل دور و  s لو دخل دورين سيصبح مجموع المصفوفة 4فتصبح : 4-2=2 فهيكرر حرف ال
        $result = $this->execute($sql, $types, ...$params);// ننفذ الاستعلام
        $users = [];
        while ($row = $result->fetch_assoc()) {// نخزن النتيجة في المتغير 
            $users[] = $row;// ثم نخزنه في المصفوفة 
        }
        return $users;
    }

public function countAll($role = "") {
    $where = "";
    $params = [];
    if (!empty($role)) {
        $where = "WHERE role = ?";
        $params[] = $role;
    }
    $sql = "SELECT COUNT(*) as total FROM users $where";
    $result = $this->execute($sql, str_repeat("s", count($params)), ...$params);
    $row = $result->fetch_assoc();
    return (int)$row['total'];
}

}
  
