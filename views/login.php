<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login &mdash; MediShop</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="auth-body">

<div class="auth-shell">
    <div class="auth-side">
        <div class="logo-big">&#128138;</div>
        <h1>Online Medicine Shop</h1>
        <p>Your trusted source for medicines. Sign in to browse and order medicines online.</p>
        <ul class="feature-list">
            <li>Browse medicines by category</li>
            <li>Search by name or vendor</li>
            <li>Add to cart and checkout</li>
            <li>Track your orders</li>
        </ul>
    </div>

    <div class="auth-form-wrap">
        <div class="auth-card">
            <h2>Welcome Back</h2>
            <p class="muted">Sign in to continue</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="index.php?page=login" class="form" novalidate>
                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email"
                           value="<?= htmlspecialchars($prefill ?? '') ?>"
                           placeholder="Enter your email" required autofocus>
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password"
                           placeholder="Enter your password" required>
                </div>
                <label class="checkbox">
                    <input type="checkbox" name="remember" <?= !empty($prefill) ? 'checked' : '' ?>>
                    <span>Remember me</span>
                </label>
                <button type="submit" class="btn btn-primary btn-block">Sign In</button>
            </form>

            <p class="auth-foot">Don't have an account?
                <a href="index.php?page=register">Register here</a>
            </p>
            <!-- <p class="hint"><strong>Default Admin:</strong> admin@medicine.com / admin123</p> -->
        </div>
    </div>
</div>

<script>
/* JS validation */
document.querySelector('form').addEventListener('submit', function (e) {
    var email    = document.getElementById('email').value.trim();
    var password = document.getElementById('password').value;
    var re       = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (email === '' || password === '') {
        e.preventDefault();
        alert('Please fill in all fields.');
        return;
    }
    if (!re.test(email)) {
        e.preventDefault();
        alert('Please enter a valid email address.');
    }
});
</script>

</body>
</html>