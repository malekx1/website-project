// ==================== SMOOTH SCROLLING ====================
document.querySelectorAll('nav a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function(e) {
    e.preventDefault();
    const targetId = this.getAttribute('href');
    const target = document.querySelector(targetId);
    if(target) target.scrollIntoView({ behavior: 'smooth' });
  });
});

let allGames = [];
let cart = JSON.parse(localStorage.getItem('cart')) || [];

// ==================== LOAD DATA FROM BACKEND ====================
async function loadGames() {
  try {
    // Use your teammate's get_products.php (no api/ folder)
    const res = await fetch('get_products.php');
    const data = await res.json();
    if(data.status === 'success') {
      allGames = data.data;
    } else {
      console.error('API error:', data);
      allGames = [];
    }
    renderShop(currentCategory, currentSort);
    renderTopSelling();
  } catch(err) {
    console.error('Failed to load games', err);
  }
}

async function renderTopSelling() {
  const container = document.getElementById('top-grid');
  if(!container) return;
  try {
    // Use your teammate's top_selling.php
    const res = await fetch('top_selling.php');
    const topGames = await res.json();
    container.innerHTML = topGames.map(game => `
      <div class="game-card">
        <img src="${game.image_url}" alt="${game.name}" style="width:100%; height:180px; object-fit:cover;" onerror="this.src='https://placehold.co/300x180/1C2E4A/C1E8FF?text=No+Image'">
        <div class="game-info">
          <h3>${escapeHtml(game.name)}</h3>
          <div class="game-price">$${parseFloat(game.price).toFixed(2)}</div>
          <button class="add-to-cart" data-id="${game.product_id}">Add to Cart</button>
        </div>
      </div>
    `).join('');
    attachAddToCartEvents();
  } catch(e) { console.error(e); }
}

function attachAddToCartEvents() {
  document.querySelectorAll('.add-to-cart').forEach(btn => {
    btn.removeEventListener('click', addToCartHandler);
    btn.addEventListener('click', addToCartHandler);
  });
}

function addToCartHandler(e) {
  const id = parseInt(e.currentTarget.dataset.id);
  addToCart(id);
}

function addToCart(gameId) {
  const game = allGames.find(g => g.product_id == gameId);
  if(!game) return;
  const existing = cart.find(item => item.id === gameId);
  if(existing) {
    existing.quantity += 1;
    showToast(`${game.name} quantity increased to ${existing.quantity}`, "fa-plus-circle");
  } else {
    cart.push({ 
      id: game.product_id, 
      name: game.name, 
      price: parseFloat(game.price), 
      quantity: 1,
      image: game.image_url
    });
    showToast(`${game.name} added to cart!`, "fa-cart-plus");
  }
  saveCart();
  updateCartUI();
}

function saveCart() {
  localStorage.setItem('cart', JSON.stringify(cart));
  updateCartCount();
}

function updateCartCount() {
  const countSpan = document.getElementById('cart-count');
  if(countSpan) {
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    countSpan.textContent = totalItems;
  }
}

function updateCartUI() {
  const tbody = document.getElementById('cart-items');
  const totalSpan = document.getElementById('cart-total-value');
  if(!tbody) return;
  if(cart.length === 0) {
    tbody.innerHTML = '<tr><td colspan="5">Your cart is empty</td></tr>';
    totalSpan.textContent = '0.00';
    return;
  }
  let total = 0;
  tbody.innerHTML = cart.map(item => {
    const itemTotal = item.price * item.quantity;
    total += itemTotal;
    return `
      <tr>
        <td>${escapeHtml(item.name)}</td>
        <td>$${item.price.toFixed(2)}</td>
        <td><input type="number" min="1" value="${item.quantity}" data-id="${item.id}" class="cart-qty" style="width:60px"></td>
        <td>$${itemTotal.toFixed(2)}</td>
        <td><button class="remove-item" data-id="${item.id}"><i class="fas fa-trash"></i></button></td>
      </tr>
    `;
  }).join('');
  totalSpan.textContent = total.toFixed(2);
  
  document.querySelectorAll('.cart-qty').forEach(input => {
    input.addEventListener('change', (e) => {
      const id = parseInt(input.dataset.id);
      let newQty = parseInt(input.value);
      if(isNaN(newQty) || newQty < 1) newQty = 1;
      const item = cart.find(i => i.id === id);
      if(item) item.quantity = newQty;
      saveCart();
      updateCartUI();
    });
  });
  document.querySelectorAll('.remove-item').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const id = parseInt(btn.dataset.id);
      cart = cart.filter(i => i.id !== id);
      saveCart();
      updateCartUI();
      showToast("Item removed", "fa-trash");
    });
  });
}

