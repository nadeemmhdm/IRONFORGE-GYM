<?php
include 'header.php';
include 'db_connect.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password, role, status FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            if ($row['role'] == 'admin') {
                // Admin trying to login here? Allowed, redirect to admin.
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['role'] = 'admin';
                header("Location: admin/dashboard.php");
                exit();
            }

            // User Checks
            if ($row['status'] == 'active') {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['role'] = 'user';
                header("Location: user/dashboard.php");
                exit();
            } elseif ($row['status'] == 'pending') {
                $error = "Your account is pending approval by the admin.";
            } elseif ($row['status'] == 'blocked') {
                $error = "Your account has been blocked. Contact support.";
            } elseif ($row['status'] == 'archived') {
                $error = "Your account is archived due to inactivity. Contact admin.";
            } else {
                $error = "Access denied.";
            }
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <h2 class="text-center" style="margin-bottom: 20px; color: var(--primary);">Member Login</h2>

        <?php if ($error): ?>
            <div
                style="background: rgba(255, 77, 77, 0.2); color: #ff4d4d; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit" class="btn" style="width: 100%;">Login</button>

            <div class="text-center mt-20">
                <a href="register.php" style="font-size: 0.9rem; color: var(--text-gray);">New here? Register</a>
            </div>
        </form>
    </div>
</div>
</body>

</html>