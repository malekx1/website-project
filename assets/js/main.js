// ---------- SMOOTH SCROLLING ----------
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

// ---------- GAME DATA (front-end demo) ----------
// Backend teammates will replace this with PHP/MySQL later
const gamesData = [
  { id: 1, name: "Mario Kart 8 Deluxe", price: 49.99, category: "Racing", caption: "Race with friends in chaotic fun!", img: "assets/images/mario.jpg", popular: 95 },
  { id: 2, name: "Stardew Valley", price: 14.99, category: "Indie", caption: "Grow your farm, build relationships.", img: "assets/images/stardew.jpg", popular: 88 },
  { id: 3, name: "Resident Evil 4", price: 39.99, category: "Horror", caption: "Survive the nightmare.", img: "assets/images/residentEvil.jpg", popular: 92 },
  { id: 4, name: "Among Us", price: 4.99, category: "Co-op", caption: "Teamwork & betrayal in space.", img: "assets/images/amongus.jpg", popular: 70 },
  { id: 5, name: "Hollow Knight", price: 15.99, category: "Indie", caption: "Beautiful hand-drawn adventure.", img: "assets/images/hollow.jpg", popular: 89 },
  { id: 6, name: "Phasmophobia", price: 13.99, category: "Co-op", caption: "Ghost hunting with friends.", img: "assets/images/phasmo.jpg", popular: 85 }
];

let cart = JSON.parse(localStorage.getItem('cart')) || [];

// Helper: render shop grid
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
      <img src="${game.img}" alt="${game.name}" onerror="this.src='https://placehold.co/300x180?text=No+Image'">
      <div class="game-info">
        <h3>${game.name}</h3>
        <div class="game-price">$${game.price}</div>
        <div class="game-caption">${game.caption}</div>
        <button class="add-to-cart" data-id="${game.id}">Add to Cart</button>
      </div>
    </div>
  `).join('');
  
  // attach add-to-cart events
  document.querySelectorAll('.add-to-cart').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const id = parseInt(btn.dataset.id);
      addToCart(id);
    });
  });
}

// Add to cart
function addToCart(gameId) {
  const game = gamesData.find(g => g.id === gameId);
  if(!game) return;
  const existing = cart.find(item => item.id === gameId);
  if(existing) {
    existing.quantity += 1;
  } else {
    cart.push({ ...game, quantity: 1 });
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
  
  // attach quantity change & remove events
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
    });
  });
}

// Filter & Sort logic
let currentCategory = "all";
let currentSort = "default";

function applyFiltersAndRender() {
  renderShop(currentCategory, currentSort);
}

// Event listeners for filter/sort
document.addEventListener('DOMContentLoaded', () => {
  renderShop("all", "default");
  updateCartUI();
  updateCartCount();
  
  // Category filter dropdown
  const catFilter = document.getElementById('category-filter');
  if(catFilter) {
    catFilter.addEventListener('change', (e) => {
      currentCategory = e.target.value;
      applyFiltersAndRender();
      // highlight active category button
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
  // Category buttons (from categories section)
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
  
  // Checkout simulation
  const checkoutBtn = document.getElementById('checkout-btn');
  if(checkoutBtn) {
    checkoutBtn.addEventListener('click', () => {
      if(cart.length === 0) alert("Your cart is empty!");
      else alert("Payment gateway would open here. (Backend integration later)");
    });
  }
});

// Helper for addToCart defined earlier
window.addToCart = addToCart;