<?php

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../core/Paginator.php';  
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/AppointmentModel.php';
require_once __DIR__ . '/../models/DoctorModel.php';
require_once __DIR__ . '/../models/SpecializationModel.php';



class AdminController {
    public function dashboard() {
    Auth::requireRole('admin');
    $pageTitle = "لوحة تحكم المدير - ClinicDesk";
    $currentPage = 'dashboard';

    // جلب الإحصائيات من قاعدة البيانات
    $userModel = new UserModel();
    $totalUsers = $userModel->countAll();  
    $totalAdmins = $userModel->countAll('admin');
    $totalDoctors = $userModel->countAll('doctor');
    $totalPatients = $userModel->countAll('patient');

    $appointmentModel = new AppointmentModel();
    $todayAppointments = $appointmentModel->countToday(); // مواعيد اليوم

    //  نجيب آخر 5 مواعيد لعرضها في جدول
    $recentAppointments = $appointmentModel->getAll(1, 5, []); // أحدث 5 مواعيد

    require_once __DIR__ . '/../views/partials/header.php';
    require_once __DIR__ . '/../views/partials/navbar.php';
    require_once __DIR__ . '/../views/partials/sidebar.php';
    ?>
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>لوحة التحكم</h1>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="container-fluid"> <!--كونتينر على رض الصفحة بلكامل-->
                <!-- صف البطاقات الإحصائية -->
                <div class="row"><!--صندوق سيوضع العناصر داخله كصف وذا امتلئ الصف ينتقل لسطر التالي-->
                    <div class="col-lg-3 col-6"><!--بطاقة عرضها 3من اصل 12 وحدة و6من اصل 12 في الشاشت الصغيرة يعني عنصرين -->
                        <div class="small-box bg-info"><!--ينشئ البطاقةةوبها ايقونة ولونها ازرق-->
                            <div class="inner">
                                <h3><?= $totalUsers ?></h3>
                                <p>إجمالي المستخدمين</p>
                            </div>
                            <div class="icon"><i class="fas fa-users"></i></div><!--شكل ايقونةالمستخدمين-->
                            <a href="index.php?page=admin&action=users" class="small-box-footer">مزيد من المعلومات <i class="fas fa-arrow-circle-right"></i></a><!-- userعند الضغط عليها ينفذ دالة ال -->
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3><?= $totalAdmins ?></h3>
                                <p>المديرين</p>
                            </div>
                            <div class="icon"><i class="fas fa-user-shield"></i></div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3><?= $totalDoctors ?></h3>
                                <p>الأطباء</p>
                            </div>
                            <div class="icon"><i class="fas fa-user-md"></i></div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3><?= $totalPatients ?></h3>
                                <p>المرضى</p>
                            </div>
                            <div class="icon"><i class="fas fa-user-injured"></i></div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3><?= $todayAppointments ?></h3>
                                <p>مواعيد اليوم</p>
                            </div>
                            <div class="icon"><i class="fas fa-calendar-day"></i></div>
                        </div>
                    </div>
                </div>
                <!-- جدول آخر المواعيد -->
                <div class="card"><!--لانشاء بطاقة الي هيا صندوق الجدول كامل -->
                    <div class="card-header">
                        <h3 class="card-title">آخر المواعيد</h3>
                    </div>
                    <div class="card-body"><!--الجزء الرئيسي-->
                        <table class="table table-bordered"><!--البراميتر الاول ينشئ جدول داخل الجزء الرئيسي الثني ينشئ جدود حول الخلايا --> 
                            <thead>
                                <tr><th>المريض</th><th>الطبيب</th><th>التاريخ</th><th>الوقت</th><th>الحالة</th></tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recentAppointments)): ?><!--بتجيب اخر خمس مرض مع اسم الطبيب تاعهم مع توقيت والحالة-->
                                    <?php foreach ($recentAppointments as $app): ?><!-- ترجع الاناتج في مصفوفة نلف عليها عنصر عنصر getAllالان نتيجة الاستعلام في دالة -->
                                        <tr>
                                            <td><?= htmlspecialchars($app['patient_name'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($app['doctor_name'] ?? '') ?></td>
                                            <td><?= $app['appt_date'] ?></td>
                                            <td><?= $app['appt_time'] ?></td>
                                            <td><span class="badge badge-<?= $this->statusBadgeClass($app['status']) ?>"></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center">لا توجد مواعيد بعد</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <?php
    require_once __DIR__ . '/../views/partials/footer.php';
}

private function statusBadgeClass($status) {
    switch ($status) {
        case 'pending': return 'warning';
        case 'confirmed': return 'primary';
        case 'completed': return 'success';
        case 'cancelled': return 'danger';
        default: return 'secondary';
    }
}


//**************************************************************صفحة المستخدم ********************************************* */





public function users() { // تنفذ في حال ضغط المستخدم على مزيد من المعلومات في سطر 53 او الغاء في دالة انشاء مستحدم جديد
    Auth::requireRole('admin');// تتحقق هل الدور من الضمن الادوار المموح لها 
    $currentPage = isset($_GET['p']) ? (int)$_GET['p'] : 1;// في اول مرة لا يرسل رقم صفحة في رابط الصفحة لذلك ستكون الصفحة الحالية هي 1
    $perPage = 10;
    $roleFilter = $_GET['role'] ?? '';// دور المستخدم 

    $userModel = new UserModel();
    $totalUsers = $userModel->countAll($roleFilter);// بتجيب عدد المستخدمين بناءا على دور المستخدم واذا فش دور بتجيب كل المستخدمين بغض المظر ع دورهم  
    $paginator = new Paginator($totalUsers, $perPage, $currentPage);
    $users = $userModel->getAllPaginated($currentPage, $perPage, $roleFilter);// currentpage-1*perpage = offsetتستحدم لحساب قيمة ال currentPage  ال

    $pageTitle = "إدارة المستخدمين";
    $currentPage = 'users';
    require_once __DIR__ . '/../views/partials/header.php';
    require_once __DIR__ . '/../views/partials/navbar.php';
    require_once __DIR__ . '/../views/partials/sidebar.php';
    ?>
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <h1>المستخدمين</h1>
            </div>
        </section>
        <section class="content"> <!--جسم كامل الجدول مع زر اضافة مستخدم -->
            <div class="card"><!--الزر-->
                <div class="card-header">
                    <a href="index.php?page=admin&action=userCreate" class="btn btn-primary">إضافة مستخدم جديد</a><!--Usercreatعند الضغط عليها ينفذ دالة -->
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr><th>ID</th><th>الاسم</th><th>البريد</th><th>الدور</th><th>الحالة</th><th>إجراءات</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?> <!-- usersتنفذ استعلام بجيب كل البيانات بناءا على الدور وبتحطهم في مصفوفة واحنا اتدعينا الدالة ووضعنا النتيجة فوق في المتغير  getpaginationدالة -->
                            <tr>
                                <td><?= $user['id'] ?></td>
                                <td><?= htmlspecialchars($user['name']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= $user['role'] ?></td>
                                <td><?= $user['is_active'] ? 'نشط' : 'معطل' ?></td>
                                <td>
                                    <a href="index.php?page=admin&action=userEdit&id=<?= $user['id'] ?>" class="btn btn-sm btn-info">تعديل</a><!--id=<user[id] في كل زر في الرابط نكتب -->
                                    <a href="index.php?page=admin&action=userToggleActive&id=<?= $user['id'] ?>" class="btn btn-sm btn-warning">تبديل الحالة</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if ($paginator->totalPages() > 1): ?><!--totalpages: = totaluser/perpage-->
                    <nav>
                        <ul class="pagination">
                            <?php if ($paginator->hasPrev()): ?><!--هذه الدالة ترجع صح اذا كانت الصفحة الحالية اكبر من واحد يعني مش الصفحة اولى اذا في صفحة سابقة-->
                                <li class="page-item"><a class="page-link" href="?p=<?= $paginator->prevPage() ?>">السابق</a></li><!-- تساوي قيمة الصفحة الحالية ناقص واحدpاذا ضغط على السابق يرسل راط وقيمة ال -->
                            <?php endif; ?>
                            <?php for ($i = 1; $i <= $paginator->totalPages(); $i++): ?>
                                <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>"><a class="page-link" href="?p=<?= $i ?>"><?= $i ?></a></li><!--activeيلف من واحد الى عدد الصفحات لحد ما يتساوى الرقم مع الصفحة الخالة ليكتب ان الصفحة -->
                            <?php endfor; ?>
                            <?php if ($paginator->hasNext()): ?>
                                <li class="page-item"><a class="page-link" href="?p=<?= $paginator->nextPage() ?>">التالي</a></li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
    <?php
    require_once __DIR__ . '/../views/partials/footer.php';
}






public function userCreate() { // في السطر 165 عند الضغط على اضافة مستخدم جديد 
    Auth::requireRole('admin');
    $pageTitle = "إضافة مستخدم جديد";
    $currentPage = 'users';
    require_once __DIR__ . '/../views/partials/header.php';
    require_once __DIR__ . '/../views/partials/navbar.php';
    require_once __DIR__ . '/../views/partials/sidebar.php';
    ?>
    <div class="content-wrapper"><!--صندوق المحتوى كامل بعد العيدر والقائمة الجانبية-->
        <section class="content-header"><!--صندوق راس المحتوى-->
            <div class="container-fluid">
                <h1>إضافة مستخدم جديد</h1>
            </div>
        </section>
        <section class="content">
            <div class="card"><!--محتوى الفورم كاملا يوضع في الصندوق والكارد يجعله بحاوف مستديرة وخلفية بيضاء -->
                <div class="card-body"><!--Padding سنشئ -->
                    <form action="index.php?page=admin&action=userStore" method="post"><!--userstoreتعني ان يرسل البيانات الى  -->
                        <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
                        <div class="form-group">
                            <label>الاسم</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>البريد الإلكتروني</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>كلمة المرور</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>الدور</label>
                            <select name="role" class="form-control" required>
                                <option value="admin">مدير</option>
                                <option value="doctor">طبيب</option>
                                <option value="patient">مريض</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>رقم الهاتف</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary">حفظ</button>
                        <a href="index.php?page=admin&action=users" class="btn btn-secondary">إلغاء</a><!--userاذا ضغط الغاء يرسله لدالة-->
                    </form>
                </div>
            </div>
        </section>
    </div>
    <?php
    require_once __DIR__ . '/../views/partials/footer.php';
}




public function userStore() {// الفورم في سطر 237 يرسل البيانات الى هذه الدالة 
    Auth::requireRole('admin');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $this->redirectToUsersWithError("طلب غير صالح");
        return;
    }
    if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
        $this->redirectToUsersWithError("طلب غير مصرح به");
        return;
    }

    $name = trim($_POST['name'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'patient';
    $phone = trim($_POST['phone'] ?? '');

    if (empty($name) || empty($email) || empty($password)) {
        $this->redirectToUsersWithError("الاسم والبريد وكلمة المرور إجبارية");
        return;
    }
    // نتحقق من عدم تكرار البريد
    $userModel = new UserModel();
    if ($userModel->findByEmail($email)) {
        $this->redirectToUsersWithError("البريد الإلكتروني موجود بالفعل");
        return;
    }
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $userId = $userModel->create([
        'name' => $name,
        'email' => $email,
        'password' => $hashed,
        'role' => $role,
        'phone' => $phone
    ]);// الدالة هذه تضيف العنصر الى قاعدة البيانات ثم ترجع الاي دي اذا نجحت الاضافة
    if ($userId) {
        $_SESSION['flash'] = ['type' => 'success', 'message' => "تم إضافة المستخدم بنجاح"];
    } else {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => "حدث خطأ أثناء الإضافة"];
    }
    header('Location: index.php?page=admin&action=users');
    exit;
}

private function redirectToUsersWithError($message) {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => $message];
    header('Location: index.php?page=admin&action=users');
    exit;
}



