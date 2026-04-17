<?php
session_start();
include "db_config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$u_id = $_SESSION['user_id'];

// Handle profile update (username & profile picture)
if (isset($_POST['update_profile'])) {
    $new_username = mysqli_real_escape_string($conn, $_POST['username']);
    $upload_dir = "uploads/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    
    $pfp_path = null;
    if (isset($_FILES['pfp_file']) && $_FILES['pfp_file']['error'] === UPLOAD_ERR_OK) {
        $file_name = time() . "_" . basename($_FILES['pfp_file']['name']);
        $target_file = $upload_dir . $file_name;
        if (move_uploaded_file($_FILES['pfp_file']['tmp_name'], $target_file)) {
            $pfp_path = $target_file;
        }
    }
    
    if ($pfp_path) {
        $update = mysqli_prepare($conn, "UPDATE users SET username = ?, profile_pic = ? WHERE id = ?");
        if (!$update) {
            die("Prepare failed: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($update, "ssi", $new_username, $pfp_path, $u_id);
        mysqli_stmt_execute($update);
        mysqli_stmt_close($update);
    } else {
        $update = mysqli_prepare($conn, "UPDATE users SET username = ? WHERE id = ?");
        if (!$update) {
            die("Prepare failed: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($update, "si", $new_username, $u_id);
        mysqli_stmt_execute($update);
        mysqli_stmt_close($update);
    }
    $_SESSION['username'] = $new_username;
    header("Location: profile.php?updated=1");
    exit();
}

// Fetch user data
$stmt = mysqli_prepare($conn, "SELECT id, username, email, pfp_url FROM users WHERE id = ?");
if (!$stmt) {
    die("Prepare failed: " . mysqli_error($conn));
}
mysqli_stmt_bind_param($stmt, "i", $u_id);
mysqli_stmt_execute($stmt);
$user_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

// Fetch user library (purchased games)
$library_query = "SELECT l.order_id, l.price, l.purchase_date, p.name 
                  FROM library l 
                  JOIN products p ON l.products_id = p.product_id 
                  WHERE l.users_id = ? 
                  ORDER BY l.order_id DESC";
$lib_stmt = mysqli_prepare($conn, $library_query);
if (!$lib_stmt) {
    die("Prepare failed: " . mysqli_error($conn));
}
mysqli_stmt_bind_param($lib_stmt, "i", $u_id);
mysqli_stmt_execute($lib_stmt);
$library_result = mysqli_stmt_get_result($lib_stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile - <?php echo htmlspecialchars($user_data['username']); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="profile-layout" style="max-width: 1000px; margin: 2rem auto;">
    <div class="glass-container" style="background: var(--navy); padding: 2rem; border-radius: 30px;">
        <div class="user-hero" style="display: flex; align-items: center; gap: 2rem; flex-wrap: wrap;">
            <div class="pfp-wrapper" style="position: relative;">
                <?php 
                    $pfp = !empty($user_data['profile_pic']) ? $user_data['profile_pic'] : 'https://ui-avatars.com/api/?name='.urlencode($user_data['username']).'&background=1b2838&color=67c1f5&size=150';
                ?>
                <img src="<?php echo $pfp; ?>" class="round-pfp" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 3px solid var(--accent);">
                <button onclick="document.getElementById('edit-box').style.display='flex'" class="edit-overlay" style="position: absolute; bottom: 5px; right: 5px; background: var(--accent); border: none; border-radius: 50%; width: 32px; height: 32px; cursor: pointer;">
                    <i class="fas fa-camera"></i>
                </button>
            </div>
            <div class="user-titles">
                <h1 style="color: var(--accent); font-size: 2.5rem;"><?php echo htmlspecialchars($user_data['username']); ?></h1>
                <p style="color: var(--blue-light);"><?php echo htmlspecialchars($user_data['email']); ?></p>
            </div>
        </div>

        <!-- Edit modal -->
        <div id="edit-box" style="display: none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.8); justify-content: center; align-items: center; z-index: 1000;">
            <form action="profile.php" method="POST" enctype="multipart/form-data" style="background: var(--dark-blue); padding: 2rem; border-radius: 30px; width: 90%; max-width: 400px;">
                <h3 style="color: var(--accent);">Update Profile</h3>
                <input type="text" name="username" value="<?php echo htmlspecialchars($user_data['username']); ?>" required style="width:100%; margin: 10px 0; padding: 10px; border-radius: 10px;">
                <input type="file" name="pfp_file" accept="image/*" style="margin: 10px 0;">
                <div style="display: flex; gap: 10px;">
                    <button type="submit" name="update_profile" class="btn">Save</button>
                    <button type="button" onclick="document.getElementById('edit-box').style.display='none'" class="btn" style="background: gray;">Cancel</button>
                </div>
            </form>
        </div>

        <hr style="margin: 2rem 0; border-color: var(--blue);">

        <h2 style="color: var(--accent);"><i class="fas fa-gamepad"></i> My Games</h2>
        <div class="table-scroll" style="overflow-x: auto;">
            <table style="width:100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: var(--dark);">
                        <th style="padding: 12px;">Order ID</th>
                        <th style="padding: 12px;">Game</th>
                        <th style="padding: 12px;">Price</th>
                        <th style="padding: 12px;">Purchase Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($library_result)): ?>
                        <tr style="border-bottom: 1px solid var(--blue);">
                            <td style="padding: 12px;">#<?php echo $row['order_id']; ?></td>
                            <td style="padding: 12px;"><?php echo htmlspecialchars($row['name']); ?></td>
                            <td style="padding: 12px;">$<?php echo number_format($row['price'], 2); ?></td>
                            <td style="padding: 12px;"><?php echo $row['purchase_date']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>