<?php include 'includes/header.php'; ?>

<!-- Hero Section with animated gradient -->
<section id="home" class="hero">
  <div class="hero-content">
    <div class="welcome-badge">
      <i class="fas fa-gamepad"></i> Welcome to Play Verse
    </div>
    <h1 class="glitch-text">
      <span class="typing-text"></span>
      <span class="cursor">|</span>
    </h1>
    <p class="hero-subtitle moving-paragraph">Latest releases, retro classics, indie gems — we have them all.</p>
    <div class="hero-buttons">
      <a href="#shop" class="btn pulse-btn">🎮 SHOP NOW</a>
      <a href="#how-to-order" class="btn outline-btn">📖 How It Works</a>
    </div>
  </div>
  <canvas id="particleCanvas" class="hero-particles"></canvas>
  <div class="hero-floaters">
    <div class="floating-icon"><i class="fas fa-joystick"></i></div>
    <div class="floating-icon"><i class="fas fa-trophy"></i></div>
    <div class="floating-icon"><i class="fas fa-users"></i></div>
  </div>
</section>

<!-- Featured Games Slider -->
<!-- Featured Games Carousel (Large Rectangle) -->
<section class="featured-carousel">
  <h2 class="section-title"><i class="fas fa-fire"></i> Featured & Recommended</h2>
  <div class="carousel-container">
    <button class="carousel-arrow prev" id="carouselPrev">&#10094;</button>
    <div class="carousel-slide" id="carouselSlide">
      <!-- Dynamic content will be injected by JavaScript -->
    </div>
    <button class="carousel-arrow next" id="carouselNext">&#10095;</button>
  </div>
  <div class="carousel-dots" id="carouselDots"></div>
</section>
<!-- How to Order -->
<section id="how-to-order">
  <h2 class="section-title"><i class="fas fa-shopping-cart"></i> How to Order</h2>
  <div class="how-to-order">
    <div class="step">
      <div class="step-circle"><i class="fas fa-search fa-2x"></i></div>
      <h3>1. Browse</h3>
      <p>Find your favorite game</p>
    </div>
    <div class="step">
      <div class="step-circle"><i class="fas fa-cart-plus fa-2x"></i></div>
      <h3>2. Add to Cart</h3>
      <p>Click 'Add to Cart'</p>
    </div>
    <div class="step">
      <div class="step-circle"><i class="fas fa-credit-card fa-2x"></i></div>
      <h3>3. Checkout</h3>
      <p>Pay securely</p>
    </div>
  </div>
</section>

<!-- Top Selling Games -->
<section id="top-selling">
  <h2 class="section-title"><i class="fas fa-trophy"></i> Top Selling Games</h2>
  <div class="games-grid" id="top-grid"></div>
</section>

<!-- Game Collection (Shop) -->
<section id="shop">
  <h2 class="section-title"><i class="fas fa-gamepad"></i> Game Collection</h2>
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
      <option value="price-asc">Price: Low to High</option>
      <option value="price-desc">Price: High to Low</option>
    </select>
  </div>
  <div class="games-grid" id="shop-grid"></div>
</section>

<!-- Browse by Genre -->
<section id="categories">
  <h2 class="section-title"><i class="fas fa-tags"></i> Browse by Genre</h2>
  <div class="categories-buttons">
    <button class="cat-btn active" data-cat="all">✨ All Games</button>
    <button class="cat-btn" data-cat="Racing">🏎️ Racing</button>
    <button class="cat-btn" data-cat="Indie">🎨 Indie</button>
    <button class="cat-btn" data-cat="Horror">👻 Horror</button>
    <button class="cat-btn" data-cat="Co-op">🤝 Co-op</button>
  </div>
</section>

<!-- Game Detail Modal -->
<div id="gameModal" class="modal">
  <div class="modal-content">
    <span class="close-modal">&times;</span>
    <div id="modal-body"></div>
  </div>
</div>

<!-- Hidden elements to satisfy main.js (cart functions) -->
<div style="display: none;">
  <tbody id="cart-items"></tbody>
  <span id="cart-total-value"></span>
</div>

<?php include 'includes/footer.php'; ?>