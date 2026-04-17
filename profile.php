<?php
include 'db_config.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_query = mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id");
$user = mysqli_fetch_assoc($user_query);
?>
<div class="profile-layout">
    <h2>Welcome, <?php echo htmlspecialchars($user['username']); ?></h2>
    <p>Email: <?php echo $user['email']; ?></p>
</div>
<?php include 'includes/footer.php'; ?>