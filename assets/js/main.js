// ==================== SMOOTH SCROLLING ====================
document.querySelectorAll('nav a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function(e) {
    e.preventDefault();
    const targetId = this.getAttribute('href');
    const target = document.querySelector(targetId);
    if(target) {
      target.scrollIntoView({ behavior: 'smooth' });
    }
  });
});

// ==================== GAME DATA (with working image placeholders) ====================
const gamesData = [
  { id: 1, name: "Mario Kart 8 Deluxe", price: 49.99, category: "Racing", caption: "Race with friends in chaotic fun!", img: "https://placehold.co/300x180/1C2E4A/C1E8FF?text=Mario+Kart", popular: 95 },
  { id: 2, name: "Stardew Valley", price: 14.99, category: "Indie", caption: "Grow your farm, build relationships.", img: "https://placehold.co/300x180/1C2E4A/C1E8FF?text=Stardew+Valley", popular: 88 },
  { id: 3, name: "Resident Evil 4", price: 39.99, category: "Horror", caption: "Survive the nightmare.", img: "https://placehold.co/300x180/1C2E4A/C1E8FF?text=Resident+Evil", popular: 92 },
  { id: 4, name: "Among Us", price: 4.99, category: "Co-op", caption: "Teamwork & betrayal in space.", img: "https://placehold.co/300x180/1C2E4A/C1E8FF?text=Among+Us", popular: 70 },
  { id: 5, name: "Hollow Knight", price: 15.99, category: "Indie", caption: "Beautiful hand-drawn adventure.", img: "https://placehold.co/300x180/1C2E4A/C1E8FF?text=Hollow+Knight", popular: 89 },
  { id: 6, name: "Phasmophobia", price: 13.99, category: "Co-op", caption: "Ghost hunting with friends.", img: "https://placehold.co/300x180/1C2E4A/C1E8FF?text=Phasmophobia", popular: 85 }
];

let cart = JSON.parse(localStorage.getItem('cart')) || [];

// ==================== TOAST FUNCTION ====================
function showToast(message, icon = "fa-check-circle") {
  const existingToast = document.querySelector('.toast-notification');
  if(existingToast) existingToast.remove();
  
  const toast = document.createElement('div');
  toast.className = 'toast-notification';
  toast.innerHTML = `<i class="fas ${icon}"></i> ${message}`;
  document.body.appendChild(toast);
  
  setTimeout(() => {
    toast.remove();
  }, 2500);
}

// ==================== RENDER SHOP GRID ====================
function renderShop(filterCategory = "all", sortBy = "default") {
  let filtered = [...gamesData];
  if(filterCategory !== "all") {
    filtered = filtered.filter(game => game.category === filterCategory);
  }
  // Sort
  if(sortBy === "price-asc") filtered.sort((a,b) => a.price - b.price);
  else if(sortBy === "price-desc") filtered.sort((a,b) => b.price - a.price);
  else if(sortBy === "popular") filtered.sort((a,b) => b.popular - a.popular);
  
  const container = document.getElementById("shop-grid");
  if(!container) return;
  
  container.innerHTML = filtered.map(game => `
    <div class="game-card" data-category="${game.category}">
      <img src="${game.img}" alt="${game.name}" style="width:100%; height:180px; object-fit:cover; background: var(--navy);">
      <div class="game-info">
        <h3>${game.name}</h3>
        <div class="game-price">$${game.price}</div>
        <div class="game-caption">${game.caption}</div>
        <button class="add-to-cart" data-id="${game.id}">Add to Cart</button>
      </div>
    </div>
  `).join('');
  
  // attach events
  document.querySelectorAll('.add-to-cart').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const id = parseInt(btn.dataset.id);
      addToCart(id);
    });
  });
}

// ==================== RENDER TOP SELLING (on home) ====================
function renderTopSelling() {
  const topContainer = document.getElementById("top-grid");
  if(!topContainer) return;
  
  // take top 3 by popularity
  const top3 = [...gamesData].sort((a,b) => b.popular - a.popular).slice(0, 3);
  topContainer.innerHTML = top3.map(game => `
    <div class="game-card">
      <img src="${game.img}" alt="${game.name}" style="width:100%; height:180px; object-fit:cover;">
      <div class="game-info">
        <h3>${game.name}</h3>
        <div class="game-price">$${game.price}</div>
        <div class="game-caption">${game.caption}</div>
        <button class="add-to-cart" data-id="${game.id}">Add to Cart</button>
      </div>
    </div>
  `).join('');
  
  document.querySelectorAll('#top-grid .add-to-cart').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const id = parseInt(btn.dataset.id);
      addToCart(id);
    });
  });
}

