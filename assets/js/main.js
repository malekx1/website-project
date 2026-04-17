// Simplified, guaranteed to work
let allGames = [];
let cart = JSON.parse(localStorage.getItem('cart')) || [];

// Smooth scroll
document.querySelectorAll('nav a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function(e) {
    e.preventDefault();
    const target = document.querySelector(this.getAttribute('href'));
    if (target) target.scrollIntoView({ behavior: 'smooth' });
  });
});

// Parallax scroll
window.addEventListener('scroll', () => {
  if (window.scrollY > 100) document.body.classList.add('scrolled');
  else document.body.classList.remove('scrolled');
});

// Load and render
async function loadGames() {
  try {
    const res = await fetch('api/get_products.php');
    const data = await res.json();
    if (data.status === 'success' && data.data) {
      allGames = data.data;
      renderShop();
      renderTopSelling();
    } else {
      document.getElementById('shop-grid').innerHTML = '<p>No games found.</p>';
    }
  } catch(e) {
    document.getElementById('shop-grid').innerHTML = '<p>Error loading games.</p>';
  }
}

function renderShop() {
  const category = document.getElementById('category-filter').value;
  let filtered = allGames;
  if (category !== 'all') {
    filtered = allGames.filter(g => g.category === category);
  }
  const container = document.getElementById('shop-grid');
  if (!container) return;
  if (filtered.length === 0) {
    container.innerHTML = '<p>No games in this category.</p>';
    return;
  }
  let html = '';
  filtered.forEach(game => {
    let img = game.image_url;
    if (img && !img.startsWith('http') && !img.startsWith('assets/')) img = 'assets/images/' + img;
    if (!img) img = 'https://placehold.co/300x180/1C2E4A/C1E8FF?text=Game';
    html += `
      <div class="game-card" data-id="${game.product_id}">
        <img src="${img}" alt="${escapeHtml(game.name)}" style="width:100%; height:180px; object-fit:cover;">
        <div class="game-info">
          <h3>${escapeHtml(game.name)}</h3>
          <div class="game-price">$${parseFloat(game.price).toFixed(2)}</div>
          <button class="add-to-cart" data-id="${game.product_id}">Add to Cart</button>
        </div>
      </div>
    `;
    // Add click event to each game card (double-click to open modal)
    const gameCardDiv = document.createElement('div');
    // ... existing card HTML ...
    gameCardDiv.addEventListener('dblclick', () => showGameModal(game));
  });
  container.innerHTML = html;
  attachCartEvents();
  document.querySelectorAll('.game-card').forEach(card => {
    card.addEventListener('dblclick', () => {
        const gameId = card.querySelector('.add-to-cart').dataset.id;
        const game = allGames.find(g => g.product_id == gameId);
        if (game) showGameModal(game);
    });
});
}
 
async function showGameModal(game) {
    const modal = document.getElementById('gameModal');
    const modalBody = document.getElementById('modal-body');
    // Fetch reviews for this game
    let reviewsHtml = '';
    try {
        const res = await fetch(`api_reviews.php?action=get_reviews&product_id=${game.product_id}`);
        const data = await res.json();
        if (data.status === 'success' && data.data.length) {
            reviewsHtml = '<h4>Reviews</h4><ul>';
            data.data.forEach(rev => {
                reviewsHtml += `<li><strong>${rev.username}</strong> (${rev.rating}/5): ${rev.review}</li>`;
            });
            reviewsHtml += '</ul>';
        } else {
            reviewsHtml = '<p>No reviews yet. Be the first!</p>';
        }
    } catch(e) { reviewsHtml = '<p>Could not load reviews.</p>'; }
    
    modalBody.innerHTML = `
        <h2>${escapeHtml(game.name)}</h2>
        <div style="position:relative; padding-bottom:56.25%; height:0; margin-bottom:20px;">
            <iframe src="${game.video_url.replace('watch?v=', 'embed/')}" style="position:absolute; top:0; left:0; width:100%; height:100%;" frameborder="0" allowfullscreen></iframe>
        </div>
        <p><strong>Price:</strong> $${parseFloat(game.price).toFixed(2)}</p>
        <p>${escapeHtml(game.description)}</p>
        ${reviewsHtml}
        <button class="add-to-cart-modal btn" data-id="${game.product_id}">Add to Cart</button>
    `;
    modal.style.display = 'block';
    document.querySelector('.add-to-cart-modal').onclick = () => {
        addToCart(game.product_id);
        modal.style.display = 'none';
    };
}
// Close modal
document.querySelector('.close-modal').onclick = () => document.getElementById('gameModal').style.display = 'none';
window.onclick = (e) => { if (e.target == document.getElementById('gameModal')) document.getElementById('gameModal').style.display = 'none'; };

function renderTopSelling() {
  const top = allGames.slice(0,4);
  const container = document.getElementById('top-grid');
  if (!container) return;
  let html = '';
  top.forEach(game => {
    let img = game.image_url;
    if (img && !img.startsWith('http') && !img.startsWith('assets/')) img = 'assets/images/' + img;
    if (!img) img = 'https://placehold.co/300x180/1C2E4A/C1E8FF?text=Game';
    html += `
      <div class="game-card">
        <img src="${img}" style="width:100%; height:180px; object-fit:cover;">
        <div class="game-info">
          <h3>${escapeHtml(game.name)}</h3>
          <div class="game-price">$${parseFloat(game.price).toFixed(2)}</div>
          <button class="add-to-cart" data-id="${game.product_id}">Add to Cart</button>
        </div>
      </div>
    `;
  });
  container.innerHTML = html;
  attachCartEvents();
}

