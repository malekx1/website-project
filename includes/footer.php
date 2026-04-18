</main>

<footer class="professional-footer">
    <div class="footer-container">
        <div class="footer-col">
            <h3><i class="fas fa-gamepad"></i> Play Verse</h3>
            <p>Your ultimate gaming destination. Discover, play, and connect.</p>
            <div class="social-icons">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-discord"></i></a>
            </div>
        </div>
        <div class="footer-col">
            <h4>Quick Links</h4>
            <ul>
                <li><a href="index.php#home">Home</a></li>
                <li><a href="index.php#shop">Shop</a></li>
                <li><a href="index.php#categories">Categories</a></li>
                <li><a href="cart.php">Cart</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Support</h4>
            <ul>
                <li><a href="#">Contact Us</a></li>
                <li><a href="#">FAQ</a></li>
                <li><a href="#">Refund Policy</a></li>
                <li><a href="#">Terms of Service</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Payment Methods</h4>
            <div class="payment-icons">
                <i class="fab fa-cc-visa"></i>
                <i class="fab fa-cc-mastercard"></i>
                <i class="fab fa-paypal"></i>
                <i class="fab fa-stripe"></i>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?php echo date("Y"); ?> Play Verse. All rights reserved.</p>
    </div>
</footer>

<!-- Global Loading Overlay -->
<div id="globalLoadingOverlay" style="display: none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.8); backdrop-filter: blur(5px); z-index: 9999; justify-content: center; align-items: center; flex-direction: column;">
    <div class="loader"></div>
    <p style="color: var(--accent); margin-top: 20px;">Loading...</p>
</div>
<script>
function showGlobalLoading() { document.getElementById('globalLoadingOverlay').style.display = 'flex'; }
function hideGlobalLoading() { document.getElementById('globalLoadingOverlay').style.display = 'none'; }
</script>
<script src="assets/js/main.js"></script>
</body>
</html>