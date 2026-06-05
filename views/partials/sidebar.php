<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="index.php?page=dashboard" class="brand-link">
        <span class="brand-text font-weight-light">ClinicDesk</span>
    </a>
<div class="sidebar">
    <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
            <li class="nav-item">
                <a href="index.php?page=admin&action=dashboard" class="nav-link <?php echo ($currentPage == 'dashboard') ? 'active' : ''; ?>">
                    <i class="nav-icon fas fa-tachometer-alt"></i>
                    <p>Dashboard</p>
                </a>
            </li>
            <?php if (Auth::role() == 'admin'): ?>
            <li class="nav-item">
                <a href="index.php?page=admin&action=users" class="nav-link <?php echo ($currentPage == 'users') ? 'active' : ''; ?>">
                    <i class="nav-icon fas fa-users"></i>
                    <p>Users</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="index.php?page=admin&action=doctors" class="nav-link <?php echo ($currentPage == 'doctors') ? 'active' : ''; ?>">
                    <i class="nav-icon fas fa-user-md"></i>
                    <p>Doctors</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="index.php?page=admin&action=specializations" class="nav-link <?php echo ($currentPage == 'specializations') ? 'active' : ''; ?>">
                    <i class="nav-icon fas fa-stethoscope"></i>
                    <p>Specializations</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="index.php?page=admin&action=appointments" class="nav-link <?php echo ($currentPage == 'appointments') ? 'active' : ''; ?>">
                    <i class="nav-icon fas fa-calendar-alt"></i>
                    <p>Appointments</p>
                </a>
            </li>
            <?php endif; ?>
                <?php if (Auth::role() == 'patient'): ?>
                <li class="nav-item">
    <a href="index.php?page=patient&action=prescriptions" class="nav-link">
        <i class="nav-icon fas fa-file-prescription"></i>
        <p>My Prescriptions</p>
    </a>
</li>
                <li class="nav-item"><a href="index.php?page=patient&action=bookForm" class="nav-link">حجز موعد</a></li>
                <li class="nav-item"><a href="index.php?page=patient&action=myAppointments" class="nav-link">مواعيدي</a></li>
                <?php endif; ?>
                <?php if (Auth::role() == 'doctor'): ?>
                    <li class="nav-item"><a href="index.php?page=doctor&action=dashboard" class="nav-link">Dashboard</a></li>
                     <li class="nav-item"><a href="index.php?page=doctor&action=schedule" class="nav-link">جدول المواعيد</a></li>
                 <?php endif; ?>

                <!--التقارير -->
<?php if (Auth::role() == 'admin'): ?>
    <li class="nav-item">
        <a href="index.php?page=admin&action=reports" class="nav-link">
            <i class="nav-icon fas fa-chart-line"></i>
            <p>التقارير</p>
        </a>
    </li>
<?php endif; ?>
            </ul>
        </nav>
    </div>
</aside>