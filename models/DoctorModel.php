<?php
require_once __DIR__ . '/../core/BaseModel.php';

class DoctorModel extends BaseModel {
    // جلب قائمة الأطباء مع بيانات المستخدم والتخصص



    public function getAllWithDetails($page, $perPage, $search = '') {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $where = "";
        if (!empty($search)) {
            $where = "WHERE u.name LIKE ? OR s.name LIKE ?";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        $sql = "SELECT d.*, u.name as doctor_name, u.email, u.phone, u.avatar, 
                       s.name as specialization_name
                FROM doctors d
                JOIN users u ON d.user_id = u.id
                JOIN specializations s ON d.specialization_id = s.id
                $where
                ORDER BY u.name ASC
                LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        $types = str_repeat("s", count($params) - 2) . "ii";
        $result = $this->execute($sql, $types, ...$params);
        $doctors = [];
        while ($row = $result->fetch_assoc()) {
            $doctors[] = $row;
        }
        return $doctors;
    }

    public function countAll($search = '') {
        $sql = "SELECT COUNT(*) as total FROM doctors d
                JOIN users u ON d.user_id = u.id
                JOIN specializations s ON d.specialization_id = s.id";
        $params = [];
        if (!empty($search)) {
            $sql .= " WHERE u.name LIKE ? OR s.name LIKE ?";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        $result = $this->execute($sql, str_repeat("s", count($params)), ...$params);
        $row = $result->fetch_assoc();
        return (int)$row['total'];
    }

    // جلب طبيب بواسطة user_id 
    public function findByUserId($userId) {// نعمل دمج مع جدول المستخدم لان الاسم موجود في جدول المسخدم فقط 
        $sql = "SELECT d.*, u.name, u.email, u.phone, u.avatar, s.name as specialization_name
                FROM doctors d
                JOIN users u ON d.user_id = u.id 
                JOIN specializations s ON d.specialization_id = s.id
                WHERE d.user_id = ?";
        $result = $this->execute($sql, "i", $userId);
        return $result->fetch_assoc();
    }

    // إضافة سجل طبيب جديد 
        public function create($data) {
        $sql = "INSERT INTO doctors (user_id, specialization_id, bio, consultation_fee, available_days) 
                VALUES (?, ?, ?, ?, ?)";
        $this->execute($sql, "iisds", 
            $data['user_id'], 
            $data['specialization_id'], 
            $data['bio'], 
            $data['consultation_fee'], 
            $data['available_days']
        );
        return $this->lastInsertId();
    }

    // تحديث بيانات الطبيب
    public function update($userId, $data) {
        $sql = "UPDATE doctors SET specialization_id = ?, bio = ?, consultation_fee = ?, available_days = ? 
                WHERE user_id = ?";
        $this->execute($sql, "isdsi", 
            $data['specialization_id'], 
            $data['bio'], 
            $data['consultation_fee'], 
            $data['available_days'],
            $userId
        );
        return true;
    }

    // جلب قائمة الأطباء لقائمة منسدلة 
    public function getAllForDropdown() {
        $sql = "SELECT d.id, u.name 
        FROM doctors d
        JOIN users u ON d.user_id = u.id 
        ORDER BY u.name";
        $result = $this->execute($sql);
        $list = [];
        while ($row = $result->fetch_assoc()) {
            $list[] = $row;
        }
        return $list;
    }
}