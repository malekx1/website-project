
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ranesh Games – Your One‑Stop Game Store</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header>
  <div class="logo">
    <h1><i class="fas fa-play-circle"></i> Play <span>Verse</span></h1>
  </div>
  <nav>
    <ul>
      <li><a href="index.php#home"><i class="fas fa-home"></i> Home</a></li>
      <li><a href="index.php#shop"><i class="fas fa-store"></i> Shop</a></li>
      <li><a href="index.php#categories"><i class="fas fa-tags"></i> Categories</a></li>
      <li><a href="index.php#cart"><i class="fas fa-shopping-cart"></i> Cart <span id="cart-count">0</span></a></li>
      <?php if(isset($_SESSION['user_id'])): ?>
        <li><a href="profile.php"><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['username']); ?></a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
      <?php else: ?>
        <li><a href="login.php"><i class="fas fa-user"></i> Login</a></li>
      <?php endif; ?>
    </ul>
  </nav>
</header>
<main>