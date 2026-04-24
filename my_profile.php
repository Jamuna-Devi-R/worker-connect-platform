<?php
require 'config.php';
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'worker') {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT users.*, worker_profiles.* FROM users JOIN worker_profiles ON users.id = worker_profiles.user_id WHERE users.id = ?");
$stmt->execute([$_SESSION['user_id']]);
$worker = $stmt->fetch();

$success = '';
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phone = $_POST['phone'];
    $location = $_POST['location'];
    $experience = $_POST['experience'];
    $daily_rate = $_POST['daily_rate'];
    $description = $_POST['description'];
    $available = $_POST['available'];

    $stmt1 = $pdo->prepare("UPDATE users SET phone=?, location=? WHERE id=?");
    $stmt1->execute([$phone, $location, $_SESSION['user_id']]);

    $stmt2 = $pdo->prepare("UPDATE worker_profiles SET experience=?, daily_rate=?, description=?, available=? WHERE user_id=?");
    $stmt2->execute([$experience, $daily_rate, $description, $available, $_SESSION['user_id']]);

    $success = "Profile updated successfully!";
    $worker['phone'] = $phone;
    $worker['location'] = $location;
    $worker['experience'] = $experience;
    $worker['daily_rate'] = $daily_rate;
    $worker['description'] = $description;
    $worker['available'] = $available;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Profile - Worker Connect</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; }
        .navbar { background: #FF5722; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar h1 { color: white; font-size: 24px; }
        .navbar a { color: white; text-decoration: none; margin-left: 15px; padding: 8px 15px; border-radius: 5px; background: rgba(255,255,255,0.2); }
        .container { max-width: 600px; margin: 40px auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { color: #333; margin-bottom: 25px; }
        label { color: #666; font-size: 14px; margin-top: 10px; display: block; }
        input, textarea, select { width: 100%; padding: 12px; margin: 8px 0; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }
        textarea { height: 100px; resize: vertical; }
        button { width: 100%; padding: 12px; background: #FF5722; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; margin-top: 15px; }
        button:hover { background: #E64A19; }
        .success { background: #e8f5e9; color: #4CAF50; padding: 15px; border-radius: 5px; margin-bottom: 15px; text-align: center; }
        .skill-badge { background: #fff3e0; color: #FF5722; padding: 5px 12px; border-radius: 20px; font-size: 14px; display: inline-block; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>🔧 Worker Connect</h1>
        <div>
            <a href="index.php">Home</a>
            <a href="my_bookings.php">Job Requests</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
    <div class="container">
        <h2>👤 My Profile</h2>
        <?php if($success): ?>
            <div class="success">✅ <?php echo $success; ?></div>
        <?php endif; ?>
        <p><strong><?php echo $worker['name']; ?></strong></p>
        <span class="skill-badge"><?php echo $worker['skill']; ?></span>
        <form method="POST">
            <label>Phone Number</label>
            <input type="text" name="phone" value="<?php echo $worker['phone']; ?>" required>
            <label>Location</label>
            <input type="text" name="location" value="<?php echo $worker['location']; ?>" required>
            <label>Experience</label>
            <input type="text" name="experience" value="<?php echo $worker['experience']; ?>">
            <label>Daily Rate (₹)</label>
            <input type="number" name="daily_rate" value="<?php echo $worker['daily_rate']; ?>">
            <label>About You</label>
            <textarea name="description"><?php echo $worker['description']; ?></textarea>
            <label>Availability</label>
            <select name="available">
                <option value="yes" <?php if($worker['available']=='yes') echo 'selected'; ?>>✅ Available</option>
                <option value="no" <?php if($worker['available']=='no') echo 'selected'; ?>>❌ Not Available</option>
            </select>
            <button type="submit">💾 Update Profile</button>
        </form>
    </div>
</body>
</html>