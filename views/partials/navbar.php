<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- زر الهامبرغر لطي القائمة الجانبية -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
    </ul>

    <!-- القائمة التي تدفع المحتوى لليمين -->
    <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['user']['name'] ?? 'Guest') ?>
            </a>
            <div class="dropdown-menu dropdown-menu-right" style="right: 0; left: auto; z-index: 1050;">
                <a href="index.php?page=profile&action=edit" class="dropdown-item">Profile</a>
                <div class="dropdown-divider"></div>
                <form action="index.php?page=auth&action=logout" method="POST" id="logout-form" style="display: none;">
                    <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
                </form>
                <a href="#" class="dropdown-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </li>
    </ul>
</nav>