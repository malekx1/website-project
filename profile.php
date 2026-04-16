<?php
session_start();
include "db_config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$u_id = $_SESSION['user_id'];
$message = "";

// --- HANDLE PROFILE UPDATE (Username & Image Upload) ---
if (isset($_POST['update_profile'])) {
    $new_username = mysqli_real_escape_string($conn, $_POST['username']);
    $pfp_name = $_FILES['pfp_file']['name'];
    
    // If a new file was uploaded
    if (!empty($pfp_name)) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) { mkdir($target_dir); } // Create folder if missing
        
        $target_file = $target_dir . time() . "_" . basename($pfp_name);
        
        if (move_uploaded_file($_FILES['pfp_file']['tmp_name'], $target_file)) {
            // Update DB with the new file path
            mysqli_query($conn, "UPDATE users SET username = '$new_username', pfp_url = '$target_file' WHERE id = '$u_id'");
        }
    } else {
        // Just update username if no picture was selected
        mysqli_query($conn, "UPDATE users SET username = '$new_username' WHERE id = '$u_id'");
    }
    $_SESSION['username'] = $new_username;
    header("Location: profile.php?success=1");
    exit();
}

$user_query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$u_id'");
$user_data = mysqli_fetch_assoc($user_query);
$library_result = mysqli_query($conn, "SELECT * FROM library WHERE users_id = '$u_id' ORDER BY order_id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile // <?php echo $user_data['username']; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="aesthetic-profile">

<?php include 'includes/header.php'; ?>

<div class="profile-layout">
    <div class="glass-container">
        <div class="user-hero">
            <div class="pfp-wrapper">
                <?php 
                    $display_img = !empty($user_data['pfp_url']) ? $user_data['pfp_url'] : 'https://ui-avatars.com/api/?name='.$user_data['username'].'&background=1b2838&color=67c1f5&size=200';
                ?>
                <img src="<?php echo $display_img; ?>" class="round-pfp">
                <button class="edit-overlay" onclick="document.getElementById('edit-box').style.display='block'">
                    <i class="fas fa-camera"></i>
                </button>
            </div>
            
            <div class="user-titles">
                <h1 class="big-username"><?php echo htmlspecialchars($user_data['username']); ?></h1>
                <p class="user-email"><?php echo $user_data['email']; ?></p>
            </div>
        </div>

        <div id="edit-box" class="edit-modal-content" style="display:none;">
            <form action="profile.php" method="POST" enctype="multipart/form-data" class="modern-form">
                <h3>Update Profile</h3>
                <input type="text" name="username" value="<?php echo $user_data['username']; ?>" required>
                <label for="file-upload" class="custom-file-upload">
                    <i class="fas fa-upload"></i> Choose Picture from Computer
                </label>
                <input id="file-upload" type="file" name="pfp_file" accept="image/*">
                <div class="form-btns">
                    <button type="submit" name="update_profile" class="save-btn">Apply Changes</button>
                    <button type="button" onclick="document.getElementById('edit-box').style.display='none'" class="cancel-btn">Cancel</button>
                </div>
            </form>
        </div>

        <div class="library-header-modern">
            <h2><i class="fas fa-gamepad"></i> Owned Games</h2>
        </div>

        <div class="table-scroll">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Product ID</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($library_result)): ?>
                        <tr class="modern-row">
                            <td>#<?php echo $row['order_id']; ?></td>
                            <td><span class="id-badge"><?php echo $row['products_id']; ?></span></td>
                            <td class="price-bold">$<?php echo number_format($row['price'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>