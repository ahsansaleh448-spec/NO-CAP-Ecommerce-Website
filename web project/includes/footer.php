</main>
<footer class="site-footer">
    <div class="container foot">
        <div>
            <div class="logo">NO<span>CAP</span></div>
            <p>Real products. Real prices. No cap.</p>
        </div>
        <div>
            <h4>Shop</h4>
            <a href="<?= url('products.php') ?>">All products</a>
            <a href="<?= url('cart.php') ?>">Cart</a>
            <a href="<?= url('orders.php') ?>">My orders</a>
        </div>
        <div>
            <h4>Account</h4>
            <a href="<?= url('login.php') ?>">Login</a>
            <a href="<?= url('register.php') ?>">Register</a>
        </div>
    </div>
    <div class="copy container">&copy; <?= date('Y') ?> NO CAP. All rights reserved.</div>
</footer>

<!-- AI Shopping Assistant -->
<button id="ai-toggle" type="button" aria-label="Open AI assistant">&#10024; Ask AI</button>
<div id="ai-box" hidden>
    <div class="ai-head">
        <b>NO CAP Assistant</b>
        <button id="ai-close" type="button">&times;</button>
    </div>
    <div id="ai-msgs">
        <div class="msg bot">Hello! I am your AI shopping assistant. Tell me your budget, category (hoodies, sneakers, tech), or style and I will recommend products!</div>
    </div>
    <form id="ai-form">
        <input id="ai-input" type="text" maxlength="500" placeholder="e.g. gift ideas under 5000" autocomplete="off">
        <button type="submit">Send</button>
    </form>
</div>

<script>window.NOCAP_AI_URL = "<?= url('assistant.php') ?>";</script>
<script src="<?= url('assets/js/main.js') ?>"></script>
</body>
</html>
