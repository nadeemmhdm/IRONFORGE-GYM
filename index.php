<?php include 'header.php'; ?>

<section class="hero">
    <div class="container">
        <h1>Forge Your Best Self</h1>
        <p>Join the elite community of fitness enthusiasts. Track your progress, manage your payments, and achieve your
            goals.</p>
        <div class="hero-buttons">
            <a href="register.php" class="btn">Join Now</a>
            <a href="login.php" class="btn btn-outline">Member Login</a>
        </div>
    </div>
</section>

<section class="container mt-20 mb-20">
    <div class="grid-2">
        <div class="stat-card">
            <h3>State of the Art</h3>
            <p>Experience the latest in gym equipment and fitness technology.</p>
        </div>
        <div class="stat-card">
            <h3>Track Progress</h3>
            <p>Log your daily workouts and visualize your fitness journey.</p>
        </div>
        <div class="stat-card">
            <h3>Flexible Plans</h3>
            <p>Monthly, Quarterly, and Yearly plans tailored to your needs.</p>
        </div>
        <div class="stat-card">
            <h3>Community</h3>
            <p>Join a supportive environment that pushes you to be better.</p>
        </div>
    </div>
</section>

<?php
include_once 'db_connect.php';
$reg_fee_res = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'registration_fee'");
$reg_fee = ($reg_fee_res->num_rows > 0) ? $reg_fee_res->fetch_assoc()['setting_value'] : "50.00";

$monthly_fee_res = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'monthly_fee'");
$monthly_fee = ($monthly_fee_res->num_rows > 0) ? $monthly_fee_res->fetch_assoc()['setting_value'] : "30.00";
?>

<section class="container mb-20">
    <h2 class="text-center mb-20">Membership Plans</h2>
    <div class="grid-2">
        <!-- Dynamic content could go here, for now static as per prompt doesn't ask for dynamic plans -->
        <div class="stat-card text-center">
            <h4>Registration Fee</h4>
            <h3 style="color: var(--primary);">$<?php echo $reg_fee; ?></h3>
            <p>One time payment</p>
        </div>
        <div class="stat-card text-center">
            <h4>Monthly Fee</h4>
            <h3 style="color: var(--primary);">$<?php echo $monthly_fee; ?></h3>
            <p> billed monthly</p>
        </div>
    </div>
</section>

<section class="container mb-20">
    <div class="stat-card text-center" style="max-width: 600px; margin: 0 auto; border-color: var(--primary);">
        <h2 style="color: var(--primary); margin-bottom: 20px;">Opening Hours</h2>
        <div class="flex-between" style="border-bottom: 1px solid #333; padding: 10px 0;">
            <span>Monday - Friday</span>
            <span>5:00 AM - 11:00 PM</span>
        </div>
        <div class="flex-between" style="border-bottom: 1px solid #333; padding: 10px 0;">
            <span>Saturday</span>
            <span>6:00 AM - 10:00 PM</span>
        </div>
        <div class="flex-between" style="padding: 10px 0;">
            <span>Sunday</span>
            <span>6:00 AM - 2:00 PM</span>
        </div>
    </div>
</section>

<footer class="text-center"
    style="padding: 40px 0; color: var(--text-gray); border-top: 1px solid var(--glass-border);">
    <p>&copy; 2024 IronForge Gym. All rights reserved.</p>
</footer>

</body>

</html>