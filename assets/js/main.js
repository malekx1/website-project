// ==================== PLAY VERSE MAIN (FULLY WORKING) ====================
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

// Load games from API
async function loadGames() {
    showGlobalLoading();
    try {
        const res = await fetch('api/get_products.php');
        const data = await res.json();
        console.log("API response:", data);
        if (data.status === 'success' && data.data) {
            allGames = data.data;
            renderShop();
            renderTopSelling();
            initCarousel();  // new function name
        } else {
            document.getElementById('shop-grid').innerHTML = '<p>No games found.</p>';
        }
    } catch(err) {
        console.error(err);
        document.getElementById('shop-grid').innerHTML = '<p>Error loading games.</p>';
    } finally {
        hideGlobalLoading();
    }
}

function renderShop() {
    const category = document.getElementById('category-filter').value;
    let filtered = allGames;
    if (category !== 'all') filtered = allGames.filter(g => g.category === category);
    const sort = document.getElementById('sort-select').value;
    if (sort === 'price-asc') filtered.sort((a,b) => a.price - b.price);
    if (sort === 'price-desc') filtered.sort((a,b) => b.price - a.price);
    const container = document.getElementById('shop-grid');
    if (!container) return;
    if (!filtered.length) { container.innerHTML = '<p>No games in this category.</p>'; return; }
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
    });
    container.innerHTML = html;
    attachCartEvents();
    attachDoubleClick();
}

function attachDoubleClick() {
    document.querySelectorAll('.game-card').forEach(card => {
        card.removeEventListener('dblclick', doubleClickHandler);
        card.addEventListener('dblclick', doubleClickHandler);
    });
}
function doubleClickHandler(e) {
    const card = e.currentTarget;
    const id = parseInt(card.querySelector('.add-to-cart').dataset.id);
    const game = allGames.find(g => g.product_id == id);
    if (game) showGameModal(game);
}

// Cache for reviews (to avoid repeated network calls)
let reviewsCache = {};

async function showGameModal(game) {
    const modal = document.getElementById('gameModal');
    const modalBody = document.getElementById('modal-body');
    
    // Show loading spinner immediately
    modalBody.innerHTML = `
        <div style="text-align: center; padding: 2rem;">
            <div class="loader"></div>
            <p style="margin-top: 1rem;">Loading game details...</p>
        </div>
    `;
    modal.style.display = 'block';
    
    // Prepare video URL
    let videoUrl = game.video_url || '';
    if (videoUrl.includes('watch?v=')) videoUrl = videoUrl.replace('watch?v=', 'embed/');
    
    // Fetch reviews (from cache or network)
    let reviewsHtml = '<h4>User Reviews</h4><div class="reviews-list">';
    if (reviewsCache[game.product_id]) {
        // Use cached reviews
        const reviews = reviewsCache[game.product_id];
        if (reviews.length) {
            reviews.forEach(rev => {
                let stars = '';
                for (let i=1; i<=5; i++) stars += i <= rev.rating ? '★' : '☆';
                reviewsHtml += `<div class="review-item"><strong>${escapeHtml(rev.username)}</strong> ${stars}<br>${escapeHtml(rev.review)}</div>`;
            });
        } else {
            reviewsHtml += '<p>No reviews yet.</p>';
        }
    } else {
        // Fetch from server
        try {
            const res = await fetch(`api_reviews.php?action=get_reviews&product_id=${game.product_id}`);
            const data = await res.json();
            if (data.status === 'success' && data.data.length) {
                reviewsCache[game.product_id] = data.data;
                data.data.forEach(rev => {
                    let stars = '';
                    for (let i=1; i<=5; i++) stars += i <= rev.rating ? '★' : '☆';
                    reviewsHtml += `<div class="review-item"><strong>${escapeHtml(rev.username)}</strong> ${stars}<br>${escapeHtml(rev.review)}</div>`;
                });
            } else {
                reviewsCache[game.product_id] = [];
                reviewsHtml += '<p>No reviews yet.</p>';
            }
        } catch(e) {
            reviewsCache[game.product_id] = [];
            reviewsHtml += '<p>Could not load reviews.</p>';
        }
    }
    reviewsHtml += '</div>';
    
    // Build final modal content
    modalBody.innerHTML = `
        <h2>${escapeHtml(game.name)}</h2>
        ${videoUrl ? `<div style="position:relative; padding-bottom:56.25%; height:0; margin-bottom:20px;"><iframe src="${videoUrl}" style="position:absolute; top:0; left:0; width:100%; height:100%;" frameborder="0" allowfullscreen></iframe></div>` : '<p><em>No video available</em></p>'}
        <p><strong>Price:</strong> $${parseFloat(game.price).toFixed(2)}</p>
        <p>${escapeHtml(game.description)}</p>
        ${reviewsHtml}
        <button class="add-to-cart-modal btn" data-id="${game.product_id}">Add to Cart</button>
    `;
    
    // Attach event to modal's Add to Cart button
    const modalBtn = modalBody.querySelector('.add-to-cart-modal');
    if (modalBtn) {
        modalBtn.onclick = () => {
            addToCart(game.product_id);
            modal.style.display = 'none';
        };
    }
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
            addToCart(id);
        };
    });
}

