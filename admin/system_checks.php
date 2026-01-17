<?php
// Auto Archive Logic
// Intended to be included in admin dashboard

$current_date = new DateTime();
$two_months_ago = (clone $current_date)->modify('-2 months')->format('Y-m');

// Find active users with no payment in last 2 months
// Get all active users
$users = $conn->query("SELECT id, created_at FROM users WHERE status='active' AND role='user'");

while ($u = $users->fetch_assoc()) {
    $uid = $u['id'];

    // Get last approved monthly payment
    $last_pay = $conn->query("SELECT MAX(month_year) as last_month FROM payments WHERE user_id=$uid AND type='monthly' AND status='approved'");
    $last_month = $last_pay->fetch_assoc()['last_month'];

    $should_archive = false;

    if ($last_month) {
        // Compare last paid month with 2 months ago
        // If last_month < two_months_ago
        if ($last_month < $two_months_ago) {
            $should_archive = true;
        }
    } else {
        // No monthly payments. Check registration date.
        // If created_at < 2 months ago
        $reg_date = new DateTime($u['created_at']);
        $diff = $current_date->diff($reg_date);
        if ($diff->m >= 2 || $diff->y > 0) {
            $should_archive = true;
        }
    }

    if ($should_archive) {
        $conn->query("UPDATE users SET status='archived' WHERE id=$uid");
    }
}
?>