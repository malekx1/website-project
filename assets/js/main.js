// ==================== SMOOTH SCROLLING ====================
document.querySelectorAll('nav a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) target.scrollIntoView({ behavior: 'smooth' });
    });
});

let allGames = [];
let cart = JSON.parse(localStorage.getItem('cart')) || [];

// ==================== LOAD GAMES FROM API ====================
async function loadGames() {
    try {
        const res = await fetch('api/get_products.php');
        const data = await res.json();
        if (Array.isArray(data)) {
            allGames = data;
            renderShop();
            renderTopSelling();
        } else {
            console.error("Invalid data:", data);
            document.getElementById('shop-grid').innerHTML = '<p>Error loading games.</p>';
        }
    } catch (err) {
        console.error("Fetch error:", err);
        document.getElementById('shop-grid').innerHTML = '<p>Network error: ' + err.message + '</p>';
    }
}

function renderShop() {
    const container = document.getElementById('shop-grid');
    if (!container) return;
    if (!allGames.length) {
        container.innerHTML = '<p>No games found.</p>';
        return;
    }

    let html = '';
    for (let game of allGames) {
        let img = game.image_url;
        if (img && !img.startsWith('http') && !img.startsWith('assets/')) {
            img = 'assets/images/' + img;
        }
        if (!img) img = 'https://placehold.co/300x180/1C2E4A/C1E8FF?text=No+Image';

        html += `
            <div class="game-card" data-category="${game.category_name || ''}">
                <div class="card-wrapper">
                    <img src="${img}" alt="${escapeHtml(game.name)}" style="width:100%; height:180px; object-fit:cover;" onerror="this.src='https://placehold.co/300x180/1C2E4A/C1E8FF?text=Error'">
                    <div class="game-info">
                        <h3>${escapeHtml(game.name)}</h3>
                        <div class="game-price">$${parseFloat(game.price).toFixed(2)}</div>
                        <div class="game-caption">${escapeHtml(game.description || '')}</div>
                        <button class="add-to-cart" data-id="${game.product_id}">Add to Cart</button>
                    </div>
                </div>
            </div>
        `;
    }
    container.innerHTML = html;
    attachCartEvents();
}

function renderTopSelling() {
    const container = document.getElementById('top-grid');
    if (!container) return;
    const top = allGames.slice(0, 4);
    if (!top.length) return;

    let html = '';
    for (let game of top) {
        let img = game.image_url;
        if (img && !img.startsWith('http') && !img.startsWith('assets/')) {
            img = 'assets/images/' + img;
        }
        if (!img) img = 'https://placehold.co/300x180/1C2E4A/C1E8FF?text=No+Image';

        html += `
            <div class="game-card">
                <img src="${img}" alt="${escapeHtml(game.name)}" style="width:100%; height:180px; object-fit:cover;" onerror="this.src='https://placehold.co/300x180/1C2E4A/C1E8FF?text=Error'">
                <div class="game-info">
                    <h3>${escapeHtml(game.name)}</h3>
                    <div class="game-price">$${parseFloat(game.price).toFixed(2)}</div>
                    <button class="add-to-cart" data-id="${game.product_id}">Add to Cart</button>
                </div>
            </div>
        `;
    }
    container.innerHTML = html;
    attachCartEvents();
}

function attachCartEvents() {
    document.querySelectorAll('.add-to-cart').forEach(btn => {
        btn.onclick = () => {
            const id = parseInt(btn.dataset.id);
            const game = allGames.find(g => g.product_id == id);
            if (game) {
                const existing = cart.find(item => item.id === id);
                if (existing) existing.quantity++;
                else cart.push({ id, name: game.name, price: parseFloat(game.price), quantity: 1 });
                localStorage.setItem('cart', JSON.stringify(cart));
                updateCartCount();
                updateCartUI();
                alert(`${game.name} added to cart!`);
            }
        };
    });
}

function updateCartCount() {
    const total = cart.reduce((s, i) => s + i.quantity, 0);
    const span = document.getElementById('cart-count');
    if (span) span.textContent = total;
}

function updateCartUI() {
    const tbody = document.getElementById('cart-items');
    if (!tbody) return;
    if (cart.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4">Cart is empty</td></tr>';
        document.getElementById('cart-total-value').textContent = '0.00';
        return;
    }
    let total = 0, rows = '';
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
        };
    });
}

// ==================== CHECKOUT ====================
async function checkout() {
    if (cart.length === 0) {
        alert("Cart is empty");
        return;
    }
    try {
        const sessionRes = await fetch('api/check_session.php');
        const sessionData = await sessionRes.json();
        if (!sessionData.logged_in) {
            alert("Please login first");
            window.location.href = 'login.php';
            return;
        }
    } catch (e) {
        alert("Could not verify login status. Please try again.");
        return;
    }

    const payload = {
        cart: cart.map(item => ({
            id: item.id,
            name: item.name,
            price: item.price,
            quantity: item.quantity
        }))
    };

    try {
        const res = await fetch('api/checkout.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
            alert("Purchase successful! Thank you.");
            cart = [];
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartUI();
            updateCartCount();
        } else {
            alert("Checkout failed: " + (data.error || "Unknown error"));
            console.error("Error details:", data);
        }
    } catch (err) {
        alert("Network error. Please try again.");
    }
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function (m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

// ==================== START ====================
document.addEventListener('DOMContentLoaded', () => {
    loadGames();
    updateCartCount();
    updateCartUI();
    const checkoutBtn = document.getElementById('checkout-btn');
    if (checkoutBtn) checkoutBtn.addEventListener('click', checkout);
});