function addToCart(id) {
    const game = allGames.find(g => g.product_id == id);
    if (!game) return;
    const existing = cart.find(item => item.id === id);
    if (existing) existing.quantity++;
    else cart.push({ id, name: game.name, price: parseFloat(game.price), quantity: 1, image: game.image_url });
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
    updateCartUI();
    showToast(`${game.name} added to cart!`, 'fa-cart-plus');
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
        tbody.innerHTML = '<tr><td colspan="5">Your cart is empty</td></tr>';
        document.getElementById('cart-total-value').textContent = '0.00';
        return;
    }
    let total = 0, rows = '';
    cart.forEach((item, idx) => {
        const sub = item.price * item.quantity;
        total += sub;
        rows += `
            <tr>
                <td>${escapeHtml(item.name)}</td>
                <td>$${item.price.toFixed(2)}</td>
                <td><input type="number" min="1" value="${item.quantity}" data-index="${idx}" class="cart-qty" style="width:60px"></td>
                <td>$${sub.toFixed(2)}</td>
                <td><button class="remove-item" data-index="${idx}"><i class="fas fa-trash"></i> Remove</button></td>
            </tr>
        `;
    });
    tbody.innerHTML = rows;
    document.getElementById('cart-total-value').textContent = total.toFixed(2);
    document.querySelectorAll('.cart-qty').forEach(inp => {
        inp.onchange = () => {
            const idx = parseInt(inp.dataset.index);
            let q = parseInt(inp.value);
            if (isNaN(q) || q < 1) q = 1;
            cart[idx].quantity = q;
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartUI();
            updateCartCount();
        };
    });
    document.querySelectorAll('.remove-item').forEach(btn => {
        btn.onclick = () => {
            const idx = parseInt(btn.dataset.index);
            cart.splice(idx, 1);
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

// Slider
let currentIndex = 0;
// ===== FEATURED CAROUSEL (LARGE RECTANGLE) =====
let featuredIndex = 0;
let featuredGames = [];

function initCarousel() {
    featuredGames = allGames.slice(0, 6); // take first 6 games
    if (!featuredGames.length) return;
    renderCarousel();
    renderDots();
}

function renderCarousel() {
    const slide = document.getElementById('carouselSlide');
    if (!slide) return;
    const game = featuredGames[featuredIndex];
    if (!game) return;
    let img = game.image_url;
    if (img && !img.startsWith('http') && !img.startsWith('assets/')) img = 'assets/images/' + img;
    if (!img) img = 'https://placehold.co/600x400/1C2E4A/C1E8FF?text=Game';
    
    const hasDiscount = game.price > 20;
    const discountPercent = hasDiscount ? 25 : 0;
    const oldPrice = hasDiscount ? (game.price / (1 - discountPercent/100)).toFixed(2) : null;
    
    slide.innerHTML = `
        <div class="featured-card">
            <div class="featured-image">
                <img src="${img}" alt="${escapeHtml(game.name)}">
            </div>
            <div class="featured-info">
                <span class="featured-badge"><i class="fas fa-star"></i> Top Seller</span>
                <h3 class="featured-title">${escapeHtml(game.name)}</h3>
                <p class="featured-desc">${escapeHtml(game.description?.substring(0, 100) || 'No description')}...</p>
                <div class="featured-price">
                    $${parseFloat(game.price).toFixed(2)}
                    ${oldPrice ? `<span class="featured-old-price">$${oldPrice}</span>` : ''}
                    ${discountPercent ? `<span class="featured-discount">-${discountPercent}%</span>` : ''}
                </div>
                <button class="featured-button add-to-cart" data-id="${game.product_id}">Add to Cart</button>
            </div>
        </div>
    `;
    const btn = slide.querySelector('.add-to-cart');
    if (btn) btn.onclick = () => addToCart(parseInt(btn.dataset.id));
}

function renderDots() {
    const dotsContainer = document.getElementById('carouselDots');
    if (!dotsContainer) return;
    dotsContainer.innerHTML = '';
    featuredGames.forEach((_, idx) => {
        const dot = document.createElement('div');
        dot.classList.add('dot');
        if (idx === featuredIndex) dot.classList.add('active');
        dot.addEventListener('click', () => {
            featuredIndex = idx;
            renderCarousel();
            renderDots();
        });
        dotsContainer.appendChild(dot);
    });
}

// Carousel arrow events
document.getElementById('carouselPrev')?.addEventListener('click', () => {
    if (featuredGames.length === 0) return;
    featuredIndex = (featuredIndex - 1 + featuredGames.length) % featuredGames.length;
    renderCarousel();
    renderDots();
});
document.getElementById('carouselNext')?.addEventListener('click', () => {
    if (featuredGames.length === 0) return;
    featuredIndex = (featuredIndex + 1) % featuredGames.length;
    renderCarousel();
    renderDots();
});
document.getElementById('prevSlide')?.addEventListener('click', () => {
    if (currentIndex > 0) currentIndex--;
    updateSlider();
});
document.getElementById('nextSlide')?.addEventListener('click', () => {
    const track = document.getElementById('sliderTrack');
    if (track && currentIndex < track.children.length - 3) currentIndex++;
    updateSlider();
});

// Filter and sort events
document.getElementById('category-filter')?.addEventListener('change', () => renderShop());
document.getElementById('sort-select')?.addEventListener('change', () => renderShop());
document.querySelectorAll('.cat-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const cat = btn.dataset.cat;
        document.getElementById('category-filter').value = cat;
        renderShop();
        document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    });
});

