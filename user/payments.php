<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: ../login.php");
    exit();
}
include '../db_connect.php';

$uid = $_SESSION['user_id'];
$message = "";

// Fetch Settings
$settings = [];
$res = $conn->query("SELECT * FROM settings");
while ($row = $res->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$monthly_fee = $settings['monthly_fee'] ?? '30';
$yearly_fee = $settings['yearly_fee'] ?? '300';
$upi_id = $settings['upi_id'] ?? '';
$upi_name = $settings['upi_name'] ?? '';
$bank_name = $settings['bank_name'] ?? '';
$qr_mode = $settings['qr_mode'] ?? 'auto';

// Handle POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fee_type = $_POST['fee_type'];

    $amount = $monthly_fee;
    $month_year = date('Y-m'); // Default current

    if ($fee_type == 'yearly') {
        $amount = $yearly_fee;
        $month_year = date('Y');
    } elseif ($fee_type == 'advance') {
        $amount = $monthly_fee;
        $month_year = date('Y-m', strtotime('+1 month'));
        // Store as 'monthly' type but future date, or keep type 'advance'? 
        // Better to store as 'monthly' with future date for consistency in checks
        // But let's check if 'advance' is needed as a type. System workflow says "Fee type selector: ... Advance".
        // Use 'monthly' type but future date so it counts for that month.
        $fee_type = 'monthly';
    }

    // File Upload
    $target_dir = "../uploads/";
    if (!file_exists($target_dir))
        mkdir($target_dir, 0777, true);

    $target_file = $target_dir . "fee_" . $fee_type . "_" . $uid . "_" . time() . "_" . basename($_FILES["screenshot"]["name"]);

    // Check duplication
    $check_sql = "SELECT id FROM payments WHERE user_id=$uid AND type='$fee_type' AND month_year='$month_year'";
    $check = $conn->query($check_sql);

    if ($check->num_rows > 0) {
        $message = "You have already submitted a payment for this period ($month_year).";
    } else {
        if (move_uploaded_file($_FILES["screenshot"]["tmp_name"], $target_file)) {
            $db_path = "uploads/" . basename($target_file);
            $stmt = $conn->prepare("INSERT INTO payments (user_id, type, amount, month_year, screenshot_path, status) VALUES (?, ?, ?, ?, ?, 'pending')");
            $stmt->bind_param("issss", $uid, $fee_type, $amount, $month_year, $db_path);
            $stmt->execute();
            $message = "Payment submitted! Waiting for approval.";
        } else {
            $message = "Error uploading file.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Make Payment</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        /* Same Styles */
        .qr-container {
            text-align: center;
            background: #fff;
            padding: 10px;
            display: inline-block;
            border-radius: 10px;
            margin-bottom: 20px;
            animation: pulse-border 2s infinite;
        }

        @keyframes pulse-border {
            0% {
                box-shadow: 0 0 0 0 rgba(212, 255, 0, 0.4);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(212, 255, 0, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(212, 255, 0, 0);
            }
        }

        .waiting-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            background: var(--primary);
            border-radius: 50%;
            animation: pulse 1s infinite;
            margin-right: 5px;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.5);
                opacity: 0.5;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
    </style>
    <script>
        // Expose PHP vars to JS
        const monthlyFee = '<?php echo $monthly_fee; ?>';
        const yearlyFee = '<?php echo $yearly_fee; ?>';
        const upiId = '<?php echo $upi_id; ?>';
        const upiName = '<?php echo urlencode($upi_name); ?>';
        const qrMode = '<?php echo $qr_mode; ?>';

        function updateState() {
            let type = document.getElementById('fee_type').value;
            let amount = 0;
            let note = "";

            if (type === 'monthly') {
                amount = monthlyFee;
                note = "Monthly Fee";
            } else if (type === 'advance') {
                amount = monthlyFee; // Next month fee same as current
                note = "Advance Fee";
            } else if (type === 'yearly') {
                amount = yearlyFee;
                note = "Yearly Fee";
            }

            // Update Input
            document.getElementById('amount_display').value = '$' + amount;

            // Update QR if Auto mode
            if (qrMode === 'auto' && upiId) {
                // Construct UPI Link with Locked Amount (am)
                let upiLink = `upi://pay?pa=${upiId}&pn=${upiName}&am=${amount}&tn=${note}`;
                let qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(upiLink)}`;
                document.getElementById('qr_img').src = qrUrl;
            }
        }
    </script>
</head>

<body onload="updateState()">
    <?php include '../header.php'; ?>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        <div class="main-content">
            <h1>Make Payment</h1>

            <div class="grid-2">
                <div class="stat-card">
                    <h3>Payment Details</h3>

                    <?php if ($upi_id): ?>
                        <div class="text-center">
                            <div class="qr-container">
                                <?php if ($qr_mode == 'custom' && $settings['qr_image']): ?>
                                    <img src="../<?php echo $settings['qr_image']; ?>" alt="Payment QR" width="180">
                                    <p style="font-size:0.8rem; color:#666;">(Custom QR - Valid for any amount)</p>
                                <?php else: ?>
                                    <img id="qr_img" src="" alt="Generating Locked QR..." width="180">
                                <?php endif; ?>
                            </div>
                            <p style="color: var(--primary); font-weight: bold;">Scan to Pay</p>
                            <p style="font-size: 0.8rem; color: var(--text-gray);">Amount is LOCKED in QR code</p>
                        </div>
                    <?php else: ?>
                        <p style="color: var(--danger);">Admin has not configured UPI.</p>
                    <?php endif; ?>

                    <div
                        style="background: rgba(255,255,255,0.05); padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                        <p><strong>UPI ID:</strong> <?php echo $upi_id; ?></p>
                        <p><strong>Name:</strong> <?php echo $settings['upi_name']; ?></p>
                        <p><strong>Bank:</strong> <?php echo $bank_name; ?></p>
                    </div>

                    <form method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>Select Fee Type</label>
                            <select name="fee_type" id="fee_type" onchange="updateState()">
                                <option value="monthly">Monthly Fee (Current)</option>
                                <option value="advance">Advance (Next Month)</option>
                                <option value="yearly">Yearly Fee</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Amount (Locked)</label>
                            <input type="text" id="amount_display" readonly
                                style="color: var(--text-gray); cursor: not-allowed;">
                        </div>

                        <div class="form-group">
                            <label>Upload Payment Screenshot</label>
                            <input type="file" name="screenshot" accept="image/*,application/pdf" required>
                        </div>

                        <button type="submit" class="btn">Submit Payment</button>
                    </form>

                    <?php if ($message): ?>
                        <div
                            style="margin-top: 15px; padding: 10px; background: rgba(255,255,255,0.1); border-radius: 5px;">
                            <?php if (strpos($message, 'submitted') !== false): ?>
                                <span class="waiting-indicator"></span>
                            <?php endif; ?>
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>

                </div>

                <div class="stat-card">
                    <h3>History</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $hist = $conn->query("SELECT * FROM payments WHERE user_id=$uid ORDER BY created_at DESC");
                            while ($row = $hist->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                    <td><?php echo ucfirst($row['type']); ?> (<?php echo $row['month_year']; ?>)</td>
                                    <td><span
                                            class="badge badge-<?php echo strtolower($row['status']); ?>"><?php echo $row['status']; ?></span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>

</html>