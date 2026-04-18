<?php
include 'db_config.php';
include 'includes/header.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$user_id = $_SESSION['user_id'];

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_pic'])) {
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
    $file_name = time() . "_" . basename($_FILES['profile_pic']['name']);
    $target_file = $target_dir . $file_name;
    if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file)) {
        mysqli_query($conn, "UPDATE users SET pfp_url = '$target_file' WHERE id = $user_id");
        $success = "Profile picture updated!";
    } else { $error = "Upload failed."; }
}

$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id"));
$purchases = mysqli_query($conn, "SELECT l.*, p.name, p.price FROM library l JOIN products p ON l.products_id = p.product_id WHERE l.users_id = $user_id ORDER BY l.order_id DESC");
?>
<div class="profile-container">
    <div class="profile-card">
        <div class="profile-header">
            <div class="profile-pic">
                <img src="<?php echo !empty($user['pfp_url']) ? $user['pfp_url'] : 'https://ui-avatars.com/api/?name='.urlencode($user['username']).'&background=1b2838&color=67c1f5&size=120'; ?>">
                <form method="POST" enctype="multipart/form-data" class="upload-form">
                    <label for="picUpload" class="upload-btn"><i class="fas fa-camera"></i> Change</label>
                    <input type="file" name="profile_pic" id="picUpload" accept="image/*" style="display:none;" onchange="this.form.submit()">
                </form>
            </div>
            <div class="profile-info">
                <h2><?php echo htmlspecialchars($user['username']); ?></h2>
                <p><?php echo $user['email']; ?></p>
                <?php if(isset($success)) echo "<p class='success'>$success</p>"; ?>
                <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
            </div>
        </div>
        <div class="library-section">
            <h3><i class="fas fa-gamepad"></i> My Games Library</h3>
            <div class="table-wrapper">
                <table class="magic-table">
                    <thead>
                        <tr><th>Order ID</th><th>Game</th><th>Price</th><th>Purchase Date</th></tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($purchases)): ?>
                        <tr class="magic-row">
                            <td>#<?php echo $row['order_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td>$<?php echo number_format($row['price'], 2); ?></td>
                            <td><?php echo $row['purchase_date']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<style>
.profile-container { max-width: 1000px; margin: 2rem auto; }
.profile-card { background: var(--navy); border-radius: 30px; padding: 2rem; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
.profile-header { display: flex; gap: 2rem; align-items: center; flex-wrap: wrap; margin-bottom: 2rem; }
.profile-pic { position: relative; }
.profile-pic img { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 3px solid var(--accent); }
.upload-btn { position: absolute; bottom: 5px; right: 5px; background: var(--accent); color: var(--dark); padding: 5px 10px; border-radius: 20px; cursor: pointer; font-size: 0.8rem; }
.magic-table { width: 100%; border-collapse: collapse; background: var(--dark-blue); border-radius: 20px; overflow: hidden; }
.magic-table th, .magic-table td { padding: 12px; text-align: left; border-bottom: 1px solid var(--blue); }
.magic-row:hover { background: rgba(84,131,179,0.3); transform: scale(1.01); transition: 0.2s; }
.table-wrapper { overflow-x: auto; }
.success { color: #aaffaa; background: #004400; padding: 5px; border-radius: 10px; }
.error { color: #ffaaaa; background: #440000; padding: 5px; border-radius: 10px; }
</style>
<?php include 'includes/footer.php'; ?>