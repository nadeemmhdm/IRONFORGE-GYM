<div class="sidebar">
    <h3 style="color: var(--primary); margin-bottom: 30px; padding-left: 10px;">ADMIN PANEL</h3>
    <ul>
        <li><a href="dashboard.php"
                class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
        </li>
        <li><a href="users.php"
                class="<?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">User Management</a>
        </li>
        <li><a href="approve_payments.php"
                class="<?php echo basename($_SERVER['PHP_SELF']) == 'approve_payments.php' ? 'active' : ''; ?>">Fee
                Approvals</a></li>
        <li><a href="monthly_fees.php"
                class="<?php echo basename($_SERVER['PHP_SELF']) == 'monthly_fees.php' ? 'active' : ''; ?>">Monthly
                Fees</a></li>
        <li><a href="fee_settings.php"
                class="<?php echo basename($_SERVER['PHP_SELF']) == 'fee_settings.php' ? 'active' : ''; ?>">Fee
                Settings</a></li>
        <li style="margin-top: 50px;"><a href="../logout.php" class="btn-danger text-center"
                style="color: white; padding: 10px;">Logout</a></li>
    </ul>
</div>