<?php
include 'db_config.php';      // ← ADD THIS LINE to get $conn
include 'includes/header.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$user_query = "SELECT id, username, email, profile_pic FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $user_query);
if (!$stmt) {
    die("Database error (user): " . mysqli_error($conn));
}
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$user_result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($user_result);
mysqli_stmt_close($stmt);

// Fetch user's purchased games from library
$library_query = "SELECT l.order_id, l.price, l.purchase_date, p.name, p.product_id 
                  FROM library l 
                  JOIN products p ON l.products_id = p.product_id 
                  WHERE l.users_id = ? 
                  ORDER BY l.order_id DESC";
$lib_stmt = mysqli_prepare($conn, $library_query);
if (!$lib_stmt) {
    die("Database error (library): " . mysqli_error($conn));
}
mysqli_stmt_bind_param($lib_stmt, "i", $user_id);
mysqli_stmt_execute($lib_stmt);
$library_result = mysqli_stmt_get_result($lib_stmt);
?>

<div class="profile-layout" style="max-width: 1000px; margin: 2rem auto;">
    <div style="background: var(--navy); padding: 2rem; border-radius: 30px;">
        <div style="display: flex; align-items: center; gap: 2rem; flex-wrap: wrap;">
            <?php 
            $avatar = !empty($user['profile_pic']) ? $user['profile_pic'] : 'https://ui-avatars.com/api/?name='.urlencode($user['username']).'&background=1b2838&color=67c1f5&size=120';
            ?>
            <img src="<?php echo $avatar; ?>" 
                 style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid var(--accent);">
            <div>
                <h1 style="color: var(--accent);"><?php echo htmlspecialchars($user['username']); ?></h1>
                <p style="color: var(--blue-light);"><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
        </div>

        <hr style="margin: 2rem 0; border-color: var(--blue);">

        <h2 style="color: var(--accent);"><i class="fas fa-gamepad"></i> My Games</h2>
        <?php if (mysqli_num_rows($library_result) > 0): ?>
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
        <?php else: ?>
            <p>You haven't purchased any games yet. <a href="index.php#shop">Go to Shop</a></p>
        <?php endif; ?>
        <?php mysqli_stmt_close($lib_stmt); ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>