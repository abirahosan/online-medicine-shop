<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register &mdash; MediShop</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="auth-body">

<div class="auth-shell">
    <div class="auth-side">
        <div class="logo-big">&#128138;</div>
        <h1>Create an Account</h1>
        <p>Join MediShop to browse medicines, manage orders and more.</p>
        <ul class="feature-list">
            <li>Register as customer</li>
            <li>Manage your profile anytime</li>
            <li>Secure password storage</li>
            <li>Fast search</li>
        </ul>
    </div>

    <div class="auth-form-wrap">
        <div class="auth-card">
            <h2>Create Account</h2>
            <p class="muted">Fill in your details to get started</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST" action="index.php?page=register" class="form" novalidate>
                <div class="field">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name"
                           value="<?= htmlspecialchars($old['name']) ?>"
                           placeholder="e.g. Full Name" required>
                </div>
                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email"
                           value="<?= htmlspecialchars($old['email']) ?>"
                           placeholder="e.g. xxx@email.com" required>
                </div>
                <input type="hidden" name="role" value="customer">
                <div class="field">
                    <label for="phone">Phone</label>
                    <input type="text" id="phone" name="phone"
                           value="<?= htmlspecialchars($old['phone']) ?>"
                           placeholder="e.g. +880 1XXXXXXXXX">
                </div>
                <div class="field">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" rows="2"
                              placeholder="Your address"><?= htmlspecialchars($old['address']) ?></textarea>
                </div>
                <div class="field-row">
                    <div class="field">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password"
                               placeholder="Min 8 characters" required>
                    </div>
                    <div class="field">
                        <label for="confirm">Confirm</label>
                        <input type="password" id="confirm" name="confirm"
                               placeholder="Repeat password" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Create Account</button>
            </form>

            <p class="auth-foot">Already registered?
                <a href="index.php?page=login">Sign in</a>
            </p>
        </div>
    </div>
</div>

<script>
//JS validation 
function validatePassword(pw) {
    if (pw.length < 8)                  return 'Password must be at least 8 characters.';
    if (!/[A-Z]/.test(pw))              return 'Password must contain at least one uppercase letter.';
    if (!/[a-z]/.test(pw))              return 'Password must contain at least one lowercase letter.';
    if (!/[0-9]/.test(pw))              return 'Password must contain at least one number.';
    if (!/[\W_]/.test(pw))              return 'Password must contain at least one special character.';
    return null;
}

document.querySelector('form').addEventListener('submit', function (e) {
    var name     = document.getElementById('name').value.trim();
    var email    = document.getElementById('email').value.trim();
    var password = document.getElementById('password').value;
    var confirm  = document.getElementById('confirm').value;
    var re       = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (name === '' || email === '' || password === '') {
        e.preventDefault();
        alert('Name, email and password are required.');
        return;
    }
    if (!re.test(email)) {
        e.preventDefault();
        alert('Please enter a valid email address.');
        return;
    }
    var pwError = validatePassword(password);
    if (pwError) {
        e.preventDefault();
        alert(pwError);
        return;
    }
    if (password !== confirm) {
        e.preventDefault();
        alert('Passwords do not match.');
    }
});
</script>

</body>
</html>