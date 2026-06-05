<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../models/AppointmentModel.php';
require_once __DIR__ . '/../models/DoctorModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../core/Paginator.php';
require_once __DIR__ . '/../models/PrescriptionModel.php'; 
class DoctorController {
    private function redirectWithError($message, $action = 'dashboard') {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => $message];
        header("Location: index.php?page=doctor&action=$action");
        exit;
    }

    private function redirectWithSuccess($message, $action = 'dashboard') {
        $_SESSION['flash'] = ['type' => 'success', 'message' => $message];
        header("Location: index.php?page=doctor&action=$action");
        exit;
    }

    // لوحة تحكم الطبيب (إحصائيات سريعة)
    public function dashboard() {
        Auth::requireRole('doctor', 'admin'); // الطبيب أو الأدمن يمكنه رؤية لوحة الطبيب
        $doctorId = $this->getDoctorId();
        if (!$doctorId) {
            $this->redirectWithError("بيانات الطبيب غير مكتملة");
            return;
        }
        $appointmentModel = new AppointmentModel();
        $todayCount = $appointmentModel->countTodayForDoctor($doctorId); //مواعيد الدكتور بتاريخ اليوم الحالي 
        $pendingCount = $appointmentModel->countByDoctor($doctorId, 'pending');
        $confirmedCount = $appointmentModel->countByDoctor($doctorId, 'confirmed');
        $completedCount = $appointmentModel->countByDoctor($doctorId, 'completed');
        $upcoming = $appointmentModel->getUpcomingForDoctor($doctorId, 5); 

        $pageTitle = "لوحة تحكم الطبيب";
        $currentPage = 'dashboard';
        require_once __DIR__ . '/../views/partials/header.php';
        require_once __DIR__ . '/../views/partials/navbar.php';
        require_once __DIR__ . '/../views/partials/sidebar.php';
        ?>
        <div class="content-wrapper">
            <section class="content-header"><div class="container-fluid"><h1>لوحة التحكم</h1></div></section>
            <section class="content">
                <div class="container-fluid">
                    <div class="row"><!--ينشئ الكروت بطريقة صفوف -->
                        <div class="col-md-3"><div class="small-box bg-info"><div class="inner"><h3><?= $todayCount ?></h3><p>مواعيد اليوم</p></div></div></div>
                        <div class="col-md-3"><div class="small-box bg-warning"><div class="inner"><h3><?= $pendingCount ?></h3><p>قيد الانتظار</p></div></div></div>
                        <div class="col-md-3"><div class="small-box bg-primary"><div class="inner"><h3><?= $confirmedCount ?></h3><p>مؤكدة</p></div></div></div>
                        <div class="col-md-3"><div class="small-box bg-success"><div class="inner"><h3><?= $completedCount ?></h3><p>مكتملة</p></div></div></div>
                    </div>
                    <div class="card"><div class="card-header"><h3 class="card-title">المواعيد القادمة</h3></div>
                    <div class="card-body">
                        <?php if ($upcoming): ?><ul><?php foreach ($upcoming as $app): ?><li><?= $app['appt_date'] ?> <?= $app['appt_time'] ?> - مريض: <?= htmlspecialchars($app['patient_name']) ?> (<?= $app['status'] ?>)</li><?php endforeach; ?></ul><?php else: ?>لا توجد مواعيد قادمة<?php endif; ?>
                    </div></div>
                </div>
            </section>
        </div>
        <?php require_once __DIR__ . '/../views/partials/footer.php';
    }

    // عرض جدول المواعيد الخاصة بالطبيب
    public function schedule() {
        Auth::requireRole('doctor');
        $doctorId = $this->getDoctorId();
        if (!$doctorId) { $this->redirectWithError("بيانات الطبيب غير مكتملة"); return; }
        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        $perPage = 10;
        $status = $_GET['status'] ?? '';
        $appModel = new AppointmentModel();
        $total = $appModel->countByDoctor($doctorId, $status);// تجلب كل مواعيد دطتور معين مع امكانية الفرز حسب حالة الموعد
        $paginator = new Paginator($total, $perPage, $page);
        $appointments = $appModel->getByDoctor($doctorId, $page, $perPage, $status);// تجلب كل مواعيد دكتور معين مع ميزة العرض بصفحات
        $pageTitle = "جدول المواعيد";
        $currentPage = 'schedule';
        require_once __DIR__ . '/../views/partials/header.php';
        require_once __DIR__ . '/../views/partials/navbar.php';
        require_once __DIR__ . '/../views/partials/sidebar.php';
        ?>
        <div class="content-wrapper">
            <section class="content-header"><div class="container-fluid"><h1>جدول المواعيد</h1></div></section>
            <section class="content">
                <div class="card">
                    <div class="card-header">
                        <form method="get" class="form-inline">
                            <input type="hidden" name="page" value="doctor"><input type="hidden" name="action" value="schedule">
                            <label class="mr-2">الحالة:</label>
                            <select name="status" class="form-control mr-2" onchange="this.form.submit()">
                                <option value="">الكل</option>
                                <option value="pending" <?= $status=='pending'?'selected':'' ?>>قيد الانتظار</option>
                                <option value="confirmed" <?= $status=='confirmed'?'selected':'' ?>>مؤكد</option>
                                <option value="completed" <?= $status=='completed'?'selected':'' ?>>مكتمل</option>
                                <option value="cancelled" <?= $status=='cancelled'?'selected':'' ?>>ملغي</option>
                            </select>
                        </form>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered">
                            <thead><tr><th>المريض</th><th>التاريخ</th><th>الوقت</th><th>الحالة</th><th>السبب</th><th>الإجراءات</th></tr></thead>
                            <tbody>
                                <?php foreach ($appointments as $app): ?>
                                <tr>
                                    <td><?= htmlspecialchars($app['patient_name']) ?></td>
                                    <td><?= $app['appt_date'] ?></td>
                                    <td><?= $app['appt_time'] ?></td>
                                    <td><span class="badge badge-<?= $this->statusBadge($app['status']) ?>"><?= $app['status'] ?></span></td>
                                    <td><?= htmlspecialchars($app['reason']) ?></td>
                                    <td><?php if ($app['status'] == 'pending'): ?>
    <form action="index.php?page=doctor&action=updateStatus" method="post" style="display:inline;">
        <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
        <input type="hidden" name="id" value="<?= $app['id'] ?>">
        <input type="hidden" name="status" value="confirmed">
        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('تأكيد الموعد؟')">تأكيد</button>
    </form>
    <form action="index.php?page=doctor&action=updateStatus" method="post" style="display:inline;">
        <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
        <input type="hidden" name="id" value="<?= $app['id'] ?>">
        <input type="hidden" name="status" value="cancelled">
        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('إلغاء الموعد؟')">إلغاء</button>
    </form>
<?php elseif ($app['status'] == 'confirmed'): ?>
    <form action="index.php?page=doctor&action=updateStatus" method="post" style="display:inline;">
        <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
        <input type="hidden" name="id" value="<?= $app['id'] ?>">
        <input type="hidden" name="status" value="completed">
        <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('إكمال الموعد؟ سيتم فتح نموذج إضافة روشتة')">إكمال</button>
    </form>
    <form action="index.php?page=doctor&action=updateStatus" method="post" style="display:inline;">
        <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
        <input type="hidden" name="id" value="<?= $app['id'] ?>">
        <input type="hidden" name="status" value="cancelled">
        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('إلغاء الموعد؟')">إلغاء</button>
    </form>
<?php endif; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?= $this->renderPagination($paginator, ['status'=>$status]) ?>
                    </div>
                </div>
            </section>
        </div>
        <?php require_once __DIR__ . '/../views/partials/footer.php';
    }

    // تحديث حالة الموعد (GET request with CSRF protection, but we'll use POST for safety)
    public function updateStatus() {
        Auth::requireRole('doctor');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectWithError("طلب غير صالح", 'schedule');
            return;
        }
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            $this->redirectWithError("طلب غير مصرح به", 'schedule');
            return;
        }
        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        if (!$id || !in_array($status, ['confirmed','completed','cancelled'])) {
            $this->redirectWithError("بيانات غير صالحة", 'schedule');
            return;
        }
        $doctorId = $this->getDoctorId();
        if (!$doctorId) { $this->redirectWithError("بيانات الطبيب غير مكتملة", 'schedule'); return; }
        $appModel = new AppointmentModel();
        $app = $appModel->getById($id);
        if (!$app || $app['doctor_id'] != $doctorId) {
            $this->redirectWithError("هذا الموعد لا يخصك", 'schedule');
            return;
        }
        // قواعد منطقية: pending -> confirmed/cancelled ; confirmed -> completed/cancelled
        $allowed = [];
        if ($app['status'] == 'pending') $allowed = ['confirmed','cancelled'];
        elseif ($app['status'] == 'confirmed') $allowed = ['completed','cancelled'];
        else $allowed = [];
        if (!in_array($status, $allowed)) {
            $this->redirectWithError("لا يمكن تغيير الحالة من {$app['status']} إلى $status", 'schedule');
            return;
        }
        $appModel->updateStatus($id, $status);
        if ($status == 'completed') {
            $_SESSION['flash'] = ['type'=>'success', 'message'=>'تم إكمال الموعد، يمكنك الآن إضافة روشتة'];
            header("Location: index.php?page=doctor&action=prescriptionForm&id=$id");
            exit;
        }
        $this->redirectWithSuccess("تم تحديث حالة الموعد إلى $status", 'schedule');
    }

    // عرض نموذج إضافة الروشتة
    public function prescriptionForm() {
        Auth::requireRole('doctor');
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) { $this->redirectWithError("معرف الموعد غير صالح", 'schedule'); return; }
        $doctorId = $this->getDoctorId();
        $appModel = new AppointmentModel();
        $app = $appModel->getById($id);
        if (!$app || $app['doctor_id'] != $doctorId || $app['status'] != 'completed') {
            $this->redirectWithError("لا يمكن إضافة روشتة لهذا الموعد", 'schedule');
            return;
        }
        // التحقق من وجود روشتة مسبقة
        $prescModel = new PrescriptionModel();
        $existing = $prescModel->findByAppointmentId($id);
        $pageTitle = "إضافة روشتة";
        $currentPage = 'schedule';
        require_once __DIR__ . '/../views/partials/header.php';
        require_once __DIR__ . '/../views/partials/navbar.php';
        require_once __DIR__ . '/../views/partials/sidebar.php';
        ?>
        <div class="content-wrapper">
            <section class="content-header"><div class="container-fluid"><h1>إضافة روشتة للمريض</h1></div></section>
            <section class="content">
                <div class="card">
                    <div class="card-body">
                        <form action="index.php?page=doctor&action=prescriptionStore" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
                            <input type="hidden" name="appointment_id" value="<?= $id ?>">
                            <div class="form-group"><label>التشخيص</label><textarea name="diagnosis" rows="3" class="form-control" required><?= htmlspecialchars($existing['diagnosis'] ?? '') ?></textarea></div>
                            <div class="form-group"><label>الأدوية والجرعات</label><textarea name="medications" rows="5" class="form-control" required><?= htmlspecialchars($existing['medications'] ?? '') ?></textarea></div>
                            <div class="form-group"><label>ملاحظات إضافية</label><textarea name="notes" rows="3" class="form-control"><?= htmlspecialchars($existing['notes'] ?? '') ?></textarea></div>
                            <button type="submit" class="btn btn-primary">حفظ الروشتة</button>
                            <a href="index.php?page=doctor&action=schedule" class="btn btn-secondary">إلغاء</a>
                        </form>
                    </div>
                </div>
            </section>
        </div>
        <?php require_once __DIR__ . '/../views/partials/footer.php';
    }


