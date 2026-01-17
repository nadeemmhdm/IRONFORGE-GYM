<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../admin_login.php");
    exit();
}
include '../db_connect.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reg_fee = $_POST['registration_fee'];
    $monthly_fee = $_POST['monthly_fee'];
    $yearly_fee = $_POST['yearly_fee'];
    $enabled = isset($_POST['registration_enabled']) ? '1' : '0';

    $upi_id = $_POST['upi_id'];
    $upi_name = $_POST['upi_name'];
    $bank_name = $_POST['bank_name'];
    $qr_mode = $_POST['qr_mode'];

    // Handle QR Upload
    if ($qr_mode == 'custom' && isset($_FILES['qr_image']) && $_FILES['qr_image']['size'] > 0) {
        $target_dir = "../uploads/";
        if (!file_exists($target_dir))
            mkdir($target_dir, 0777, true);
        $target_file = $target_dir . "qr_code_" . time() . ".png";
        if (move_uploaded_file($_FILES['qr_image']['tmp_name'], $target_file)) {
            $qr_path = "uploads/" . basename($target_file);
            $conn->query("INSERT INTO settings (setting_key, setting_value) VALUES ('qr_image', '$qr_path') ON DUPLICATE KEY UPDATE setting_value='$qr_path'");
        }
    }

    $queries = [
        "INSERT INTO settings (setting_key, setting_value) VALUES ('registration_fee', '$reg_fee') ON DUPLICATE KEY UPDATE setting_value='$reg_fee'",
        "INSERT INTO settings (setting_key, setting_value) VALUES ('monthly_fee', '$monthly_fee') ON DUPLICATE KEY UPDATE setting_value='$monthly_fee'",
        "INSERT INTO settings (setting_key, setting_value) VALUES ('yearly_fee', '$yearly_fee') ON DUPLICATE KEY UPDATE setting_value='$yearly_fee'",
        "INSERT INTO settings (setting_key, setting_value) VALUES ('registration_enabled', '$enabled') ON DUPLICATE KEY UPDATE setting_value='$enabled'",
        "INSERT INTO settings (setting_key, setting_value) VALUES ('upi_id', '$upi_id') ON DUPLICATE KEY UPDATE setting_value='$upi_id'",
        "INSERT INTO settings (setting_key, setting_value) VALUES ('upi_name', '$upi_name') ON DUPLICATE KEY UPDATE setting_value='$upi_name'",
        "INSERT INTO settings (setting_key, setting_value) VALUES ('bank_name', '$bank_name') ON DUPLICATE KEY UPDATE setting_value='$bank_name'",
        "INSERT INTO settings (setting_key, setting_value) VALUES ('qr_mode', '$qr_mode') ON DUPLICATE KEY UPDATE setting_value='$qr_mode'"
    ];

    foreach ($queries as $q) {
        $conn->query($q);
    }

    $message = "Settings updated successfully.";
}

// Fetch Settings
$settings = [];
$res = $conn->query("SELECT * FROM settings");
while ($row = $res->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Defaults
$s_reg = $settings['registration_fee'] ?? '500';
$s_month = $settings['monthly_fee'] ?? '30';
$s_year = $settings['yearly_fee'] ?? '300';
$s_enabled = $settings['registration_enabled'] ?? '1';
$s_upi = $settings['upi_id'] ?? '';
$s_upi_name = $settings['upi_name'] ?? '';
$s_bank = $settings['bank_name'] ?? '';
$s_qr_mode = $settings['qr_mode'] ?? 'auto';
$s_qr_img = $settings['qr_image'] ?? '';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Fee & Payment Settings</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .tab-btn {
            background: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
            padding: 10px 20px;
            margin-right: 10px;
            cursor: pointer;
        }

        .tab-btn.active {
            background: var(--primary);
            color: #000;
        }

        .tab-content {
            display: none;
            padding: 20px;
            border: 1px solid var(--glass-border);
            margin-top: 20px;
            border-radius: 5px;
            background: var(--card-bg);
        }

        .tab-content.active {
            display: block;
        }
    </style>
    <script>
        function openTab(name) {
            document.querySelectorAll('.tab-content').forEach(x => x.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(x => x.classList.remove('active'));
            document.getElementById(name).classList.add('active');
            document.getElementById(name + '-btn').classList.add('active');
        }
    </script>
</head>

<body>
    <?php include '../header.php'; ?>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        <div class="main-content">
            <h1>Fee & Payment Settings</h1>

            <?php if ($message): ?>
                <div
                    style="background: rgba(0,255,136,0.2); color: #00ff88; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div style="margin-bottom: 20px;">
                <button id="fees-btn" class="tab-btn active" onclick="openTab('fees')">Fee Structure</button>
                <button id="payment-btn" class="tab-btn" onclick="openTab('payment')">UPI & QR Settings</button>
            </div>

            <form method="post" enctype="multipart/form-data">

                <!-- Fees Tab -->
                <div id="fees" class="tab-content active">
                    <h3 style="color: var(--primary); margin-bottom: 20px;">Fee Structure</h3>
                    <div class="form-group">
                        <label>Registration Fee Amount ($)</label>
                        <input type="number" name="registration_fee" value="<?php echo $s_reg; ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Monthly Fee Amount ($)</label>
                        <input type="number" name="monthly_fee" value="<?php echo $s_month; ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Yearly Fee Amount ($)</label>
                        <input type="number" name="yearly_fee" value="<?php echo $s_year; ?>" required>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="registration_enabled" <?php echo ($s_enabled == '1') ? 'checked' : ''; ?>>
                            Enable New Registrations
                        </label>
                    </div>
                </div>

                <!-- Payment Tab -->
                <div id="payment" class="tab-content">
                    <h3 style="color: var(--primary); margin-bottom: 20px;">Payment Gateway (UPI/QR)</h3>

                    <div class="form-group">
                        <label>UPI ID</label>
                        <input type="text" name="upi_id" value="<?php echo $s_upi; ?>" placeholder="e.g. business@upi">
                    </div>

                    <div class="form-group">
                        <label>UPI Name / Merchant Name</label>
                        <input type="text" name="upi_name" value="<?php echo $s_upi_name; ?>"
                            placeholder="e.g. IronForge Gym">
                    </div>

                    <div class="form-group">
                        <label>Bank Name</label>
                        <input type="text" name="bank_name" value="<?php echo $s_bank; ?>" placeholder="e.g. HDFC Bank">
                    </div>

                    <hr style="border: 0; border-top: 1px solid var(--glass-border); margin: 20px 0;">

                    <label style="margin-bottom: 15px; display: block;">QR Code Mode</label>
                    <div style="margin-bottom: 20px;">
                        <label style="margin-right: 20px;">
                            <input type="radio" name="qr_mode" value="auto" <?php echo ($s_qr_mode == 'auto') ? 'checked' : ''; ?>>
                            System Generated (from UPI ID)
                        </label>
                        <label>
                            <input type="radio" name="qr_mode" value="custom" <?php echo ($s_qr_mode == 'custom') ? 'checked' : ''; ?>>
                            Upload Custom QR Image
                        </label>
                    </div>

                    <div class="form-group">
                        <label>Upload Custom QR (If Custom selected)</label>
                        <input type="file" name="qr_image" accept="image/*">
                        <?php if ($s_qr_img): ?>
                            <p style="margin-top: 10px;">Current Custom QR: <a href="../<?php echo $s_qr_img; ?>"
                                    target="_blank">View</a></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="margin-top: 20px;">
                    <button type="submit" class="btn">Save All Settings</button>
                </div>

            </form>
        </div>
    </div>
</body>

</html>