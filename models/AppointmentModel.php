<?php
require_once __DIR__ . '/../core/BaseModel.php';

class AppointmentModel extends BaseModel {
    // حجز موعد جديد (مع التحقق من عدم التضارب)
    public function book($data) {
        if($this->hasConflict($data['patient_id'],$data['doctor_id'],$data['appt_date'],$data['appt_time'])){// اذا الدالة رجعت صف اذا في موعد نفس المدخل اذا رجع خطا 
            return false;
        }
        $sql = "INSERT INTO appointments (patient_id, doctor_id, appt_date, appt_time, reason) 
                VALUES (?, ?, ?, ?, ?)";
        return $this->execute($sql, "iisss", 
            $data['patient_id'], 
            $data['doctor_id'], 
            $data['appt_date'], //تاريخ الموعد
            $data['appt_time'], // توقيت الموعد بساعة والدقيقة 
            $data['reason']
        );
    }

    //  التحقق من وجود تعارض 
    public function hasConflict($doctorId, $date, $time) {
        $sql = "SELECT id FROM appointments WHERE doctor_id = ? AND appt_date = ? AND appt_time = ?";
        $result = $this->execute($sql, "iss", $doctorId, $date, $time);
        return $result->num_rows > 0;
    }


// احضار كل المواعيد 
    public function getByPatient($patientId, $page, $perPage, $status = '') {
    $offset = ($page - 1) * $perPage;
    $params = [$patientId];
    $statusFilter = '';
    if (!empty($status)) {
        $statusFilter = " AND a.status = ?";
        $params[] = $status;
    }
    $sql = "SELECT a.*, d.user_id as doctor_user_id, u.name as doctor_name
            FROM appointments a
            JOIN doctors d ON a.doctor_id = d.id
            JOIN users u ON d.user_id = u.id
            WHERE a.patient_id = ? $statusFilter
            ORDER BY a.appt_date DESC, a.appt_time DESC
            LIMIT ? OFFSET ?";
    $params[] = $perPage;
    $params[] = $offset;
    $types = str_repeat("s", count($params) - 2) . "ii";
    $result = $this->execute($sql, $types, ...$params);
    $list = [];
    while ($row = $result->fetch_assoc()) {
        $list[] = $row;
    }
    return $list;
}

