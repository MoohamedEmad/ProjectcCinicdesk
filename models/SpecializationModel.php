<?php
require_once __DIR__ . '/../core/BaseModel.php';

class SpecializationModel extends BaseModel {
    public function getAll() {
        $result = $this->execute("SELECT * FROM specializations ORDER BY name");
        $list = [];
        while ($row = $result->fetch_assoc()) {
            $list[] = $row;
        }
        return $list;
    }
     public function create($name) {
        $sql = "INSERT INTO specializations (name) VALUES (?)";
        $this->execute($sql, "s", $name);
        return $this->lastInsertId();
    }

    // نحدف التخصص في حلا ما كان في دكتور مرتبط فيه
    public function delete($id) {
        // نتحقق هل الايدي المدخل موجود زيه في جدول الدكاترة اذا اه يعني في دكاترة مرتبطين به 
        $checkSql = "SELECT COUNT(*) as total FROM doctors WHERE specialization_id = ?";
        $checkResult = $this->execute($checkSql, "i", $id);
        $row = $checkResult->fetch_assoc();
        if ($row['total'] > 0) {
            return false; // لا يمكن الحذف لوجود أطباء مرتبطين
        }// ما دون ذلك يحذف 
        $sql = "DELETE FROM specializations WHERE id = ?";
        $this->execute($sql, "i", $id);
        return true;
    }

    // التحقق من وجود تخصص بالاسم عشان التكرارر
    public function findByName($name) {
        $sql = "SELECT id FROM specializations WHERE name = ?";
        $result = $this->execute($sql, "s", $name);
        return $result->fetch_assoc();
    }
}
