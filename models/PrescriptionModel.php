<?php
require_once __DIR__ . '/../core/BaseModel.php';

class PrescriptionModel extends BaseModel {
    public function findByAppointmentId($appointment_id) {
        $sql = "SELECT * FROM prescriptions WHERE appointment_id = ?";
        $result = $this->execute($sql, "i", $appointment_id);
        return $result->fetch_assoc();
    }
       public function create($data) {
        $sql = "INSERT INTO prescriptions (appointment_id, diagnosis, medications, notes, file_path) 
                VALUES (?, ?, ?, ?, ?)";
        $this->execute($sql, "issss", 
            $data['appointment_id'], 
            $data['diagnosis'], 
            $data['medications'], 
            $data['notes'], 
            $data['file_path'] ?? null
        );
        return $this->lastInsertId();
    }
    public function update($appointment_id, $data) {
        $sql = "UPDATE prescriptions SET diagnosis = ?, medications = ?, notes = ?, file_path = ? 
                WHERE appointment_id = ?";
        $this->execute($sql, "ssssi", 
            $data['diagnosis'], 
            $data['medications'], 
            $data['notes'], 
            $data['file_path'] ?? null,
            $appointment_id
        );
        return true;
    }
}