public function userToggleActive() {// تنفذ اذا ضغط المتخدم على تبديل الخال سطر 182 يتم ارسال الاي دي الخاص بلمستخدم في 
    Auth::requireRole('admin');
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) {
        $this->redirectToUsersWithError("معرف المستخدم غير صالح");
        return;
    }
    //منع تعطيل حساب حالو 
    if ($id == Auth::user()['id']) {
        $this->redirectToUsersWithError("لا يمكنك تعطيل حسابك الخاص");
        return;
    }
    $userModel = new UserModel();
    $userModel->toggleActive($id);// prepar and pind paramالتي تنفذ الاستعلام ب excute وفيه  SET is_activity = NOT Where id =?  دالة تنفذ استعلام 
    $_SESSION['flash'] = ['type' => 'success', 'message' => "تم تبديل حالة المستخدم"];
    header('Location: index.php?page=admin&action=users');
    exit;
}






public function userEdit() {// تنفذ اذا ظغط المستخدم على تعديل سطر 181 
    Auth::requireRole('admin');
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) {
        $this->redirectToUsersWithError("معرف المستخدم غير صالح");
        return;
    }

    $userModel = new UserModel();
    $user = $userModel->findById($id);
    if (!$user) {
        $this->redirectToUsersWithError("المستخدم غير موجود");
        return;
    }

    $pageTitle = "تعديل المستخدم - " . htmlspecialchars($user['name']);
    $currentPage = 'users';
    require_once __DIR__ . '/../views/partials/header.php';
    require_once __DIR__ . '/../views/partials/navbar.php';
    require_once __DIR__ . '/../views/partials/sidebar.php';
    ?>
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <h1>تعديل المستخدم</h1>
            </div>
        </section>
        <section class="content">
            <div class="card">
                <div class="card-body">
                    <form action="index.php?page=admin&action=userUpdate" method="post">
                        <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
                        <input type="hidden" name="id" value="<?= $user['id'] ?>">
                        
                        <div class="form-group">
                            <label>الاسم</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>البريد الإلكتروني</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>الدور</label>
                            <select name="role" class="form-control" required>
                                <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>مدير</option>
                                <option value="doctor" <?= $user['role'] == 'doctor' ? 'selected' : '' ?>>طبيب</option>
                                <option value="patient" <?= $user['role'] == 'patient' ? 'selected' : '' ?>>مريض</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>رقم الهاتف</label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                        </div>
                        <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                        <a href="index.php?page=admin&action=users" class="btn btn-secondary">إلغاء</a>
                    </form>
                </div>
            </div>
        </section>
    </div>
    <?php
    require_once __DIR__ . '/../views/partials/footer.php';
}
public function userUpdate() {
    Auth::requireRole('admin');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $this->redirectToUsersWithError("طلب غير صالح");
        return;
    }
    if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
        $this->redirectToUsersWithError("طلب غير مصرح به");
        return;
    }

    $id = isset($_POST['id'])?(int)$_POST['id']:0;
    if ($id <= 0) {
        $this->redirectToUsersWithError("معرف المستخدم غير صالح");
        return;
    }

    $userModel = new UserModel();
    $user = $userModel->findById($id);
    if (!$user) {
        $this->redirectToUsersWithError("المستخدم غير موجود");
        return;
    }

    $name = trim($_POST['name'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $role = $_POST['role'] ?? 'patient';
    $phone = trim($_POST['phone'] ?? '');

    if (empty($name) || empty($email)) {
        $this->redirectToUsersWithError("الاسم والبريد الإلكتروني إجباريان");
        return;
    }

    // نتحقق ان البريد مش موجود زيه
    $existing = $userModel->findByEmail($email);
    if ($existing && $existing['id'] != $id) {
        $this->redirectToUsersWithError("البريد الإلكتروني موجود بالفعل");
        return;
    }

    // منع تغيير دور المدير لنفسه
    if ($id == Auth::user()['id'] && $role != Auth::user()['role']) {// ترجع المستحدم الحالي يكون مسجل في سيشن نمنع المدير من تعديل دوره user دالة ال 
        $this->redirectToUsersWithError("لا يمكنك تغيير دور حسابك الخاص");
        return;
    }

    $userModel->updateFull($id, [
        'name' => $name,
        'email' => $email,
        'role' => $role,
        'phone' => $phone
    ]);

    $_SESSION['flash'] = ['type' => 'success', 'message' => "تم تحديث المستخدم بنجاح"];
    header('Location: index.php?page=admin&action=users');
    exit;
}




// ************************************************************صفحة الطبيب ***********************************************

public function doctors() {
    Auth::requireRole('admin');
    $currentPage = isset($_GET['p']) ? (int)$_GET['p'] : 1;
    $perPage = 10;
    $search = $_GET['search'] ?? '';// قيمة المدخل في خانة البحث تذهب في الرابط لان الفورم get

    $doctorModel = new DoctorModel();
    $total = $doctorModel->countAll($search);
    $paginator = new Paginator($total, $perPage, $currentPage);
    $doctors = $doctorModel->getAllWithDetails($currentPage, $perPage, $search);

    $pageTitle = "إدارة الأطباء";
    $currentPage = 'doctors';
    require_once __DIR__ . '/../views/partials/header.php';
    require_once __DIR__ . '/../views/partials/navbar.php';
    require_once __DIR__ . '/../views/partials/sidebar.php';
    ?>
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <h1>الأطباء</h1>
            </div>
        </section>
        <section class="content">
            <div class="card">
                <div class="card-header">
                    <a href="index.php?page=admin&action=doctorCreate" class="btn btn-primary">إضافة طبيب</a>
                    <form method="get" class="float-right">
                        <input type="hidden" name="page" value="admin"> <!--نرسل في الرابط قيمة الصفحة -->
                        <input type="hidden" name="action" value="doctors"><!--ونحدد ان يتم ارسال القيمة الي دخلت في البحث الى الدكتور -->
                        <div class="input-group" style="width: 250px;">
                            <input type="text" name="search" class="form-control" placeholder="بحث بالاسم أو التخصص" value="<?= htmlspecialchars($search) ?>">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="submit">بحث</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr><th>ID</th><th>الاسم</th><th>البريد</th><th>التخصص</th><th>رسوم الكشف</th><th>الإجراءات</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($doctors as $doc): ?>
                            <tr>
                                <td><?= $doc['id'] ?></td>
                                <td><?= htmlspecialchars($doc['doctor_name']) ?></td>
                                <td><?= htmlspecialchars($doc['email']) ?></td>
                                <td><?= htmlspecialchars($doc['specialization_name']) ?></td>
                                <td><?= number_format($doc['consultation_fee'], 2) ?> ₪</td>
                                <td>
                                    <a href="index.php?page=admin&action=doctorEdit&id=<?= $doc['user_id'] ?>" class="btn btn-sm btn-info">تعديل</a>
                                </td>
                            ?</tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

<?php if ($paginator->totalPages() > 1): ?>
<nav>
    <ul class="pagination justify-content-center">
        <?php if ($paginator->hasPrev()): ?>
            <li class="page-item"><a class="page-link" href="?p=<?= $paginator->prevPage() ?>&search=<?= urlencode($search) ?>">السابق</a></li>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $paginator->totalPages(); $i++): ?>
            <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                <a class="page-link" href="?p=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
        <?php if ($paginator->hasNext()): ?>
            <li class="page-item"><a class="page-link" href="?p=<?= $paginator->nextPage() ?>&search=<?= urlencode($search) ?>">التالي</a></li>
        <?php endif; ?>
    </ul>
</nav>
<?php endif; ?>             
   </div>
            </div>
        </section>
    </div>
    <?php
    require_once __DIR__ . '/../views/partials/footer.php';
}