    public function countByPatient($patientId, $status = '') {
    $sql = "SELECT COUNT(*) as total FROM appointments WHERE patient_id = ?";
    $params = [$patientId];
    if (!empty($status)) {
        $sql .= " AND status = ?";
        $params[] = $status;
    }
    $result = $this->execute($sql, str_repeat("s", count($params)), ...$params);
    $row = $result->fetch_assoc();
    return (int)$row['total'];
}

// جلب مواعيد طبيب
  public function getByDoctor($doctorId, $page, $perPage, $status = '') {
    $offset = ($page - 1) * $perPage;
    $params = [$doctorId];        
    $statusFilter = '';
    if (!empty($status)) {
        $statusFilter = " AND a.status = ?";
        $params[] = $status;
    }
    $sql = "SELECT a.*, u.name as patient_name 
            FROM appointments a 
            JOIN users u ON a.patient_id = u.id 
            WHERE a.doctor_id = ? $statusFilter 
            ORDER BY a.appt_date DESC, a.appt_time DESC 
            LIMIT ? OFFSET ?";
    $params[] = $perPage;
    $params[] = $offset;
    $types = str_repeat("s", count($params) - 2) . "ii";
    $result = $this->execute($sql, $types, ...$params);
    $list = [];
    while ($row = $result->fetch_assoc()) {
        $list[] = $row;
    }
    return $list;
}
    //جلب جميع المواعيد للأدمن 
public function getAll($page, $perPage, $filters = []) {
    $offset = ($page - 1) * $perPage;
    $params = [];
    $conditions = [];

    // اذا كان في المصفوفة دكتور يضيف الكونديشن لتمريره الى الاسعلام وكذلك القيمة المخزنة في المصفوفة المصفوفة يتم تعبئتها في صفحة الحجوزات مثلا من خانات الفلترة 
    if (!empty($filters['doctor_id'])) {
        $conditions[] = "a.doctor_id = ?";
        $params[] = $filters['doctor_id'];
    }

    //  بحث جزئي من الاسم يعني حتى لو كتب نصف الاسم او حرفين ييجب القيم الي فيها الحرفين 
    if (!empty($filters['patient_name'])) {
        $conditions[] = "pat.name LIKE ?";
        $params[] = "%" . $filters['patient_name'] . "%";
    }

       if (!empty($filters['status'])) {
        $conditions[] = "a.status = ?";
        $params[] = $filters['status'];
    }

    
    if (!empty($filters['start_date'])) {
        $conditions[] = "a.appt_date >= ?";
        $params[] = $filters['start_date'];
    }
    if (!empty($filters['end_date'])) {
        $conditions[] = "a.appt_date <= ?";
        $params[] = $filters['end_date'];
    }

    $where = "";
    if (!empty($conditions)) {
        $where = "WHERE " . implode(" AND ", $conditions);
    }

$sql = "SELECT a.*, 
               doc.user_id as doctor_user_id,
               docu.name as doctor_name,
               pat.name as patient_name
        FROM appointments a
        JOIN doctors doc ON a.doctor_id = doc.id 
        JOIN users docu ON doc.user_id = docu.id 
        JOIN users pat ON a.patient_id = pat.id 
        $where
        ORDER BY a.appt_date DESC, a.appt_time DESC
        LIMIT ? OFFSET ?";

    //إلى نهاية المصفوفة LIMIT و OFFSET إضافة معاملات  
        $params[] = $perPage;
    $params[] = $offset;
    $types = str_repeat("s", count($params) - 2) . "ii";

    $result = $this->execute($sql, $types, ...$params);
    $appointments = [];
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
    return $appointments;
}
    public function getById($id) {
    $sql = "SELECT * FROM appointments WHERE id = ?";
    $result = $this->execute($sql, "i", $id);
    return $result->fetch_assoc();
}
    public function updateStatus($id, $status, $doctorNotes = null) {// confirm دالة تغير حالة تاعت المريض مع الدكتور مثلا 
        $sql = "UPDATE appointments SET status = ?" . ($doctorNotes ? ", doctor_notes = ?" : "") . " WHERE id = ?";// في جملة الاستعلام doctor_notes = ?" تعني انه اذا ادخل ملاحظة الطبيب عند حجز الموعد يتم تمرير ($doctorNotes ? ", doctor_notes = ?" : "")  
        if ($doctorNotes) {// في حال الطبيب ادخل ملاحظة 
            $this->execute($sql, "ssi", $status, $doctorNotes, $id);
        } else {
            $this->execute($sql, "si", $status, $id);
        }
        return true;
    }

    
    public function countToday() {// نجيب كل الحجوزات تاعت اليوم الحالي 
        $sql = "SELECT COUNT(*) as total FROM appointments WHERE appt_date = CURDATE()";// تعني ان يكون التاري مساوي لتاريخ اليوم CURDATE 
        $result = $this->execute($sql);
        $row = $result->fetch_assoc();
        return $row['total'];
    }

    public function countThisWeekGroupedByStatus() {
        $sql = "SELECT status, COUNT(*) as total FROM appointments WHERE WEEK(appt_date) = WEEK(NOW()) GROUP BY status";// ترجع عدد كل حالة تاعت الاسبوع الحالي 
        $result = $this->execute($sql);
        $stats = [];
        while ($row = $result->fetch_assoc()) {
            $stats[$row['status']] = $row['total'];//confirm 2 // pading 1  يعني 
        }
        return $stats;
    }
    

