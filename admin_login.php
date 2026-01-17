<?php
include 'header.php';
include 'db_connect.php';

$error = "";

// Simple Captcha Generation
if (!isset($_SESSION['captcha_result'])) {
    $num1 = rand(1, 10);
    $num2 = rand(1, 10);
    $_SESSION['captcha_result'] = $num1 + $num2;
    $_SESSION['captcha_text'] = "$num1 + $num2";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $captcha = $_POST['captcha'];

    if (intval($captcha) !== $_SESSION['captcha_result']) {
        $error = "Incorrect Captcha.";
    } else {
        $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE email = ? AND role = 'admin'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['role'] = 'admin';
                // Reset captcha
                unset($_SESSION['captcha_result']);
                header("Location: admin/dashboard.php");
                exit();
            } else {
                $error = "Invalid credentials.";
            }
        } else {
            $error = "Admin not found.";
        }
    }

    // Regenerate Captcha on failure
    $num1 = rand(1, 10);
    $num2 = rand(1, 10);
    $_SESSION['captcha_result'] = $num1 + $num2;
    $_SESSION['captcha_text'] = "$num1 + $num2";
}
?>

<div class="auth-wrapper">
    <div class="auth-card" style="border-color: var(--primary);">
        <h2 class="text-center" style="margin-bottom: 20px; color: var(--primary);">Admin Portal</h2>

        <?php if ($error): ?>
            <div
                style="background: rgba(255, 77, 77, 0.2); color: #ff4d4d; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label>Admin Email</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <div class="form-group">
                <label>Security Check:
                    <?php echo $_SESSION['captcha_text']; ?> = ?
                </label>
                <input type="number" name="captcha" required style="width: 100px;">
            </div>

            <button type="submit" class="btn" style="width: 100%;">Secure Login</button>
        </form>
    </div>
</div>
</body>

</html>