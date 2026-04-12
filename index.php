<?php include 'db_config.php'; ?>
<?php include 'includes/header.php'; ?>

<!-- HOME SECTION -->
<section id="home" class="hero">
  <h2>Your one-stop video game centre</h2>
  <p>Latest releases, retro classics, indie gems – we have them all.</p>
  <a href="#shop" class="btn">SHOP NOW</a>
</section>

<section id="how-to-order">
  <h2 class="section-title">How to Order</h2>
  <div class="how-to-order">
    <div class="step"><i class="fas fa-search"></i><h3>1. Browse</h3><p>Find your favorite game</p></div>
    <div class="step"><i class="fas fa-cart-plus"></i><h3>2. Add to Cart</h3><p>Click 'Add to Cart'</p></div>
    <div class="step"><i class="fas fa-credit-card"></i><h3>3. Checkout</h3><p>Pay securely</p></div>
  </div>
</section>

<section id="top-selling">
  <h2 class="section-title">🔥 Top Selling Games</h2>
  <div class="games-grid" id="top-grid"></div>
</section>

<!-- SHOP SECTION -->
<section id="shop">
  <h2 class="section-title">🎮 Game Collection</h2>
  <div class="filter-bar">
    <select id="category-filter">
      <option value="all">All Categories</option>
      <option value="Racing">Racing</option>
      <option value="Indie">Indie</option>
      <option value="Horror">Horror</option>
      <option value="Co-op">Co-op</option>
    </select>
    <select id="sort-select">
      <option value="default">Default</option>
      <option value="popular">Most Popular</option>
      <option value="price-asc">Price: Low to High</option>
      <option value="price-desc">Price: High to Low</option>
    </select>
  </div>
  <div class="games-grid" id="shop-grid"></div>
</section>

<!-- CATEGORIES SECTION (buttons) -->
<section id="categories">
  <h2 class="section-title">📂 Browse by Category</h2>
  <div class="categories-buttons">
    <button class="cat-btn active" data-cat="all">All</button>
    <button class="cat-btn" data-cat="Racing">🏎️ Racing</button>
    <button class="cat-btn" data-cat="Indie">🎨 Indie</button>
    <button class="cat-btn" data-cat="Horror">👻 Horror</button>
    <button class="cat-btn" data-cat="Co-op">🤝 Co-op</button>
  </div>
</section>

<!-- CART SECTION -->
<section id="cart">
  <h2 class="section-title">🛒 Your Cart</h2>
  <table class="cart-table">
    <thead>
      <tr><th>Game</th><th>Price</th><th>Qty</th><th>Subtotal</th><th></th></tr>
    </thead>
    <tbody id="cart-items">
      <tr><td colspan="4">Cart is empty</td></tr>
    </tbody>
  </table>
  <div class="cart-total">
    <strong>Total: $<span id="cart-total-value">0.00</span></strong>
  </div>
  <button id="checkout-btn" class="btn checkout-btn">Proceed to Payment</button>
</section>

<!-- LOGIN PLACEHOLDER -->
<section id="login">
  <h2 class="section-title">🔐 Login / Register</h2>
  <div class="login-form">
    <input type="email" placeholder="Email">
    <input type="password" placeholder="Password">
    <button class="btn">Login</button>
    <p style="margin-top:1rem">New user? <a href="#" style="color:var(--accent)">Create account</a></p>
  </div>
</section>

<?php include 'includes/footer.php'; ?>