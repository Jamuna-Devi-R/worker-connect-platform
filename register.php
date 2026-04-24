<?php
require 'config.php';
session_start();

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $user_type = $_POST['user_type'];
    $phone = $_POST['phone'];
    $location = $_POST['location'];

    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, user_type, phone, location) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password, $user_type, $phone, $location]);
        $user_id = $pdo->lastInsertId();

        if($user_type == 'worker') {
            $skill = $_POST['skill'];
            $experience = $_POST['experience'];
            $description = $_POST['description'];
            $daily_rate = $_POST['daily_rate'];
            $stmt2 = $pdo->prepare("INSERT INTO worker_profiles (user_id, skill, experience, description, daily_rate) VALUES (?, ?, ?, ?, ?)");
            $stmt2->execute([$user_id, $skill, $experience, $description, $daily_rate]);
        }
        header('Location: login.php');
    } catch(PDOException $e) {
        $error = "Email already exists!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - Worker Connect</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .container { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 450px; }
        h2 { text-align: center; color: #333; margin-bottom: 20px; }
        input, select, textarea { width: 100%; padding: 12px; margin: 8px 0; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }
        textarea { height: 80px; }
        button { width: 100%; padding: 12px; background: #FF5722; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; margin-top: 10px; }
        button:hover { background: #E64A19; }
        .login-link { text-align: center; margin-top: 15px; }
        .login-link a { color: #FF5722; text-decoration: none; }
        .error { color: red; text-align: center; margin-bottom: 10px; }
        .worker-fields { display: none; }
        label { color: #666; font-size: 13px; margin-top: 5px; display: block; }
        h3 { color: #FF5722; margin: 15px 0 5px; }
    </style>
    <script>
        function toggleWorkerFields() {
            var type = document.getElementById('user_type').value;
            var workerFields = document.getElementById('worker_fields');
            workerFields.style.display = type == 'worker' ? 'block' : 'none';
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>🔧 Worker Connect</h2>
        <h3 style="text-align:center;color:#666;margin-bottom:20px;">Create Account</h3>
        <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <label>I am a:</label>
            <select name="user_type" id="user_type" onchange="toggleWorkerFields()" required>
                <option value="">Select Type</option>
                <option value="client">Client (Looking for workers)</option>
                <option value="worker">Worker (Offering services)</option>
            </select>
            <label>Full Name</label>
            <input type="text" name="name" placeholder="Full Name" required>
            <label>Email</label>
            <input type="email" name="email" placeholder="Email Address" required>
            <label>Password</label>
            <input type="password" name="password" placeholder="Password" required>
            <label>Phone Number</label>
            <input type="text" name="phone" placeholder="Phone Number" required>
            <label>Location</label>
            <input type="text" name="location" placeholder="City / Area" required>

            <div class="worker-fields" id="worker_fields">
                <h3>💼 Worker Details</h3>
                <label>Skill / Profession</label>
                <select name="skill">
                    <option value="">Select Skill</option>
                    <option value="Plumber">🔧 Plumber</option>
                    <option value="Electrician">⚡ Electrician</option>
                    <option value="Carpenter">🪚 Carpenter</option>
                    <option value="Painter">🎨 Painter</option>
                    <option value="Cleaner">🧹 Cleaner</option>
                    <option value="Mason">🧱 Mason</option>
                    <option value="Driver">🚗 Driver</option>
                    <option value="Cook">👨‍🍳 Cook</option>
                    <option value="Security Guard">🛡️ Security Guard</option>
                    <option value="Others">Others</option>
                </select>
                <label>Experience</label>
                <input type="text" name="experience" placeholder="Ex: 5 years">
                <label>Daily Rate (₹)</label>
                <input type="number" name="daily_rate" placeholder="Ex: 500">
                <label>About You</label>
                <textarea name="description" placeholder="Describe your skills and experience..."></textarea>
            </div>
            <button type="submit">Register</button>
        </form>
        <div class="login-link">
            <p>Already have account? <a href="login.php">Login here</a></p>
        </div>
    </div>
</body>
</html>