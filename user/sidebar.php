<div class="sidebar">
    <h3 style="color: var(--primary); margin-bottom: 30px; padding-left: 10px;">USER PANEL</h3>
    <ul>
        <li><a href="dashboard.php"
                class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
        </li>
        <li><a href="payments.php"
                class="<?php echo basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : ''; ?>">Fee Payments</a>
        </li>
        <li><a href="workout.php"
                class="<?php echo basename($_SERVER['PHP_SELF']) == 'workout.php' ? 'active' : ''; ?>">Workout
                Tracking</a></li>
        <li style="margin-top: 50px;"><a href="../logout.php" class="btn-danger text-center"
                style="color: white; padding: 10px;">Logout</a></li>
    </ul>
</div>