function attachCartEvents() {
  document.querySelectorAll('.add-to-cart').forEach(btn => {
    btn.onclick = (e) => {
      e.stopPropagation();
      const id = parseInt(btn.dataset.id);
      const game = allGames.find(g => g.product_id == id);
      if (game) {
        const existing = cart.find(item => item.id === id);
        if (existing) existing.quantity++;
        else cart.push({ id, name: game.name, price: parseFloat(game.price), quantity: 1 });
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartCount();
        updateCartUI();
        showToast(`${game.name} added to cart!`, 'fa-cart-plus');
      }
    };
  });
}

function updateCartCount() {
  const total = cart.reduce((s,i) => s + i.quantity, 0);
  const span = document.getElementById('cart-count');
  if (span) span.textContent = total;
}

function updateCartUI() {
  const tbody = document.getElementById('cart-items');
  if (!tbody) return;
  if (cart.length === 0) {
    tbody.innerHTML = '<tr><td colspan="5">Your cart is empty</td></td>';
    document.getElementById('cart-total-value').textContent = '0.00';
    return;
  }
  let total = 0;
  let rows = '';
  cart.forEach(item => {
    const sub = item.price * item.quantity;
    total += sub;
    rows += `
      <tr>
        <td>${escapeHtml(item.name)}</td>
        <td>$${item.price.toFixed(2)}</td>
        <td><input type="number" min="1" value="${item.quantity}" data-id="${item.id}" class="cart-qty" style="width:60px"></td>
        <td>$${sub.toFixed(2)}</td>
        <td><button class="remove-item" data-id="${item.id}">X</button></td>
      </tr>
    `;
  });
  tbody.innerHTML = rows;
  document.getElementById('cart-total-value').textContent = total.toFixed(2);
  // attach qty and remove events
  document.querySelectorAll('.cart-qty').forEach(inp => {
    inp.onchange = () => {
      const id = parseInt(inp.dataset.id);
      const item = cart.find(i => i.id === id);
      if (item) {
        let q = parseInt(inp.value);
        if (isNaN(q) || q < 1) q = 1;
        item.quantity = q;
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartUI();
        updateCartCount();
      }
    };
  });
  document.querySelectorAll('.remove-item').forEach(btn => {
    btn.onclick = () => {
      const id = parseInt(btn.dataset.id);
      cart = cart.filter(i => i.id !== id);
      localStorage.setItem('cart', JSON.stringify(cart));
      updateCartUI();
      updateCartCount();
      showToast('Item removed', 'fa-trash');
    };
  });
}

function showToast(msg, icon) {
  const existing = document.querySelector('.toast-notification');
  if (existing) existing.remove();
  const toast = document.createElement('div');
  toast.className = 'toast-notification';
  toast.innerHTML = `<i class="fas ${icon}"></i> ${msg}`;
  document.body.appendChild(toast);
  setTimeout(() => toast.remove(), 2500);
}

function escapeHtml(str) {
  if (!str) return '';
  return str.replace(/[&<>]/g, function(m) {
    if (m === '&') return '&amp;';
    if (m === '<') return '&lt;';
    if (m === '>') return '&gt;';
    return m;
  });
}

// Filter change events
document.getElementById('category-filter')?.addEventListener('change', renderShop);
document.getElementById('sort-select')?.addEventListener('change', () => {
  const sort = document.getElementById('sort-select').value;
  if (sort === 'price-asc') allGames.sort((a,b)=>a.price-b.price);
  else if (sort === 'price-desc') allGames.sort((a,b)=>b.price-a.price);
  renderShop();
});
document.querySelectorAll('.cat-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const cat = btn.dataset.cat;
    document.getElementById('category-filter').value = cat;
    renderShop();
    document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
  });
});

// Checkout
document.getElementById('checkout-btn').addEventListener('click', async () => {
    if (cart.length === 0) {
        showToast("Cart is empty", "fa-exclamation-triangle");
        return;
    }
    // Check if logged in via session
    try {
        const res = await fetch('api_user.php?action=get_profile');
        const data = await res.json();
        if (data.status === 'error') {
            showToast("Please login first", "fa-exclamation-triangle");
            setTimeout(() => window.location.href = 'login.php', 1500);
            return;
        }
        // Simulate payment success
        showToast("Payment successful! (Demo)", "fa-check-circle");
        cart = [];
        saveCart();
        updateCartUI();
    } catch(e) {
        showToast("Error checking login status", "fa-times-circle");
    }
});
// Parallax background on scroll
window.addEventListener('scroll', function() {
    const scrollY = window.scrollY;
    if (scrollY < 600) {
        document.body.classList.remove('scroll-bg-2', 'scroll-bg-3');
        document.body.classList.add('scroll-bg-1');
    } else if (scrollY < 1200) {
        document.body.classList.remove('scroll-bg-1', 'scroll-bg-3');
        document.body.classList.add('scroll-bg-2');
    } else {
        document.body.classList.remove('scroll-bg-1', 'scroll-bg-2');
        document.body.classList.add('scroll-bg-3');
    }
});
// Trigger once on load
window.dispatchEvent(new Event('scroll'));
// Initialize
document.addEventListener('DOMContentLoaded', () => {
  loadGames();
  updateCartCount();
  updateCartUI();
});