// دالة تعديل بيانات الدكتورر
public function doctorEdit() {
    Auth::requireRole('admin');
    $userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;// نحضر الاي دي الخاص بالدكتور 
    if ($userId <= 0) {
        $this->redirectToDoctorsWithError("معرف المستخدم غير صالح");
        return;
    }

    $doctorModel = new DoctorModel();
    $doctor = $doctorModel->findByUserId($userId);// نفحص هل موجود في قاعدة البيانات 
    if (!$doctor) {
        $this->redirectToDoctorsWithError("الطبيب غير موجود");
        return;
    }

    $specModel = new SpecializationModel();
    $specializations = $specModel->getAll();

    $pageTitle = "تعديل بيانات الطبيب";
    $currentPage = 'doctors';
    require_once __DIR__ . '/../views/partials/header.php';
    require_once __DIR__ . '/../views/partials/navbar.php';
    require_once __DIR__ . '/../views/partials/sidebar.php';
    ?>
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <h1>تعديل الطبيب: <?= htmlspecialchars($doctor['name']) ?></h1>
            </div>
        </section>
        <section class="content">
            <div class="card">
                <div class="card-body">
                    <form action="index.php?page=admin&action=doctorUpdate" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
                        <input type="hidden" name="user_id" value="<?= $doctor['user_id'] ?>">
                        
                        <h4>بيانات الحساب</h4>
                        <div class="form-group">
                            <label>الاسم</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($doctor['name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>البريد الإلكتروني</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($doctor['email']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>رقم الهاتف</label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($doctor['phone'] ?? '') ?>">
                        </div>

                        <h4>بيانات الطبيب المهنية</h4>
                        <div class="form-group">
                            <label>التخصص</label>
                            <select name="specialization_id" class="form-control" required>
                                <?php foreach ($specializations as $spec): ?>
                                    <option value="<?= $spec['id'] ?>" <?= $spec['id'] == $doctor['specialization_id'] ? 'selected' : '' ?>><!--اذا كان التخصص الحالي في الحلقة بيساوي تخصص الطبيب المختار يخليه هوا المحدد افتراضيا  -->
                                        <?= htmlspecialchars($spec['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>رسوم الكشف ($)</label>
                            <input type="number" step="0.01" name="consultation_fee" class="form-control" value="<?= $doctor['consultation_fee'] ?>" required>
                        </div>
                        <div class="form-group">
                            <label>أيام العمل (اختر أيام)</label><br>
                            <?php
                            $daysMap = ['Sun'=>'الأحد','Mon'=>'الإثنين','Tue'=>'الثلاثاء','Wed'=>'الأربعاء','Thu'=>'الخميس','Fri'=>'الجمعة','Sat'=>'السبت'];
                            $currentDays = explode(',', $doctor['available_days']);
                            foreach ($daysMap as $key => $day):
                            ?>
                           <div class="form-check form-check-inline">
                               <input class="form-check-input" type="checkbox" name="available_days[]" value="<?= $key ?>" <?= in_array($key, $currentDays) ? 'checked' : '' ?>>
                            <label class="form-check-label"><?= $day ?></label>
                            <?php endforeach; ?>
                        </div>
                        <div class="form-group">
                            <label>السيرة الذاتية</label>
                            <textarea name="bio" class="form-control" rows="3"><?= htmlspecialchars($doctor['bio'] ?? '') ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                        <a href="index.php?page=admin&action=doctors" class="btn btn-secondary">إلغاء</a>
                    </form>
                </div>
            </div>
        </section>
    </div>
    <?php
    require_once __DIR__ . '/../views/partials/footer.php';
}
public function doctorUpdate() {
    Auth::requireRole('admin');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $this->redirectToDoctorsWithError("طلب غير صالح");
        return;
    }
    if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
        $this->redirectToDoctorsWithError("طلب غير مصرح به");
        return;
    }

    $userId = (int)($_POST['user_id'] ?? 0);
    if ($userId <= 0) {
        $this->redirectToDoctorsWithError("معرف المستخدم غير صالح");
        return;
    }

    $name = trim($_POST['name'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $phone = trim($_POST['phone'] ?? '');

    if (empty($name) || empty($email)) {
        $this->redirectToDoctorsWithError("الاسم والبريد الإلكتروني إجباريان");
        return;
    }

    $userModel = new UserModel();
    $existing = $userModel->findByEmail($email);
    if ($existing && $existing['id'] != $userId) {
        $this->redirectToDoctorsWithError("البريد الإلكتروني مستخدم من قبل حساب آخر");
        return;
    }

    // تحديث بيانات المستخدم ونخلي دور الطبيب ثابت
    $userModel->updateFull($userId, [
        'name' => $name,
        'email' => $email,
        'role' => 'doctor',     
        'phone' => $phone
    ]);

    // الحصول على بييانات الطبيب المهنية
    $specialization_id = (int)($_POST['specialization_id'] ?? 0);
    $consultation_fee = (float)($_POST['consultation_fee'] ?? 0);
    $available_days = isset($_POST['available_days']) ? implode(',', $_POST['available_days']) : ''; // أضف هذا السطر
    $bio = trim($_POST['bio'] ?? '');

    $doctorModel = new DoctorModel();
    $doctorModel->update($userId, [
        'specialization_id' => $specialization_id,
        'bio' => $bio,
        'consultation_fee' => $consultation_fee,
        'available_days' => $available_days
    ]);

    $_SESSION['flash'] = ['type' => 'success', 'message' => "تم تحديث بيانات الطبيب بنجاح"];
    header('Location: index.php?page=admin&action=doctors');
    exit;
}
//    دالة اضفة الدكتور ودالة تخزينها 

public function doctorCreate() {
    Auth::requireRole('admin');
    $specModel = new SpecializationModel();
    $specializations = $specModel->getAll();

    $pageTitle = "إضافة طبيب جديد";
    $currentPage = 'doctors';
    require_once __DIR__ . '/../views/partials/header.php';
    require_once __DIR__ . '/../views/partials/navbar.php';
    require_once __DIR__ . '/../views/partials/sidebar.php';
    ?>
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <h1>إضافة طبيب جديد</h1>
            </div>
        </section>
        <section class="content">
            <div class="card">
                <div class="card-body">
                    <form action="index.php?page=admin&action=doctorStore" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
                        
                        <h4>بيانات الحساب</h4>
                        <div class="form-group">
                            <label>الاسم الكامل *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>البريد الإلكتروني *</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>كلمة المرور *</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>رقم الهاتف</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        
                        <h4>البيانات المهنية</h4>
                        <div class="form-group">
                            <label>التخصص *</label>
                            <select name="specialization_id" class="form-control" required>
                                <option value="">اختر التخصص</option>
                                <?php foreach ($specializations as $spec): ?>
                                    <option value="<?= $spec['id'] ?>"><?= htmlspecialchars($spec['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>رسوم الكشف ($)</label>
                            <input type="number" step="0.01" name="consultation_fee" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>أيام العمل</label><br>
                            <?php
                            $days = ['Sun'=>'الأحد','Mon'=>'الإثنين','Tue'=>'الثلاثاء','Wed'=>'الأربعاء','Thu'=>'الخميس','Fri'=>'الجمعة','Sat'=>'السبت'];
                            foreach ($days as $key => $day):
                            ?>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="available_days[]" value="<?= $key ?>">
                                    <label class="form-check-label"><?= $day ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="form-group">
                            <label>السيرة الذاتية</label>
                            <textarea name="bio" class="form-control" rows="3"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">حفظ الطبيب</button>
                        <a href="index.php?page=admin&action=doctors" class="btn btn-secondary">إلغاء</a>
                    </form>
                </div>
            </div>
        </section>
    </div>
    <?php
    require_once __DIR__ . '/../views/partials/footer.php';
}
public function doctorStore() {
    Auth::requireRole('admin');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $this->redirectToDoctorsWithError("طلب غير صالح");
        return;
    }
    if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
        $this->redirectToDoctorsWithError("طلب غير مصرح به");
        return;
    }

    // بيانات الحساب
    $name = trim($_POST['name'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');

    // البيانات المهنية
    $specialization_id = (int)($_POST['specialization_id'] ?? 0);
    $consultation_fee = (float)($_POST['consultation_fee'] ?? 0);
    $available_days = isset($_POST['available_days']) ? implode(',', $_POST['available_days']) : '';
    $bio = trim($_POST['bio'] ?? '');

    // التحقق من الحقول المطلوبة
    if (empty($name) || empty($email) || empty($password) || $specialization_id <= 0) {
        $this->redirectToDoctorsWithError("جميع الحقول المطلوبة غير مكتملة");
        return;
    }

    $userModel = new UserModel();
    if ($userModel->findByEmail($email)) {
        $this->redirectToDoctorsWithError("البريد الإلكتروني موجود بالفعل");
        return;
    }

    // 1. إنشاء حساب بلبيانات الاساسية في جدول المستخدم 
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $userId = $userModel->create([
        'name' => $name,
        'email' => $email,
        'password' => $hashed,
        'role' => 'doctor',
        'phone' => $phone
    ]);

    if (!$userId) {
        $this->redirectToDoctorsWithError("حدث خطأ في إنشاء حساب المستخدم");
        return;
    }

    // 2. doctors إدراج سجل الطبيب ببيانات المهنية في جدول 
    $doctorModel = new DoctorModel();
    try {
        $doctorId = $doctorModel->create([
            'user_id' => $userId,
            'specialization_id' => $specialization_id,
            'bio' => $bio,
            'consultation_fee' => $consultation_fee,
            'available_days' => $available_days
        ]);
        if (!$doctorId) {
            throw new Exception("فشل إدراج الطبيب (قيمة مرتجعة false)");
        }
    } catch (Exception $e) {
        error_log("خطأ في إدراج الطبيب: " . $e->getMessage());
        $this->redirectToDoctorsWithError("فشل إدراج بيانات الطبيب: " . $e->getMessage());
        return;
    }

    $_SESSION['flash'] = ['type' => 'success', 'message' => "تم إضافة الطبيب بنجاح"];
    header('Location: index.php?page=admin&action=doctors');
    exit;
}
//***************************************عرض صفخة التخصصات ******************************************************* */

public function specializations() {
    Auth::requireRole('admin');
    $specModel = new SpecializationModel();
    $specializations = $specModel->getAll();

    $pageTitle = "إدارة التخصصات";
    $currentPage = 'specializations';
    require_once __DIR__ . '/../views/partials/header.php';
    require_once __DIR__ . '/../views/partials/navbar.php';
    require_once __DIR__ . '/../views/partials/sidebar.php';
    ?>
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <h1>التخصصات الطبية</h1>
            </div>
        </section>
        <section class="content">
            <div class="card">
                <div class="card-header">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addModal"><!-- data-target تعني فتح نافذة منبثقة وشكل النافذة هي اسم الاي دي data_toggle=modal-->
                        <i class="fas fa-plus"></i> إضافة تخصص جديد
                    </button>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr><th>#</th><th>اسم التخصص</th><th>الإجراءات</th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($specializations)): ?>
                                <tr><td colspan="3" class="text-center">لا توجد تخصصات بعد</td></tr>
                            <?php else: ?>
                                <?php foreach ($specializations as $spec): ?>
                                    <tr>
                                        <td><?= $spec['id'] ?></td>
                                        <td><?= htmlspecialchars($spec['name']) ?></td>
                                        <td>
                                            <a href="index.php?page=admin&action=specializationDelete&id=<?= $spec['id'] ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('هل أنت متأكد من حذف هذا التخصص؟ سيتم حذف التخصص فقط إذا لم يكن مرتبطاً بأي طبيب.')">
                                                <i class="fas fa-trash"></i> حذف
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>

    <!--  إضافة تخصص جديد -->
    <div class="modal fade" id="addModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="index.php?page=admin&action=specializationStore" method="post">
                    <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
                    <div class="modal-header">
                        <h5 class="modal-title">إضافة تخصص جديد</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>اسم التخصص</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">حفظ التخصص</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
    require_once __DIR__ . '/../views/partials/footer.php';
}

//فحص المدخلات وحفظه في قاعدة البيانات 
public function specializationStore() {
    Auth::requireRole('admin');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $this->redirectToSpecializationsWithError("طلب غير صالح");
        return;
    }
    if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
        $this->redirectToSpecializationsWithError("طلب غير مصرح به");
        return;
    }

    $name = trim($_POST['name'] ?? '');
    if (empty($name)) {
        $this->redirectToSpecializationsWithError("اسم التخصص مطلوب");
        return;
    }

    $specModel = new SpecializationModel();
    // التحقق من عدم التكرار
    if ($specModel->findByName($name)) {
        $this->redirectToSpecializationsWithError("هذا التخصص موجود بالفعل");
        return;
    }

    $specModel->create($name);
    $_SESSION['flash'] = ['type' => 'success', 'message' => "تم إضافة التخصص بنجاح"];
    header('Location: index.php?page=admin&action=specializations');
    exit;
}
// حذف التخصص 
public function specializationDelete() {
    Auth::requireRole('admin');
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;// الاي دي الخاص بالتخصص الي انضغط عليه خذف 
    if ($id <= 0) {
        $this->redirectToSpecializationsWithError("معرف التخصص غير صالح");
        return;
    }

    $specModel = new SpecializationModel();
    if ($specModel->delete($id)) {
        $_SESSION['flash'] = ['type' => 'success', 'message' => "تم حذف التخصص بنجاح"];
    } else {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => "لا يمكن حذف هذا التخصص لأنه مرتبط بأطباء موجودين"];
    }
    header('Location: index.php?page=admin&action=specializations');
    exit;
}
// عرضض رسالة الخطا لخاصة بصفحة التخصصات 
private function redirectToSpecializationsWithError($message) {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => $message];
    header('Location: index.php?page=admin&action=specializations');
    exit;
}
private function redirectToDoctorsWithError($message) {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => $message];
    header('Location: index.php?page=admin&action=doctors');
    exit;
}

//*******************************************الحجوزات************************************* */
public function appointments() {
    Auth::requireRole('admin');
    $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
    $perPage = 10;

    // قراءة القيم التي تم ادخالها في خانات الفلترة $_GET
    $filters = [];
    if (!empty($_GET['doctor_id'])) 
        $filters['doctor_id'] = (int)$_GET['doctor_id'];
    if (!empty($_GET['patient_name'])) 
        $filters['patient_name'] = trim($_GET['patient_name']);
    if (!empty($_GET['status'])) 
        $filters['status'] = $_GET['status'];
    if (!empty($_GET['start_date'])) 
        $filters['start_date'] = $_GET['start_date'];
    if (!empty($_GET['end_date'])) 
        $filters['end_date'] = $_GET['end_date'];

    $appModel = new AppointmentModel();
    $total = $appModel->countAll($filters);
    $paginator = new Paginator($total, $perPage, $page);
    $appointments = $appModel->getAll($page, $perPage, $filters);

    // نجيب قائمة الأطباء للقائمة المنسدلة في الفلتر
    $doctorModel = new DoctorModel();
    $doctors = $doctorModel->getAllForDropdown();// دالة تجيب الاي دي واسم الدكتور وترجعهم في قائمة 

    $pageTitle = "إدارة المواعيد";
    $currentPage = 'appointments';
    require_once __DIR__ . '/../views/partials/header.php';
    require_once __DIR__ . '/../views/partials/navbar.php';
    require_once __DIR__ . '/../views/partials/sidebar.php';
    ?>
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <h1>جميع المواعيد</h1>
            </div>
        </section>
        <section class="content">
            <div class="card">
                <div class="card-header">
                    <form method="get" class="form-inline">
                        <input type="hidden" name="page" value="admin">
                        <input type="hidden" name="action" value="appointments"><!--نرسل القيم الى صفحة الادمين والى دالة الحجوزات-->
                        <div class="form-group mr-2">
                            <label class="mr-1">الدكتور:</label>
                            <select name="doctor_id" class="form-control">
                                <option value="">الكل</option>
                                <?php foreach ($doctors as $doc): ?>
                                    <option value="<?= $doc['id'] ?>" <?= ($filters['doctor_id'] ?? '') == $doc['id'] ? 'selected' : '' ?>><!--في حال كان الحيار المرسل سابقا في الرباط بيساوي القيمة الي في الفة ,يتم اختياره هذه يضمن ان يعرض في الفلاتر القيم المختارة في الفلترة السابقة  -->
                                        <?= htmlspecialchars($doc['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group mr-2">
                            <label class="mr-1">المريض:</label>
                            <input type="text" name="patient_name" class="form-control" placeholder="اسم المريض" value="<?= htmlspecialchars($filters['patient_name'] ?? '') ?>"><!--يعرض القيمة المخزنة سابقا -->
                        </div>
                        <div class="form-group mr-2">
                            <label class="mr-1">الحالة:</label>
                            <select name="status" class="form-control">
                                <option value="">الكل</option>
                                <option value="pending" <?= ($filters['status'] ?? '') == 'pending' ? 'selected' : '' ?>>معلق</option>
                                <option value="confirmed" <?= ($filters['status'] ?? '') == 'confirmed' ? 'selected' : '' ?>>مؤكد</option>
                                <option value="completed" <?= ($filters['status'] ?? '') == 'completed' ? 'selected' : '' ?>>مكتمل</option>
                                <option value="cancelled" <?= ($filters['status'] ?? '') == 'cancelled' ? 'selected' : '' ?>>ملغي</option>
                            </select>
                        </div>
                        <div class="form-group mr-2">
                            <label class="mr-1">من تاريخ:</label>
                            <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($filters['start_date'] ?? '') ?>">
                        </div>
                        <div class="form-group mr-2">
                            <label class="mr-1">إلى تاريخ:</label>
                            <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($filters['end_date'] ?? '') ?>">
                        </div>
                        <button type="submit" class="btn btn-primary">بحث</button>
                        <a href="index.php?page=admin&action=appointments" class="btn btn-secondary ml-2">إلغاء الفلتر</a>
                    </form>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr><th>ID</th><th>المريض</th><th>الطبيب</th><th>التاريخ</th><th>الوقت</th><th>الحالة</th><th>السبب</th><th>ملاحظات الطبيب</th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($appointments)): ?>
                                <tr><td colspan="8" class="text-center">لا توجد مواعيد</td></tr>
                            <?php else: ?>
                                <?php foreach ($appointments as $app): ?>
                                    <tr>
                                        <td><?= $app['id'] ?></td>
                                        <td><?= htmlspecialchars($app['patient_name']) ?></td>
                                        <td><?= htmlspecialchars($app['doctor_name']) ?></td>
                                        <td><?= $app['appt_date'] ?></td>
                                        <td><?= $app['appt_time'] ?></td>
                                        <td><span class="badge badge-<?= $this->statusBadge($app['status']) ?>"><?= $app['status'] ?></span></td>
                                        <td><?= htmlspecialchars($app['reason']) ?></td>
                                        <td><?= htmlspecialchars($app['doctor_notes'] ?? '') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <?= $this->renderPagination($paginator, $_GET) ?>
                </div>
            </div>
        </section>
    </div>
    <?php
    require_once __DIR__ . '/../views/partials/footer.php';
}
private function renderPagination($paginator, $params = []) {
    if ($paginator->totalPages() <= 1) return '';
    $query = http_build_query(array_merge($params, ['p' => null]));
    $html = '<nav><ul class="pagination justify-content-center">';
    if ($paginator->hasPrev()) $html .= '<li class="page-item"><a class="page-link" href="?p=' . $paginator->prevPage() . '&' . $query . '">السابق</a></li>';
    for ($i = 1; $i <= $paginator->totalPages(); $i++) {
        $active = ($i == $paginator->currentPage()) ? 'active' : '';
        $html .= '<li class="page-item ' . $active . '"><a class="page-link" href="?p=' . $i . '&' . $query . '">' . $i . '</a></li>';
    }
    if ($paginator->hasNext()) $html .= '<li class="page-item"><a class="page-link" href="?p=' . $paginator->nextPage() . '&' . $query . '">التالي</a></li>';
    $html .= '</ul></nav>';
    return $html;
}
//**************************************************التقرير***************************************** */

public function reports() {
    Auth::requireRole('admin');
    
    if (isset($_GET['export']) && $_GET['export'] == 'csv') {
        $this->exportCSV();
        return;
    }
    
    $pageTitle = "تقارير المواعيد";
    $currentPage = 'reports';
    require_once __DIR__ . '/../views/partials/header.php';
    require_once __DIR__ . '/../views/partials/navbar.php';
    require_once __DIR__ . '/../views/partials/sidebar.php';
    ?>
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <h1>تصدير تقرير المواعيد</h1>
            </div>
        </section>
        <section class="content">
            <div class="card">
                <div class="card-body">
                    <form method="get" action="index.php">
                        <input type="hidden" name="page" value="admin">
                        <input type="hidden" name="action" value="reports">
                        <input type="hidden" name="export" value="csv">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>من تاريخ</label>
                                    <input type="date" name="start_date" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>إلى تاريخ</label>
                                    <input type="date" name="end_date" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>الدكتور</label>
                                    <select name="doctor_id" class="form-control">
                                        <option value="">الكل</option>
                                        <?php
                                        $doctorModel = new DoctorModel();
                                        $doctors = $doctorModel->getAllForDropdown();
                                        foreach ($doctors as $doc): ?>
                                            <option value="<?= $doc['id'] ?>"><?= htmlspecialchars($doc['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>الحالة</label>
                                    <select name="status" class="form-control">
                                        <option value="">الكل</option>
                                        <option value="pending">قيد الانتظار</option>
                                        <option value="confirmed">مؤكد</option>
                                        <option value="completed">مكتمل</option>
                                        <option value="cancelled">ملغي</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">تصدير CSV</button>
                    </form>
                </div>
            </div>
        </section>
    </div>
    <?php
    require_once __DIR__ . '/../views/partials/footer.php';
}
private function exportCSV() {
    $filters = [];
    if (!empty($_GET['start_date'])) $filters['start_date'] = $_GET['start_date'];
    if (!empty($_GET['end_date'])) $filters['end_date'] = $_GET['end_date'];
    if (!empty($_GET['doctor_id'])) $filters['doctor_id'] = (int)$_GET['doctor_id'];
    if (!empty($_GET['status'])) $filters['status'] = $_GET['status'];
    
    // التحقق من وجود تاريخ البداية والنهاية
    if (empty($filters['start_date']) || empty($filters['end_date'])) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'الرجاء تحديد تاريخ البداية والنهاية'];
        header('Location: index.php?page=admin&action=reports');
        exit;
    }
    
    $appModel = new AppointmentModel();
    $appointments = $appModel->getAll(1, 10000, $filters); // نأخذ كل النتائج بدون بجينيشن
    
    
    header('Content-Type: text/csv; charset=utf-8');// CSVنخبر المتصفح ان  الملف المرسل هوا 
    header('Content-Disposition: attachment; filename="appointments_'.date('Y-m-d').'.csv"');// نحدد اسم الملف 
    
    $output = fopen('php://output', 'w');//تعني انه نريد ان نكتب على الملف W
    
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));// لدعم الغة العربية 
    // نكتب عناوين الاعمدة 
    fputcsv($output, ['ID', 'المريض', 'الطبيب', 'التاريخ', 'الوقت', 'الحالة', 'السبب', 'ملاحظات الطبيب']);
    // نعبي البيانات في الصفوف 
    foreach ($appointments as $app) {
        fputcsv($output, [
            $app['id'],
            $app['patient_name'],
            $app['doctor_name'],
            $app['appt_date'],
            $app['appt_time'],
            $app['status'],
            $app['reason'],
            $app['doctor_notes'] ?? ''
        ]);
    }
    
    fclose($output);// نغلقالملف 
    exit;
}


}