<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../models/UserModel.php';

class AuthController {
    public function login() {
        if (Auth::check()) {// تفحص هل المستخدم سجل دخول Authدالة في كلاس ال 
            $this->redirectToDashboard();
            return;
        }
        $pageTitle = "تسجيل الدخول - ClinicDesk";
        require_once __DIR__ . '/../views/partials/header.php';
        ?>
        <div class="login-box"><!--كلاس بوتستراب يجعل الصندوق في وسط الصفحة -->
            <div class="card card-outline card-primary"><!--كلاس كارت يرسم كارت وبه حدود وملون بالوان اساسية رمادي وارزرق -->

                <div class="card-header text-center">
                    <h2>ClinicDesk</h2>
                </div>

                <div class="card-body">
                    <?php if (isset($_SESSION['flash'])): ?><!--يفحص هل يوجد عنصر خطا في الجلسة -->
                        <div class="alert alert-<?= $_SESSION['flash']['type'] ?>"><!-- يعني احمر danger النوع  redirectToLoginWithError يحدد لونه في دالة   alert- هوا كلاس بوتستراب ينشئ تنبيه بحوافة مستديرة و alert -->
                            <?= htmlspecialchars($_SESSION['flash']['message']) ?>
                        </div>
                        <?php unset($_SESSION['flash']); ?><!--نحذفها من الجلسة حتى لا تظهر مرة اخرى بعد تحديث الصفحة -->
                    <?php endif; ?>


                    <form action="index.php?page=auth&action=doLogin" method="post"><!--    التي تتحقق من تسجيل الدخول doLogin وننفذ دالة authcontrolar والاندكس يقراء اسم الكلاس ليعمل له اوبجيكت  index نرسل البيانات لل -->
                        <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
                        <div class="input-group mb-3">
                            <input type="email" name="email" class="form-control" placeholder="البريد الإلكتروني" required><!--required كلاس بوتستراب يقوم بتنسيق الحقل وجعل له حوا مستديرة وجعله  -->
                            <div class="input-group-append"><!--حقل نضع به الايقونة بجانب الخانة -->
                                <div class="input-group-text"><span class="fas fa-envelope"></span></div>
                            </div>

                        </div>
                        <div class="input-group mb-3">
                            <input type="password" name="password" class="form-control" placeholder="كلمة المرور" required>
                            <div class="input-group-append">
                                <div class="input-group-text"><span class="fas fa-lock"></span></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-block">تسجيل الدخول</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
        require_once __DIR__ . '/../views/partials/footer.php';//تضمين ال foteer 
    }

    public function doLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectToLoginWithError("طلب غير صالح");
            return;
        }
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            $this->redirectToLoginWithError("طلب غير مصرح به");
            return;
        }
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $password =isset($_POST['password']) ? $_POST['password']: '';
        if (empty($email) || empty($password)) {
            $this->redirectToLoginWithError("الرجاء إدخال البريد الإلكتروني وكلمة المرور");
            return;
        }
        $userModel = new UserModel();
        $user = $userModel->findByEmail($email);
        if (!$user || !password_verify($password, $user['password'])) {
            $this->redirectToLoginWithError("بيانات الدخول غير صحيحة");
            return;
        }
        if ($user['is_active'] != 1) {
            $this->redirectToLoginWithError("الحساب معطل، يرجى الاتصال بالمدير");
            return;
        }
        
        Auth::login($user);
        $this->redirectToDashboard();
    }

    public function logout() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectToLogin();
            return;
        }
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            $this->redirectToLoginWithError("طلب غير مصرح به");
            return;
        }
        Auth::logout();
        $this->redirectToLogin();
    }


    private function redirectToLoginWithError($message) {// ينشئ عنصر في الجلية يحمل النوع ورسالة الخطاء
        $_SESSION['flash'] = ['type' => 'danger', 'message' => $message];
        header('Location: index.php?page=auth&action=login');// ثم يعيده الى  الصفحة تسجيل الدخول  
        exit;
    }

    private function redirectToLogin() {
        header('Location: index.php?page=auth&action=login');
        exit;
    }

    private function redirectToDashboard() {
        $role = Auth::role();
        switch ($role) {
            case 'admin':
                header('Location: index.php?page=admin&action=dashboard');
                break;
            case 'doctor':
                header('Location: index.php?page=doctor&action=dashboard');
                break;
            case 'patient':
                header('Location: index.php?page=patient&action=dashboard');
                break;
            default:
                header('Location: index.php?page=auth&action=login');
        }
        exit;
    }
}