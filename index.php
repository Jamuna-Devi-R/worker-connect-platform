<?php
require 'config.php';
session_start();

$search = isset($_GET['search']) ? $_GET['search'] : '';
$skill = isset($_GET['skill']) ? $_GET['skill'] : '';

$query = "SELECT users.*, worker_profiles.* FROM users JOIN worker_profiles ON users.id = worker_profiles.user_id WHERE users.user_type = 'worker' AND worker_profiles.available = 'yes'";

if($search) {
    $query .= " AND (users.name LIKE '%$search%' OR users.location LIKE '%$search%')";
}
if($skill) {
    $query .= " AND worker_profiles.skill = '$skill'";
}

$query .= " ORDER BY users.created_at DESC";
$workers = $pdo->query($query)->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Worker Connect - Find Workers</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; }
        .navbar { background: #FF5722; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar h1 { color: white; font-size: 24px; }
        .navbar a { color: white; text-decoration: none; margin-left: 15px; padding: 8px 15px; border-radius: 5px; background: rgba(255,255,255,0.2); }
        .navbar a:hover { background: rgba(255,255,255,0.3); }
        .search-bar { padding: 20px 30px; background: white; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .search-bar form { display: flex; gap: 10px; }
        .search-bar input { flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .search-bar select { padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .search-bar button { padding: 10px 20px; background: #FF5722; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .workers { padding: 30px; display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .worker-card { background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 20px; }
        .worker-card h3 { color: #333; margin-bottom: 8px; }
        .skill-badge { background: #fff3e0; color: #FF5722; padding: 4px 10px; border-radius: 20px; font-size: 13px; display: inline-block; margin-bottom: 10px; }
        .worker-info p { color: #666; font-size: 14px; margin: 5px 0; }
        .rate { color: #FF5722; font-size: 18px; font-weight: bold; margin: 10px 0; }
        .view-btn { display: block; text-align: center; padding: 10px; background: #FF5722; color: white; text-decoration: none; border-radius: 5px; margin-top: 15px; }
        .view-btn:hover { background: #E64A19; }
        .no-workers { text-align: center; padding: 50px; color: #666; font-size: 18px; }
        .hero { background: linear-gradient(135deg, #FF5722, #FF8A65); padding: 60px 30px; text-align: center; color: white; }
        .hero h2 { font-size: 36px; margin-bottom: 15px; }
        .hero p { font-size: 18px; opacity: 0.9; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>🔧 Worker Connect</h1>
        <div>
            <?php if(isset($_SESSION['user_id'])): ?>
                <?php if($_SESSION['user_type'] == 'client'): ?>
                    <a href="my_bookings.php">My Bookings</a>
                <?php else: ?>
                    <a href="my_profile.php">My Profile</a>
                    <a href="my_bookings.php">Job Requests</a>
                <?php endif; ?>
                <a href="logout.php">Logout (<?php echo $_SESSION['user_name']; ?>)</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if(!isset($_SESSION['user_id'])): ?>
    <div class="hero">
        <h2>Find Skilled Workers Near You! 🛠️</h2>
        <p>Connect with verified plumbers, electricians, carpenters and more!</p>
    </div>
    <?php endif; ?>

    <div class="search-bar">
        <form method="GET">
            <input type="text" name="search" placeholder="Search by name or location..." value="<?php echo $search; ?>">
            <select name="skill">
                <option value="">All Skills</option>
                <option value="Plumber" <?php if($skill=='Plumber') echo 'selected'; ?>>🔧 Plumber</option>
                <option value="Electrician" <?php if($skill=='Electrician') echo 'selected'; ?>>⚡ Electrician</option>
                <option value="Carpenter" <?php if($skill=='Carpenter') echo 'selected'; ?>>🪚 Carpenter</option>
                <option value="Painter" <?php if($skill=='Painter') echo 'selected'; ?>>🎨 Painter</option>
                <option value="Cleaner" <?php if($skill=='Cleaner') echo 'selected'; ?>>🧹 Cleaner</option>
                <option value="Mason" <?php if($skill=='Mason') echo 'selected'; ?>>🧱 Mason</option>
                <option value="Driver" <?php if($skill=='Driver') echo 'selected'; ?>>🚗 Driver</option>
                <option value="Cook" <?php if($skill=='Cook') echo 'selected'; ?>>👨‍🍳 Cook</option>
                <option value="Security Guard" <?php if($skill=='Security Guard') echo 'selected'; ?>>🛡️ Security Guard</option>
                <option value="Others" <?php if($skill=='Others') echo 'selected'; ?>>Others</option>
            </select>
            <button type="submit">🔍 Search</button>
        </form>
    </div>

    <div class="workers">
        <?php if(count($workers) > 0): ?>
            <?php foreach($workers as $worker): ?>
                <div class="worker-card">
                    <h3>👤 <?php echo $worker['name']; ?></h3>
                    <span class="skill-badge"><?php echo $worker['skill']; ?></span>
                    <div class="worker-info">
                        <p>📍 <?php echo $worker['location']; ?></p>
                        <p>⏳ Experience: <?php echo $worker['experience']; ?></p>
                        <p>📞 <?php echo $worker['phone']; ?></p>
                    </div>
                    <p class="rate">₹<?php echo $worker['daily_rate']; ?>/day</p>
                    <a href="<?php echo isset($_SESSION['user_id']) ? 'worker.php?id='.$worker['user_id'] : 'login.php'; ?>" class="view-btn">View Profile & Book</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-workers">
                <p>No workers found! Try different search. 🔍</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>