function renderShop(filterCategory = "all", sortBy = "default") {
  let filtered = [...allGames];
  if(filterCategory !== "all") {
    filtered = filtered.filter(game => game.category === filterCategory);
  }
  if(sortBy === "price-asc") filtered.sort((a,b) => a.price - b.price);
  else if(sortBy === "price-desc") filtered.sort((a,b) => b.price - a.price);
  else if(sortBy === "popular") filtered.sort((a,b) => (b.popular || 0) - (a.popular || 0));
  
  const container = document.getElementById("shop-grid");
  if(!container) return;
  
  container.innerHTML = filtered.map(game => {
    // Apply 3D pop-out effect ONLY to Mario Kart (product_id == 1)
    const isMario = game.product_id == 1;
    const cardClass = isMario ? 'game-card mario-3d' : 'game-card';
    return `
      <div class="${cardClass}" data-category="${game.category}">
        <div class="card-wrapper">
          <img src="${game.image_url}" alt="${game.name}" style="width:100%; height:180px; object-fit:cover;" onerror="this.src='https://placehold.co/300x180/1C2E4A/C1E8FF?text=No+Image'">
          <div class="game-info">
            <h3>${escapeHtml(game.name)}</h3>
            <div class="game-price">$${parseFloat(game.price).toFixed(2)}</div>
            <div class="game-caption">${escapeHtml(game.description || '')}</div>
            <button class="add-to-cart" data-id="${game.product_id}">Add to Cart</button>
          </div>
        </div>
      </div>
    `;
  }).join('');
  attachAddToCartEvents();
}

function showToast(message, icon = "fa-check-circle") {
  const existing = document.querySelector('.toast-notification');
  if(existing) existing.remove();
  const toast = document.createElement('div');
  toast.className = 'toast-notification';
  toast.innerHTML = `<i class="fas ${icon}"></i> ${message}`;
  document.body.appendChild(toast);
  setTimeout(() => toast.remove(), 2500);
}

function escapeHtml(str) {
  if(!str) return '';
  return str.replace(/[&<>]/g, function(m) {
    if(m === '&') return '&amp;';
    if(m === '<') return '&lt;';
    if(m === '>') return '&gt;';
    return m;
  });
}

let currentCategory = "all";
let currentSort = "default";

function applyFiltersAndRender() {
  renderShop(currentCategory, currentSort);
}

document.addEventListener('DOMContentLoaded', () => {
  loadGames();
  updateCartUI();
  updateCartCount();
  
  const catFilter = document.getElementById('category-filter');
  if(catFilter) catFilter.addEventListener('change', (e) => { currentCategory = e.target.value; applyFiltersAndRender(); });
  const sortSelect = document.getElementById('sort-select');
  if(sortSelect) sortSelect.addEventListener('change', (e) => { currentSort = e.target.value; applyFiltersAndRender(); });
  
  document.querySelectorAll('.cat-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      currentCategory = btn.dataset.cat;
      if(catFilter) catFilter.value = currentCategory;
      applyFiltersAndRender();
      document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
    });
  });
  
  document.getElementById('checkout-btn')?.addEventListener('click', () => {
    if(cart.length === 0) {
      showToast("Cart is empty", "fa-exclamation-triangle");
    } else {
      alert("Checkout will be implemented by backend team.");
    }
  });
  
  document.querySelector('.video-thumb')?.addEventListener('click', () => {
    alert("Video tutorial would play here.");
  });
});