<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: ../login.php");
    exit();
}
include '../db_connect.php';

$uid = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date = $_POST['date'];
    $exercise = $_POST['exercise'];
    $sets = $_POST['sets'];
    $reps = $_POST['reps'];
    $notes = $_POST['notes'];

    $stmt = $conn->prepare("INSERT INTO workouts (user_id, date, exercise_name, sets, reps, notes) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issiis", $uid, $date, $exercise, $sets, $reps, $notes);
    $stmt->execute();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Workout Tracker</title>
    <link rel="stylesheet" href="../style.css">
</head>

<body>
    <?php include '../header.php'; ?>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        <div class="main-content">
            <h1>Workout Tracker</h1>

            <div class="grid-2">
                <div class="stat-card">
                    <h3>Log Workout</h3>
                    <form method="post">
                        <div class="form-group">
                            <label>Date</label>
                            <input type="date" name="date" required value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label>Exercise Name</label>
                            <input type="text" name="exercise" required placeholder="e.g. Bench Press">
                        </div>
                        <div class="grid-2">
                            <div class="form-group">
                                <label>Sets</label>
                                <input type="number" name="sets" required>
                            </div>
                            <div class="form-group">
                                <label>Reps</label>
                                <input type="number" name="reps" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Notes</label>
                            <textarea name="notes" rows="2"></textarea>
                        </div>
                        <button type="submit" class="btn">Log Workout</button>
                    </form>
                </div>

                <div class="stat-card">
                    <h3>Recent Logs</h3>
                    <div style="max-height: 400px; overflow-y: auto;">
                        <?php
                        $logs = $conn->query("SELECT * FROM workouts WHERE user_id=$uid ORDER BY date DESC, created_at DESC");
                        $current_date = "";
                        while ($row = $logs->fetch_assoc()):
                            if ($row['date'] != $current_date) {
                                echo "<h4 style='margin-top: 15px; border-bottom: 1px solid #333; padding-bottom: 5px; color: var(--primary);'>" . date('l, M d', strtotime($row['date'])) . "</h4>";
                                $current_date = $row['date'];
                            }
                            ?>
                            <div
                                style="padding: 10px; background: rgba(255,255,255,0.02); margin-top: 5px; border-radius: 5px;">
                                <div class="flex-between">
                                    <strong>
                                        <?php echo $row['exercise_name']; ?>
                                    </strong>
                                    <span style="font-size: 0.9rem; color: var(--text-gray);">
                                        <?php echo $row['sets']; ?> sets x
                                        <?php echo $row['reps']; ?> reps
                                    </span>
                                </div>
                                <?php if ($row['notes']): ?>
                                    <p style="font-size: 0.8rem; color: var(--text-gray); margin-top: 5px;">
                                        <?php echo $row['notes']; ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>