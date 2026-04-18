<?php include 'includes/header.php'; ?>
echo '<script>function showGlobalLoading() { var el = document.getElementById("globalLoadingOverlay"); if(el) el.style.display = "flex"; } function hideGlobalLoading() { var el = document.getElementById("globalLoadingOverlay"); if(el) el.style.display = "none"; } showGlobalLoading(); window.addEventListener("load", function() { hideGlobalLoading(); });</script>';
<div class="cart-page-container">
    <h2 class="section-title">
        <i class="fas fa-shopping-cart"></i> Your Shopping Cart
    </h2>
    <div id="cart-page-content"></div>
</div>

<style>
.cart-page-container { max-width: 1200px; margin: 2rem auto; padding: 0 1rem; }
.cart-table { width: 100%; border-collapse: collapse; background: var(--navy); border-radius: 20px; overflow: hidden; margin-bottom: 2rem; }
.cart-table th, .cart-table td { padding: 15px; text-align: left; border-bottom: 1px solid var(--blue); }
.cart-table th { background: var(--dark-blue); color: var(--accent); }
.cart-item-img { width: 60px; height: 60px; object-fit: cover; border-radius: 10px; }
.remove-btn { background: #ff4444; color: white; border: none; padding: 5px 12px; border-radius: 20px; cursor: pointer; transition: 0.2s; }
.remove-btn:hover { background: #cc0000; }
.cart-summary { background: var(--dark-blue); padding: 1.5rem; border-radius: 20px; text-align: right; margin-top: 1rem; }
.cart-summary h3 { font-size: 1.8rem; color: var(--accent); }
#paypal-button-container { margin-top: 1rem; display: inline-block; }
.empty-cart { text-align: center; padding: 3rem; background: var(--navy); border-radius: 20px; }
.checkbox-col { width: 30px; }
</style>

<script src="https://www.paypal.com/sdk/js?client-id=sb&currency=USD"></script>

<script>
let cart = JSON.parse(localStorage.getItem('cart')) || [];
let selectedItems = new Set();

function renderCartPage() {
    const container = document.getElementById('cart-page-content');
    if (!container) return;

    if (cart.length === 0) {
        container.innerHTML = `
            <div class="empty-cart">
                <i class="fas fa-shopping-cart fa-4x"></i>
                <h3>Your cart is empty</h3>
                <p><a href="index.php#shop">Continue Shopping</a></p>
            </div>`;
        return;
    }

    let rows = '';

    cart.forEach((item, idx) => {
        let img = item.image;
        if (img && !img.startsWith('http') && !img.startsWith('assets/')) {
            img = 'assets/images/' + img;
        }
        if (!img) img = 'https://placehold.co/60x60';

        rows += `
            <tr>
                <td class="checkbox-col">
                    <input type="checkbox" class="select-item" data-index="${idx}" ${selectedItems.has(idx) ? 'checked' : ''}>
                </td>
                <td><img src="${img}" class="cart-item-img"></td>
                <td><strong>${escapeHtml(item.name)}</strong></td>
                <td>$${item.price.toFixed(2)}</td>
                <td>
                    <button class="remove-btn" data-index="${idx}">
                        <i class="fas fa-trash"></i> Remove
                    </button>
                </td>
            </tr>
        `;
    });

    const html = `
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Select</th>
                    <th>Image</th>
                    <th>Game</th>
                    <th>Price</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>${rows}</tbody>
        </table>

        <div class="cart-summary">
            <h3>Total selected: $0.00</h3>
            <div id="paypal-button-container"></div>
            <p><a href="index.php#shop">← Continue Shopping</a></p>
        </div>
    `;

    container.innerHTML = html;

    // checkbox selection
    document.querySelectorAll('.select-item').forEach(cb => {
        cb.addEventListener('change', () => {
            const idx = parseInt(cb.dataset.index);
            if (cb.checked) selectedItems.add(idx);
            else selectedItems.delete(idx);
            updateTotalSelected();
        });
    });

    // remove item
    document.querySelectorAll('.remove-btn').forEach(btn => {
        btn.onclick = () => {
            const idx = parseInt(btn.dataset.index);
            cart.splice(idx, 1);
            localStorage.setItem('cart', JSON.stringify(cart));

            const newSelected = new Set();
            selectedItems.forEach(i => {
                if (i > idx) newSelected.add(i - 1);
                else if (i < idx) newSelected.add(i);
            });
            selectedItems = newSelected;

            renderCartPage();
            updateNavCartCount();
        };
    });

    updateTotalSelected();
}

function updateTotalSelected() {
    let total = 0;

    selectedItems.forEach(idx => {
        if (cart[idx]) total += cart[idx].price;
    });

    document.querySelector('.cart-summary h3').innerHTML =
        `Total selected: $${total.toFixed(2)}`;

    if (window.paypal && total > 0) {
        document.getElementById('paypal-button-container').innerHTML = '';

        window.paypal.Buttons({
            createOrder: (data, actions) => {
                return actions.order.create({
                    purchase_units: [{
                        amount: { value: total.toFixed(2) }
                    }]
                });
            },
            onApprove: (data, actions) => {
                return actions.order.capture().then(details => {
                    alert('Transaction completed by ' + details.payer.name.given_name);

                    cart = cart.filter((_, idx) => !selectedItems.has(idx));
                    selectedItems.clear();

                    localStorage.setItem('cart', JSON.stringify(cart));

                    renderCartPage();
                    updateNavCartCount();
                });
            },
            onError: () => alert('Payment failed')
        }).render('#paypal-button-container');
    }
}

function updateNavCartCount() {
    const total = cart.length;
    const span = document.getElementById('cart-count');
    if (span) span.textContent = total;
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, m => (
        m === '&' ? '&amp;' :
        m === '<' ? '&lt;' :
        '&gt;'
    ));
}

document.addEventListener('DOMContentLoaded', () => {
    renderCartPage();
    updateNavCartCount();
});
function showLoading() { const el = document.getElementById('loadingOverlay'); if(el) el.style.display = 'flex'; }
function hideLoading() { const el = document.getElementById('loadingOverlay'); if(el) el.style.display = 'none'; }
</script>

<?php include 'includes/footer.php'; ?>