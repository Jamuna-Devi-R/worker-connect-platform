<?php
require 'config.php';
session_start();

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT users.*, worker_profiles.* FROM users JOIN worker_profiles ON users.id = worker_profiles.user_id WHERE users.id = ?");
$stmt->execute([$id]);
$worker = $stmt->fetch();

if(!$worker) {
    header('Location: index.php');
    exit;
}

$success = '';
if($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['user_type'] == 'client') {
    $description = $_POST['description'];
    $stmt2 = $pdo->prepare("INSERT INTO bookings (client_id, worker_id, description) VALUES (?,?,?)");
    $stmt2->execute([$_SESSION['user_id'], $id, $description]);
    $success = "Booking request sent successfully!";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $worker['name']; ?> - Worker Connect</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; }
        .navbar { background: #FF5722; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar h1 { color: white; font-size: 24px; }
        .navbar a { color: white; text-decoration: none; margin-left: 15px; padding: 8px 15px; border-radius: 5px; background: rgba(255,255,255,0.2); }
        .container { max-width: 900px; margin: 40px auto; padding: 0 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .profile-card { background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 30px; }
        .profile-card h2 { color: #333; margin-bottom: 15px; }
        .skill-badge { background: #fff3e0; color: #FF5722; padding: 5px 12px; border-radius: 20px; font-size: 14px; display: inline-block; margin-bottom: 15px; }
        .info-item { display: flex; align-items: center; margin: 10px 0; color: #666; }
        .info-item span { margin-left: 10px; }
        .rate { color: #FF5722; font-size: 28px; font-weight: bold; margin: 15px 0; }
        .description { background: #f9f9f9; padding: 15px; border-radius: 8px; color: #666; line-height: 1.6; margin-top: 15px; }
        .booking-card { background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 30px; }
        .booking-card h2 { color: #333; margin-bottom: 20px; }
        textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; height: 120px; font-size: 14px; resize: vertical; }
        button { width: 100%; padding: 15px; background: #FF5722; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; margin-top: 15px; }
        button:hover { background: #E64A19; }
        .success { background: #e8f5e9; color: #4CAF50; padding: 15px; border-radius: 5px; margin-bottom: 15px; text-align: center; }
        .back-btn { display: block; text-align: center; padding: 12px; background: #607D8B; color: white; text-decoration: none; border-radius: 5px; margin-top: 15px; }
        .available { color: #4CAF50; font-weight: bold; }
        .unavailable { color: #f44336; font-weight: bold; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>🔧 Worker Connect</h1>
        <div>
            <a href="index.php">Home</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
    <div class="container">
        <div class="profile-card">
            <h2>👤 <?php echo $worker['name']; ?></h2>
            <span class="skill-badge"><?php echo $worker['skill']; ?></span>
            <p class="<?php echo $worker['available'] == 'yes' ? 'available' : 'unavailable'; ?>">
                <?php echo $worker['available'] == 'yes' ? '✅ Available' : '❌ Not Available'; ?>
            </p>
            <div class="info-item">📍 <span><?php echo $worker['location']; ?></span></div>
            <div class="info-item">📞 <span><?php echo $worker['phone']; ?></span></div>
            <div class="info-item">⏳ <span>Experience: <?php echo $worker['experience']; ?></span></div>
            <div class="info-item">📧 <span><?php echo $worker['email']; ?></span></div>
            <p class="rate">₹<?php echo $worker['daily_rate']; ?>/day</p>
            <?php if($worker['description']): ?>
                <div class="description">
                    <strong>About:</strong><br>
                    <?php echo $worker['description']; ?>
                </div>
            <?php endif; ?>
            <a href="index.php" class="back-btn">← Back to Workers</a>
        </div>
        <div class="booking-card">
            <h2>📋 Book This Worker</h2>
            <?php if($success): ?>
                <div class="success">✅ <?php echo $success; ?></div>
            <?php endif; ?>
            <?php if($_SESSION['user_type'] == 'client'): ?>
                <?php if($worker['available'] == 'yes'): ?>
                    <form method="POST">
                        <label style="color:#666;display:block;margin-bottom:8px;">Describe your work requirement:</label>
                        <textarea name="description" placeholder="Ex: Need plumber to fix kitchen pipe leak at my home in Chennai..." required></textarea>
                        <button type="submit">📩 Send Booking Request</button>
                    </form>
                <?php else: ?>
                    <p style="color:#f44336;text-align:center;padding:20px;">❌ This worker is currently not available!</p>
                <?php endif; ?>
            <?php elseif($_SESSION['user_type'] == 'worker'): ?>
                <p style="color:#666;text-align:center;padding:20px;">👷 You are logged in as a worker. Only clients can book workers!</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>