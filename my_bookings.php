<?php
require 'config.php';
session_start();

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if(isset($_GET['accept'])) {
    $stmt = $pdo->prepare("UPDATE bookings SET status='accepted' WHERE id=? AND worker_id=?");
    $stmt->execute([$_GET['accept'], $_SESSION['user_id']]);
    header('Location: my_bookings.php');
}
if(isset($_GET['reject'])) {
    $stmt = $pdo->prepare("UPDATE bookings SET status='rejected' WHERE id=? AND worker_id=?");
    $stmt->execute([$_GET['reject'], $_SESSION['user_id']]);
    header('Location: my_bookings.php');
}
if(isset($_GET['complete'])) {
    $stmt = $pdo->prepare("UPDATE bookings SET status='completed' WHERE id=? AND worker_id=?");
    $stmt->execute([$_GET['complete'], $_SESSION['user_id']]);
    header('Location: my_bookings.php');
}

if($_SESSION['user_type'] == 'client') {
    $stmt = $pdo->prepare("SELECT bookings.*, users.name as worker_name, users.phone as worker_phone, worker_profiles.skill FROM bookings JOIN users ON bookings.worker_id = users.id JOIN worker_profiles ON users.id = worker_profiles.user_id WHERE bookings.client_id = ? ORDER BY bookings.created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
} else {
    $stmt = $pdo->prepare("SELECT bookings.*, users.name as client_name, users.phone as client_phone, users.location as client_location FROM bookings JOIN users ON bookings.client_id = users.id WHERE bookings.worker_id = ? ORDER BY bookings.created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
}
$bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Bookings - Worker Connect</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; }
        .navbar { background: #FF5722; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar h1 { color: white; font-size: 24px; }
        .navbar a { color: white; text-decoration: none; margin-left: 15px; padding: 8px 15px; border-radius: 5px; background: rgba(255,255,255,0.2); }
        .container { max-width: 900px; margin: 40px auto; padding: 0 20px; }
        h2 { color: #333; margin-bottom: 25px; }
        .booking-card { background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 20px; }
        .booking-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .booking-header h3 { color: #333; }
        .status-pending { background: #fff3e0; color: #FF9800; padding: 5px 12px; border-radius: 20px; font-size: 13px; }
        .status-accepted { background: #e8f5e9; color: #4CAF50; padding: 5px 12px; border-radius: 20px; font-size: 13px; }
        .status-rejected { background: #ffebee; color: #f44336; padding: 5px 12px; border-radius: 20px; font-size: 13px; }
        .status-completed { background: #e3f2fd; color: #2196F3; padding: 5px 12px; border-radius: 20px; font-size: 13px; }
        .booking-info p { color: #666; font-size: 14px; margin: 5px 0; }
        .description { background: #f9f9f9; padding: 12px; border-radius: 5px; color: #666; margin: 10px 0; }
        .actions { display: flex; gap: 10px; margin-top: 15px; }
        .btn { padding: 8px 16px; border-radius: 5px; text-decoration: none; font-size: 14px; }
        .btn-accept { background: #4CAF50; color: white; }
        .btn-reject { background: #f44336; color: white; }
        .btn-complete { background: #2196F3; color: white; }
        .no-bookings { text-align: center; padding: 50px; color: #666; }
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
        <h2><?php echo $_SESSION['user_type'] == 'client' ? '📋 My Booking Requests' : '📋 Job Requests'; ?></h2>
        <?php if(count($bookings) > 0): ?>
            <?php foreach($bookings as $booking): ?>
                <div class="booking-card">
                    <div class="booking-header">
                        <h3>
                            <?php if($_SESSION['user_type'] == 'client'): ?>
                                🔧 <?php echo $booking['skill']; ?> — <?php echo $booking['worker_name']; ?>
                            <?php else: ?>
                                👤 <?php echo $booking['client_name']; ?>
                            <?php endif; ?>
                        </h3>
                        <span class="status-<?php echo $booking['status']; ?>">
                            <?php
                            $status_labels = ['pending'=>'⏳ Pending', 'accepted'=>'✅ Accepted', 'rejected'=>'❌ Rejected', 'completed'=>'🎉 Completed'];
                            echo $status_labels[$booking['status']];
                            ?>
                        </span>
                    </div>
                    <div class="booking-info">
                        <?php if($_SESSION['user_type'] == 'client'): ?>
                            <p>📞 Worker Phone: <?php echo $booking['worker_phone']; ?></p>
                        <?php else: ?>
                            <p>📞 Client Phone: <?php echo $booking['client_phone']; ?></p>
                            <p>📍 Location: <?php echo $booking['client_location']; ?></p>
                        <?php endif; ?>
                        <p>📅 <?php echo date('d M Y', strtotime($booking['created_at'])); ?></p>
                    </div>
                    <div class="description">
                        <strong>Work Description:</strong><br>
                        <?php echo $booking['description']; ?>
                    </div>
                    <?php if($_SESSION['user_type'] == 'worker' && $booking['status'] == 'pending'): ?>
                        <div class="actions">
                            <a href="my_bookings.php?accept=<?php echo $booking['id']; ?>" class="btn btn-accept" onclick="return confirm('Accept this job?')">✅ Accept</a>
                            <a href="my_bookings.php?reject=<?php echo $booking['id']; ?>" class="btn btn-reject" onclick="return confirm('Reject this job?')">❌ Reject</a>
                        </div>
                    <?php elseif($_SESSION['user_type'] == 'worker' && $booking['status'] == 'accepted'): ?>
                        <div class="actions">
                            <a href="my_bookings.php?complete=<?php echo $booking['id']; ?>" class="btn btn-complete" onclick="return confirm('Mark as completed?')">🎉 Mark Complete</a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-bookings">
                <p>No bookings yet! <?php echo $_SESSION['user_type'] == 'client' ? '<a href="index.php">Find workers!</a>' : 'Wait for job requests!'; ?></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>