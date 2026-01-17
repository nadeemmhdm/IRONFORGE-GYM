<?php
include 'header.php';
include 'db_connect.php';

// Fetch Settings
$settings = [];
$res = $conn->query("SELECT * FROM settings");
while ($row = $res->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$reg_fee = $settings['registration_fee'] ?? "500";
$upi_id = $settings['upi_id'] ?? "";
$upi_name = $settings['upi_name'] ?? "";

// Generate QR Link (Locked Amount)
$qr_url = "";
if ($upi_id) {
    // am parameter locks amount in UPI apps
    $upi_link = "upi://pay?pa=$upi_id&pn=" . urlencode($upi_name) . "&am=$reg_fee&tn=Registration Fee";
    $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($upi_link);
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $address = $_POST['address'];
    $gender = $_POST['gender'];
    $age = $_POST['age'];
    $membership_type = $_POST['membership_type'];

    // File Upload
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $target_file = $target_dir . basename($_FILES["payment_screenshot"]["name"]);

    // Check if user exists
    $check = $conn->query("SELECT email FROM users WHERE email='$email'");
    if ($check->num_rows > 0) {
        $error = "Email already registered.";
    } else {
        if (move_uploaded_file($_FILES["payment_screenshot"]["tmp_name"], $target_file)) {
            // Insert User
            $stmt = $conn->prepare("INSERT INTO users (full_name, phone, email, password, address, gender, age, membership_type, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->bind_param("ssssssis", $full_name, $phone, $email, $password, $address, $gender, $age, $membership_type);

            if ($stmt->execute()) {
                $user_id = $stmt->insert_id;
                // Insert Payment Record
                $p_stmt = $conn->prepare("INSERT INTO payments (user_id, type, amount, screenshot_path, status) VALUES (?, 'registration', ?, ?, 'pending')");
                $p_stmt->bind_param("ids", $user_id, $reg_fee, $target_file);
                $p_stmt->execute();

                $success = "Registration successful! Wait for admin approval.";
            } else {
                $error = "Error: " . $stmt->error;
            }
        } else {
            $error = "Error uploading payment screenshot.";
        }
    }
}
?>

<div class="auth-wrapper">
    <div class="auth-card" style="max-width: 600px;">
        <h2 class="text-center" style="margin-bottom: 20px; color: var(--primary);">Join IronForge</h2>

        <?php if ($error): ?>
            <div
                style="background: rgba(255, 77, 77, 0.2); color: #ff4d4d; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div
                style="background: rgba(0, 255, 136, 0.2); color: #00ff88; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $success; ?> <br> <a href="login.php" style="color: var(--primary);">Login here</a>
            </div>
        <?php else: ?>

            <form method="post" enctype="multipart/form-data">
                <div class="grid-2">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" required>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone" required>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label>Age</label>
                        <input type="number" name="age" required>
                    </div>
                    <div class="form-group">
                        <label>Gender</label>
                        <select name="gender">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>

                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" rows="2" required></textarea>
                </div>

                <div class="form-group">
                    <label>Membership Plan</label>
                    <select name="membership_type">
                        <option value="Monthly">Monthly</option>
                        <option value="Yearly">Yearly</option>
                    </select>
                </div>

                <div class="form-group"
                    style="background: rgba(255,255,255,0.05); padding: 20px; border-radius: 10px; text-align: center;">
                    <h3 style="color: var(--primary); margin-bottom: 10px;">Registration Fee: $<?php echo $reg_fee; ?></h3>

                    <?php if ($qr_url): ?>
                        <div
                            style="background: #fff; padding: 10px; display: inline-block; border-radius: 10px; margin-bottom: 10px; border: 2px solid var(--primary);">
                            <img src="<?php echo $qr_url; ?>" alt="Scan to Pay" width="150" height="150"
                                style="display: block;">
                        </div>
                        <p style="font-size: 0.9rem; color: var(--text-gray);">Scan to pay (Amount is Locked)</p>
                        <p style="font-size: 0.8rem; margin-top:5px; font-family: monospace;">UPI: <?php echo $upi_id; ?></p>
                    <?php else: ?>
                        <p style="color: var(--danger);">Payment Gateway Error: Admin has not configured UPI.</p>
                    <?php endif; ?>

                    <div style="text-align: left; margin-top: 20px; border-top: 1px solid #333; padding-top: 15px;">
                        <label>Upload Payment Screenshot</label>
                        <input type="file" name="payment_screenshot" accept="image/*,application/pdf" required>
                    </div>
                </div>

                <div class="form-group">
                    <input type="checkbox" required id="terms"> <label for="terms" style="display:inline;">I agree to the
                        Terms & Conditions</label>
                </div>

                <button type="submit" class="btn" style="width: 100%;">Register & Pay</button>
            </form>
        <?php endif; ?>
    </div>
</div>
</body>

</html>