// ==================== ADD TO CART with toast & slide hint ====================
function addToCart(gameId) {
  const game = gamesData.find(g => g.id === gameId);
  if(!game) return;
  const existing = cart.find(item => item.id === gameId);
  if(existing) {
    existing.quantity += 1;
    showToast(`${game.name} quantity increased to ${existing.quantity}`, "fa-plus-circle");
  } else {
    cart.push({ ...game, quantity: 1 });
    showToast(`${game.name} added to cart!`, "fa-cart-plus");
  }
  saveCart();
  updateCartUI();
  // Optional: highlight cart icon
  const cartLink = document.querySelector('nav a[href="#cart"]');
  if(cartLink) {
    cartLink.style.animation = "pulseBorder 0.5s";
    setTimeout(() => cartLink.style.animation = "", 500);
  }
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
    tbody.innerHTML = '<tr><td colspan="4">Your cart is empty</td></tr>';
    totalSpan.textContent = '0.00';
    return;
  }
  
  let total = 0;
  tbody.innerHTML = cart.map(item => {
    const itemTotal = item.price * item.quantity;
    total += itemTotal;
    return `
      <tr>
        <td>${item.name}</td>
        <td>$${item.price}</td>
        <td><input type="number" min="1" value="${item.quantity}" data-id="${item.id}" class="cart-qty" style="width:60px"></td>
        <td>$${itemTotal.toFixed(2)}</td>
        <td><button class="remove-item" data-id="${item.id}"><i class="fas fa-trash"></i></button></td>
      </tr>
    `;
  }).join('');
  totalSpan.textContent = total.toFixed(2);
  
  // attach events
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

// ==================== FILTER & SORT LOGIC ====================
let currentCategory = "all";
let currentSort = "default";

function applyFiltersAndRender() {
  renderShop(currentCategory, currentSort);
}

// ==================== VIDEO TUTORIAL POPUP ====================
function setupVideoTutorial() {
  const videoThumb = document.querySelector('.video-thumb');
  if(videoThumb) {
    videoThumb.addEventListener('click', () => {
      // Simple alert - you can replace with actual video modal
      alert("Video tutorial would play here. (Embed YouTube or Lottie animation)");
    });
  }
}

// ==================== FAKE LOGIN/REGISTER (demo) ====================
function setupLogin() {
  const loginBtn = document.querySelector('#login .btn');
  if(loginBtn) {
    loginBtn.addEventListener('click', () => {
      alert("Login/Register is a demo. Backend integration required.");
    });
  }
  const createLink = document.querySelector('#login a');
  if(createLink) {
    createLink.addEventListener('click', (e) => {
      e.preventDefault();
      alert("Account creation demo. Will be connected to database later.");
    });
  }
}

// ==================== DOM CONTENT LOADED ====================
document.addEventListener('DOMContentLoaded', () => {
  // Render all dynamic content
  renderTopSelling();
  renderShop("all", "default");
  updateCartUI();
  updateCartCount();
  
  // Filter dropdown
  const catFilter = document.getElementById('category-filter');
  if(catFilter) {
    catFilter.addEventListener('change', (e) => {
      currentCategory = e.target.value;
      applyFiltersAndRender();
      // highlight category buttons
      document.querySelectorAll('.cat-btn').forEach(btn => {
        if(btn.dataset.cat === currentCategory) btn.classList.add('active');
        else btn.classList.remove('active');
      });
    });
  }
  
  // Sort dropdown
  const sortSelect = document.getElementById('sort-select');
  if(sortSelect) {
    sortSelect.addEventListener('change', (e) => {
      currentSort = e.target.value;
      applyFiltersAndRender();
    });
  }
  
  // Category buttons
  document.querySelectorAll('.cat-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const cat = btn.dataset.cat;
      currentCategory = cat;
      if(catFilter) catFilter.value = cat;
      applyFiltersAndRender();
      document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
    });
  });
  
  // Checkout button
  const checkoutBtn = document.getElementById('checkout-btn');
  if(checkoutBtn) {
    checkoutBtn.addEventListener('click', () => {
      if(cart.length === 0) {
        showToast("Cart is empty", "fa-exclamation-triangle");
      } else {
        alert("Payment gateway would open here. (Backend later)");
      }
    });
  }
  
  // Video tutorial
  setupVideoTutorial();
  // Login demo
  setupLogin();
});