// Checkout (demo)
document.getElementById('checkout-btn')?.addEventListener('click', () => {
    if (cart.length === 0) showToast("Cart empty", "fa-exclamation-triangle");
    else showToast("Demo: Payment successful", "fa-check-circle");
});

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    loadGames();
    updateCartCount();
    updateCartUI();
});
// Typing effect for hero headline
const words = ["Your one-stop video game centre", "Play the Future", "Unleash the Gamer", "Level Up Your Experience"];
let wordIndex = 0;
let charIndex = 0;
let isDeleting = false;
const typingElement = document.querySelector('.typing-text');
const cursor = document.querySelector('.cursor');

function typeEffect() {
    if (!typingElement) return;
    const currentWord = words[wordIndex];
    if (isDeleting) {
        typingElement.textContent = currentWord.substring(0, charIndex - 1);
        charIndex--;
    } else {
        typingElement.textContent = currentWord.substring(0, charIndex + 1);
        charIndex++;
    }
    if (!isDeleting && charIndex === currentWord.length) {
        isDeleting = true;
        setTimeout(typeEffect, 2000);
        return;
    }
    if (isDeleting && charIndex === 0) {
        isDeleting = false;
        wordIndex = (wordIndex + 1) % words.length;
    }
    const speed = isDeleting ? 50 : 100;
    setTimeout(typeEffect, speed);
}
if (typingElement) setTimeout(typeEffect, 500);

// Particle background
function initParticles() {
    const canvas = document.getElementById('particleCanvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    let width = window.innerWidth;
    let height = document.querySelector('.hero')?.offsetHeight || 500;
    canvas.width = width;
    canvas.height = height;
    let particles = [];
    const particleCount = 80;
    for (let i = 0; i < particleCount; i++) {
        particles.push({
            x: Math.random() * width,
            y: Math.random() * height,
            radius: Math.random() * 3 + 1,
            speedX: (Math.random() - 0.5) * 0.5,
            speedY: (Math.random() - 0.5) * 0.3,
            color: `rgba(193, 232, 255, ${Math.random() * 0.4 + 0.1})`
        });
    }
    function draw() {
        if (!ctx) return;
        ctx.clearRect(0, 0, width, height);
        for (let p of particles) {
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
            ctx.fillStyle = p.color;
            ctx.fill();
            p.x += p.speedX;
            p.y += p.speedY;
            if (p.x < 0) p.x = width;
            if (p.x > width) p.x = 0;
            if (p.y < 0) p.y = height;
            if (p.y > height) p.y = 0;
        }
        requestAnimationFrame(draw);
    }
    draw();
    window.addEventListener('resize', () => {
        width = window.innerWidth;
        height = document.querySelector('.hero')?.offsetHeight || 500;
        canvas.width = width;
        canvas.height = height;
        particles = [];
        for (let i = 0; i < particleCount; i++) {
            particles.push({
                x: Math.random() * width,
                y: Math.random() * height,
                radius: Math.random() * 3 + 1,
                speedX: (Math.random() - 0.5) * 0.5,
                speedY: (Math.random() - 0.5) * 0.3,
                color: `rgba(193, 232, 255, ${Math.random() * 0.4 + 0.1})`
            });
        }
    });
}
initParticles();