public function prescriptionStore() {
    Auth::requireRole('doctor');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirectWithError("طلب غير صالح", 'schedule'); return; }
    if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) { $this->redirectWithError("طلب غير مصرح به", 'schedule'); return; }

    $appointment_id = (int)($_POST['appointment_id'] ?? 0);
    $diagnosis = trim($_POST['diagnosis'] ?? '');
    $medications = trim($_POST['medications'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if (empty($diagnosis) || empty($medications)) {
        $this->redirectWithError("التشخيص والأدوية مطلوبة", "prescriptionForm&id=$appointment_id");
        return;
    }

    $doctorId = $this->getDoctorId();
    $appModel = new AppointmentModel();
    $app = $appModel->getById($appointment_id);
    if (!$app || $app['doctor_id'] != $doctorId || $app['status'] != 'completed') {
        $this->redirectWithError("لا يمكن إضافة روشتة", 'schedule');
        return;
    }

    $prescModel = new PrescriptionModel();
    $existing = $prescModel->findByAppointmentId($appointment_id);
    $file_path = null;  // لا نرفع ملفات

    if ($existing) {
        $prescModel->update($appointment_id, [
            'diagnosis' => $diagnosis,
            'medications' => $medications,
            'notes' => $notes,
            'file_path' => $file_path
        ]);
    } else {
        $prescModel->create([
            'appointment_id' => $appointment_id,
            'diagnosis' => $diagnosis,
            'medications' => $medications,
            'notes' => $notes,
            'file_path' => $file_path
        ]);
    }

    $_SESSION['flash'] = ['type' => 'success', 'message' => 'تم حفظ الروشتة بنجاح'];
    header("Location: index.php?page=doctor&action=schedule");
    exit;
}

    private function getDoctorId() {
        $user = Auth::user();
        if (!$user) return null;
        $doctorModel = new DoctorModel();
        $doctor = $doctorModel->findByUserId($user['id']);
        return $doctor ? $doctor['id'] : null;
    }

    private function statusBadge($status) {
        switch ($status) {
            case 'pending': return 'warning';
            case 'confirmed': return 'primary';
            case 'completed': return 'success';
            case 'cancelled': return 'danger';
            default: return 'secondary';
        }
    }

    private function renderPagination($paginator, $params = []) {
        if ($paginator->totalPages() <= 1) return '';
        $query = http_build_query(array_merge($_GET, $params));
        $html = '<nav><ul class="pagination justify-content-center">';
        if ($paginator->hasPrev()) $html .= '<li class="page-item"><a class="page-link" href="?p='.$paginator->prevPage().'&'.$query.'">السابق</a></li>';
        for ($i=1; $i<=$paginator->totalPages(); $i++) $html .= '<li class="page-item '.($i==$paginator->currentPage?'active':'').'"><a class="page-link" href="?p='.$i.'&'.$query.'">'.$i.'</a></li>';
        if ($paginator->hasNext()) $html .= '<li class="page-item"><a class="page-link" href="?p='.$paginator->nextPage().'&'.$query.'">التالي</a></li>';
        $html .= '</ul></nav>';
        return $html;
    }
}