    public function getAvailableDays($doctorId) {// تخزن فيها القيم الشيك بوكس عند اضافة الطبيب  available_days ايام عمل الدكتور 
    $sql = "SELECT available_days FROM doctors WHERE id = ?";
    $result = $this->execute($sql, "i", $doctorId);
    $row = $result->fetch_assoc();
    if ($row) {
        return explode(',', $row['available_days']);//'sun','mon','thu' تصبح "sun,mon,thu" تقسم القيم الى عناصر بدل جعل 
    }
    return [];
}
public function countTodayForDoctor($doctorId) {// ترجع عدد المواعيد لموظف معين بتاريخ اليوم الحالي 
    $sql = "SELECT COUNT(*) as total FROM appointments WHERE doctor_id = ? AND appt_date = CURDATE()";
    $result = $this->execute($sql, "i", $doctorId);
    $row = $result->fetch_assoc();
    return (int)$row['total'];
}

public function countByDoctor($doctorId, $status = '') {// دالة ترجع كل مواعيد دكتور معين مع امكانية الفرز حسب الحالة الموعد 
    $sql = "SELECT COUNT(*) as total FROM appointments WHERE doctor_id = ?";
    $params = [$doctorId];
    if (!empty($status)) {
        $sql .= " AND status = ?";
        $params[] = $status;
    }
    $result = $this->execute($sql, str_repeat("s", count($params)), ...$params);
    $row = $result->fetch_assoc();
    return (int)$row['total'];
}

public function getUpcomingForDoctor($doctorId, $limit = 5) {// لان جدول الكواعيد يحتوي فقط على الاي دي للمريض بينما جدول المستخدمين يحتوي على الاسم نعمل دمج للجدولين بناءا على تساوي الاي ديللمريض ي جدول المواعيد مع الاي دي المريض في جدول المستخدمين 
    $sql = "SELECT a.*, u.name as patient_name 
            FROM appointments a 
            JOIN users u ON a.patient_id = u.id 
            WHERE a.doctor_id = ? AND a.appt_date >= CURDATE() 
            ORDER BY a.appt_date ASC, a.appt_time ASC 
            LIMIT ?";
    $result = $this->execute($sql, "ii", $doctorId, $limit);
    $list = [];
    while ($row = $result->fetch_assoc()) 
        $list[] = $row;
    return $list;
}// بعد دمج الجدولين نجيب اسماء المرضى الخاصين بدكتور معين وموعدهم اليوم مرتبين حسب التوقيت بنجيب اول خمسة 



public function getNextForPatient($patientId) {//بنشيل الماضي منه appt_date>=CURDATE  يعرض الموعد المستقبلي لمريض معين مع اسم الدكتور بنحدد الموعد المستقبلي من خلال  
    $sql = "SELECT a.*, u.name as doctor_name
            FROM appointments a
            JOIN doctors d ON a.doctor_id = d.id
            JOIN users u ON d.user_id = u.id
            WHERE a.patient_id = ? AND a.appt_date >= CURDATE()
            ORDER BY a.appt_date ASC, a.appt_time ASC
            LIMIT 1";
    $result = $this->execute($sql, "i", $patientId);
    return $result->fetch_assoc();
}
// تربط كل الجداول ببعض وترجع عدد القيم
public function countAll($filters = []) {
    $conditions = [];
    $params = [];

    if (!empty($filters['doctor_id'])) {
        $conditions[] = "a.doctor_id = ?";
        $params[] = $filters['doctor_id'];
    }
    if (!empty($filters['patient_name'])) {
        $conditions[] = "pat.name LIKE ?";
        $params[] = "%" . $filters['patient_name'] . "%";
    }
    if (!empty($filters['status'])) {
        $conditions[] = "a.status = ?";
        $params[] = $filters['status'];
    }
    if (!empty($filters['start_date'])) {
        $conditions[] = "a.appt_date >= ?";
        $params[] = $filters['start_date'];
    }
    if (!empty($filters['end_date'])) {
        $conditions[] = "a.appt_date <= ?";
        $params[] = $filters['end_date'];
    }

    $where = "";
    if (!empty($conditions)) {
        $where = "WHERE " . implode(" AND ", $conditions);
    }

    $sql = "SELECT COUNT(*) as total 
            FROM appointments a
            JOIN doctors doc ON a.doctor_id = doc.id
            JOIN users docu ON doc.user_id = docu.id
            JOIN users pat ON a.patient_id = pat.id
            $where";
    $result = $this->execute($sql, str_repeat("s", count($params)), ...$params);
    $row = $result->fetch_assoc();
    return (int)$row['total'];
}
}