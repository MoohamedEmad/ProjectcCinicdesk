<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../models/DoctorModel.php';
require_once __DIR__ . '/../models/AppointmentModel.php';
require_once __DIR__ . '/../models/PrescriptionModel.php';
require_once __DIR__ . '/../core/Paginator.php';


class PatientController {


    private function redirectWithError($message) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => $message];
        header('Location: index.php?page=patient&action=dashboard');
        exit;
    }
    private function redirectWithSuccess($message, $action = 'dashboard') {
    $_SESSION['flash'] = ['type' => 'success', 'message' => $message];
    header("Location: index.php?page=patient&action=$action");
    exit;
}
public function dashboard() {
    Auth::requireRole('patient');
    $patientId = Auth::user()['id'];
    $appModel = new AppointmentModel();
    $activeCount = $appModel->countByPatient($patientId, 'pending') + $appModel->countByPatient($patientId, 'confirmed');
    $completedCount = $appModel->countByPatient($patientId, 'completed');
    $prescModel = new PrescriptionModel();
    $prescriptionCount = 0;
    $completedApps = $appModel->getByPatient($patientId, 1, 100, 'completed');
    foreach ($completedApps as $app) {
        if ($prescModel->findByAppointmentId($app['id'])) $prescriptionCount++;
    }
    $nextAppointment = $appModel->getNextForPatient($patientId); // بتجيب الموعد المستقبلي 

    $pageTitle = "لوحة تحكم المريض";
    $currentPage = 'dashboard';
    require_once __DIR__ . '/../views/partials/header.php';
    require_once __DIR__ . '/../views/partials/navbar.php';
    require_once __DIR__ . '/../views/partials/sidebar.php';
    ?>
    <div class="content-wrapper">
        <section class="content-header"><div class="container-fluid"><h1>لوحة التحكم</h1></div></section>
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-4"><div class="small-box bg-info"><div class="inner"><h3><?= $activeCount ?></h3><p>مواعيد نشطة</p></div></div></div>
                    <div class="col-md-4"><div class="small-box bg-success"><div class="inner"><h3><?= $completedCount ?></h3><p>مواعيد مكتملة</p></div></div></div>
                    <div class="col-md-4"><div class="small-box bg-warning"><div class="inner"><h3><?= $prescriptionCount ?></h3><p>الروشتات المتاحة</p></div></div></div>
                </div>
                <?php if ($nextAppointment): ?>
                <div class="card"><div class="card-header"><h3>موعدك القادم</h3></div>
                <div class="card-body">
                    <strong>الدكتور:</strong> <?= htmlspecialchars($nextAppointment['doctor_name']) ?><br>
                    <strong>التاريخ:</strong> <?= $nextAppointment['appt_date'] ?> الساعة <?= $nextAppointment['appt_time'] ?><br>
                    <strong>الحالة:</strong> <span class="badge badge-<?= $this->statusBadge($nextAppointment['status']) ?>"><?= $nextAppointment['status'] ?></span>
                </div></div>
                <?php endif; ?>
            </div>
        </section>
    </div>
    <?php require_once __DIR__ . '/../views/partials/footer.php';
}

    // عرض نموذج حجز موعد
    public function bookForm() {
        Auth::requireRole('patient');
        $doctorModel = new DoctorModel();
        $doctors = $doctorModel->getAllForDropdown(); // تحتاج إلى إضافة هذه الدالة في DoctorModel

        $pageTitle = "حجز موعد جديد";
        $currentPage = 'appointments';
        require_once __DIR__ . '/../views/partials/header.php';
        require_once __DIR__ . '/../views/partials/navbar.php';
        require_once __DIR__ . '/../views/partials/sidebar.php';
        ?>
        <div class="content-wrapper">
            <section class="content-header"><div class="container-fluid"><h1>حجز موعد</h1></div></section>
            <section class="content">
                <div class="card">
                    <div class="card-body">
                        <form action="index.php?page=patient&action=bookStore" method="post">
                            <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
                            <div class="form-group">
                                <label>اختر الدكتور</label>
                                <select name="doctor_id" id="doctor_id" class="form-control" required>
                                    <option value="">-- اختر --</option>
                                    <?php foreach ($doctors as $doc): ?>
                                        <option value="<?= $doc['id'] ?>"><?= htmlspecialchars($doc['name']) ?></option><!--عند الاختيار يتم ارسال القيمة الي هيا الي دي تاع الدكتور الي في الفاليو للفاليو الي فوق في السيلكت -->
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>تاريخ الموعد</label>
                                <input type="date" name="appt_date" id="appt_date" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>الوقت</label>
                                <select name="appt_time" id="appt_time" class="form-control" required>
                                    <option value="">-- اختر --</option>
                                    <?php
                                    for ($h = 9; $h <= 16; $h++) {// نلف على علمواعيد من الساعة تاسعة صباحة حتى الرابعة عصرا 
                                        $time = sprintf("%02d:00:00", $h);//ننشئ تنسيق اول خانة بعرض عمودين ويتم وضع صفر اما الرقم اذا كان اقل من 10
                                        echo "<option value='$time'>$time</option>";
                                        if ($h != 16) {// الموعد ينتهي عند 4 عصرا في حال كانت الساعة  4 عصر لا يضع 4:30
                                            $time30 = sprintf("%02d:30:00", $h);
                                            echo "<option value='$time30'>$time30</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>سبب الزيارة</label>
                                <textarea name="reason" class="form-control" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">حجز</button>
                            <a href="index.php?page=patient&action=dashboard" class="btn btn-secondary">إلغاء</a>
                        </form>
                    </div>
                </div>
            </section>
        </div>
        <?php
        require_once __DIR__ . '/../views/partials/footer.php';
        }

    // معالجة طلب الحجز
    public function bookStore() {
        Auth::requireRole('patient');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectWithError("طلب غير صالح");
            return;
        }
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            $this->redirectWithError("طلب غير مصرح به");
            return;
        }

        $doctorId = (int)($_POST['doctor_id'] ?? 0);
        $date = $_POST['appt_date'] ?? '';
        $time = $_POST['appt_time'] ?? '';
        $reason = trim($_POST['reason'] ?? '');

        if ($doctorId <= 0 || empty($date) || empty($time) || empty($reason)) {
            $this->redirectWithError("جميع الحقول مطلوبة");
            return;
        }

        // التحقق من أن التاريخ مش في الماضي
        if ($date < date('Y-m-d')) {
            $this->redirectWithError("لا يمكن حجز موعد في تاريخ ماضٍ");
            return;
        }

        // التحقق من أن اليوم ضمن أيام عمل الطبيب
        $appointmentModel = new AppointmentModel();
        $availableDays = $appointmentModel->getAvailableDays($doctorId);// تعمل استعلام لتحضر ايام العمل لطبيب عندم قمت بانشائه 
        $dayOfWeek = date('D', strtotime($date)); // Sunنحول التاريخ الذي اختاره المستخدم في خانة التايرخ مثلا  22/5/2026الى احد ايام الاسبوع مثل 
        if (!in_array($dayOfWeek, $availableDays)) {// ثم نقارن هل اليوم بعد ان احضرناه من التاريح غير متاح ضمن ايام عمل الدكتور 
            $this->redirectWithError("الطبيب لا يعمل في هذا اليوم");
            return;
        }

//من وجود نفس الحجز بنفس التوقيت لدكتور والمريض المحدد hasconflictفي الاول باستدعاء دالة bookتتحقق دالة  
        $patientId = Auth::user()['id'];// الاي دي تاع الدكتور حصلنا عليه عندما اختارالمريض الدكتور والان نحصل على الاي دي للمريض المسجل حاليا 
       $result = $appointmentModel->book([
    'patient_id' => $patientId,
    'doctor_id' => $doctorId,
    'appt_date' => $date,
    'appt_time' => $time,
    'reason' => $reason
]);
        if ($result === false) {
            $this->redirectWithError("هذا الموعد محجوز مسبقاً، اختر وقتاً آخر");
            return;
        }

        $_SESSION['flash'] = ['type' => 'success', 'message' => "تم حجز الموعد بنجاح، في انتظار تأكيد الطبيب"];
        header('Location: index.php?page=patient&action=myAppointments');
        exit;
    }










    public function getNextForPatient($patientId) {
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


    // عرض مواعيد المريض
    public function myAppointments() {
        Auth::requireRole('patient');
        $currentPage = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        $perPage = 10;
        $statusFilter = $_GET['status'] ?? '';
        $patientId = Auth::user()['id'];

        $appointmentModel = new AppointmentModel();
        $total = $appointmentModel->countByPatient($patientId, $statusFilter);
        $paginator = new Paginator($total, $perPage, $currentPage);
        $appointments = $appointmentModel->getByPatient($patientId, $currentPage, $perPage, $statusFilter);

        $pageTitle = "مواعيدي";
        $currentPage = 'appointments';
        require_once __DIR__ . '/../views/partials/header.php';
        require_once __DIR__ . '/../views/partials/navbar.php';
        require_once __DIR__ . '/../views/partials/sidebar.php';
        ?>
        <div class="content-wrapper">
            <section class="content-header"><div class="container-fluid"><h1>مواعيدي</h1></div></section>
            <section class="content">
                <div class="card">
                    <div class="card-header">
                        <form method="get" class="form-inline">
                            <input type="hidden" name="page" value="patient">
                            <input type="hidden" name="action" value="myAppointments">
                            <label class="mr-2">تصفية حسب الحالة:</label>
                            <select name="status" class="form-control mr-2" onchange="this.form.submit()"> <!--في كل مرة يختار حالة يعيد اسرال النموذج -->
                                <option value="">الكل</option>
                                <option value="pending" <?= $statusFilter == 'pending' ? 'selected' : '' ?>>قيد الانتظار</option>
                                <option value="confirmed" <?= $statusFilter == 'confirmed' ? 'selected' : '' ?>>مؤكد</option>
                                <option value="completed" <?= $statusFilter == 'completed' ? 'selected' : '' ?>>مكتمل</option>
                                <option value="cancelled" <?= $statusFilter == 'cancelled' ? 'selected' : '' ?>>ملغي</option>
                            </select>
                        </form>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead><tr><th>الدكتور</th><th>التاريخ</th><th>الوقت</th><th>الحالة</th><th>السبب</th><th>إجراء</th></tr></thead>
                            <tbody>
                                <?php if (empty($appointments)): ?>
                                    <tr><td colspan="6" class="text-center">لا توجد مواعيد</td></tr>
                                <?php else: ?>
                                    <?php foreach ($appointments as $app): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($app['doctor_name']) ?></td>
                                            <td><?= $app['appt_date'] ?></td>
                                            <td><?= $app['appt_time'] ?></td>
                                            <td><span class="badge badge-<?= $this->statusBadge($app['status']) ?>"><?= $app['status'] ?></span></td><!--يتم انشاء اطار بلون مختلف حسب الحالة --->
                                            <td><?= htmlspecialchars($app['reason']) ?></td>
                                            <td>
                                                <?php if ($app['status'] == 'pending'): ?>
                                                    <a href="index.php?page=patient&action=cancelAppointment&id=<?= $app['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('هل تريد إلغاء هذا الموعد؟')">إلغاء</a>
                                                <?php endif; ?>
                                                <?php if ($app['status'] == 'completed'): ?>
                                                <a href="index.php?page=patient&action=prescriptions" class="btn btn-sm btn-info">عرض الروشتات</a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <?php if ($paginator->totalPages() > 1): ?> <!--في حال كان عدد الصفحات اكثر من واحد -->
                            <nav><ul class="pagination">
                                <?php for ($i=1;$i<=$paginator->totalPages();$i++): ?><!--لف من واحد الى اخر صفحة -->
                                    <li class="page-item <?= $i==$currentPage?'active':'' ?>"><!--في كل لفة سينئ الرقم وفي حال كانالصفحة الخالية راح تكوناكتيف يعني لو كنا في الصفحة رقم واحد راح تكون بس رقم واحد هي الي اكتف -->
                                        <a class="page-link" href="?p=<?= $i ?>&status=<?= urlencode($statusFilter) ?>"><?= $i ?>
                                    </a></li>
                                    <?php endfor; ?></ul></nav>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </div>
        <?php
        require_once __DIR__ . '/../views/partials/footer.php';
    }

    // إلغاء موعد (لمنع التضارب نستخدم POST مع CSRF)
    public function cancelAppointment() {
        Auth::requireRole('patient');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectWithError("طلب غير صالح");
            return;
        }
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            $this->redirectWithError("طلب غير مصرح به");
            return;
        }
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->redirectWithError("معرف غير صالح");
            return;
        }
        // التحقق من ملكية الموعد قبل التحديث
        $appModel = new AppointmentModel();
        $app = $appModel->getById($id); // تحتاج إلى إضافة getById في AppointmentModel
        if (!$app || $app['patient_id'] != Auth::user()['id']) {
            $this->redirectWithError("لا يمكنك إلغاء هذا الموعد");
            return;
        }
        if ($app['status'] != 'pending') {
            $this->redirectWithError("لا يمكن إلغاء موعد غير معلق");
            return;
        }
        $appModel->updateStatus($id, 'cancelled');
        $_SESSION['flash'] = ['type' => 'success', 'message' => "تم إلغاء الموعد"];
        header('Location: index.php?page=patient&action=myAppointments');
        exit;
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
    public function prescriptions() {
    Auth::requireRole('patient');
    $patientId = Auth::user()['id'];
    $appModel = new AppointmentModel();
    // جلب جميع المواعيد المكتملة 
    $completedApps = $appModel->getByPatient($patientId, 1, 100, 'completed');
    $prescriptions = [];
    $prescModel = new PrescriptionModel();
    foreach ($completedApps as $app) {
        $presc = $prescModel->findByAppointmentId($app['id']);
        if ($presc) {
            $presc['appointment'] = $app;// نضيف على الروشتة بيانات الموعد كاملة 
            $prescriptions[] = $presc;//ثم نضيف النسخة النهائية الروشتةمع الموعد الى المصفوفة 
        }
    }
    $pageTitle = "الروشتات";
    $currentPage = 'prescriptions';
    require_once __DIR__ . '/../views/partials/header.php';
    require_once __DIR__ . '/../views/partials/navbar.php';
    require_once __DIR__ . '/../views/partials/sidebar.php';
    ?>
    <div class="content-wrapper">
        <section class="content-header"><div class="container-fluid"><h1>الروشتات</h1></div></section>
        <section class="content">
            <div class="card">
                <div class="card-body table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr><th>الدكتور</th><th>التاريخ</th><th>التشخيص</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($prescriptions as $presc): ?>
                            <tr>
                                <td><?= htmlspecialchars($presc['appointment']['doctor_name']) ?></td>
                                <td><?= $presc['appointment']['appt_date'] ?></td>
                                <td><?= nl2br(htmlspecialchars($presc['diagnosis'])) ?></td>
                                
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
    <?php require_once __DIR__ . '/../views/partials/footer.php';
}


public function downloadPrescription() {
    Auth::requireRole('patient');
    $appointment_id = (int)($_GET['id'] ?? 0);
    $patientId = Auth::user()['id'];
    $appModel = new AppointmentModel();
    $app = $appModel->getById($appointment_id);
    if (!$app || $app['patient_id'] != $patientId) {
        die("غير مصرح لك بتحميل هذه الروشتة");
    }
    $prescModel = new PrescriptionModel();
    $presc = $prescModel->findByAppointmentId($appointment_id);
    if (!$presc || !$presc['file_path']) {
        die("لا يوجد ملف مرفق لهذه الروشتة");
    }
    $file = __DIR__ . '/../' . $presc['file_path'];
    if (!file_exists($file)) {
        die("الملف غير موجود على الخادم");
    }
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="prescription_'.$appointment_id.'.pdf"');
    readfile($file);
    exit;
}
}
