<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register &mdash; MediShop</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="auth-body">

<div class="auth-shell" style="max-width:1060px; min-height:640px;">

    <!-- Left panel -->
    <div class="auth-side">
        <div class="logo-big">&#128138;</div>
        <h1>Create Your Account</h1>
        <p>Join MediShop today and get access to hundreds of medicines from trusted vendors.</p>
        <ul class="feature-list">
            <li>Register as Admin or Customer</li>
            <li>Secure password hashing</li>
            <li>Manage profile &amp; picture</li>
            <li>Track your orders anytime</li>
        </ul>
    </div>

    <!-- Right panel -->
    <div class="auth-form-wrap" style="align-items:flex-start; padding-top:40px; padding-bottom:40px;">
        <div class="auth-card" style="max-width:420px;">
            <h2>Create Account</h2>
            <p class="muted">Fill in the details below to get started</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST" action="index.php?page=register" class="form" novalidate id="regForm">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

                <!-- Name + Phone -->
                <div class="field-row">
                    <div class="field">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name"
                               value="<?= htmlspecialchars($old['name']) ?>"
                               placeholder="John Doe" required>
                        <span class="field-error" id="nameErr"></span>
                    </div>
                    <div class="field">
                        <label for="phone">Phone</label>
                        <input type="text" id="phone" name="phone"
                               value="<?= htmlspecialchars($old['phone']) ?>"
                               placeholder="+880 1XXXXXXXXX">
                    </div>
                </div>

                <!-- Email -->
                <div class="field">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                           value="<?= htmlspecialchars($old['email']) ?>"
                           placeholder="you@example.com" required>
                    <span class="field-error" id="emailErr"></span>
                </div>

                <!-- Address -->
                <div class="field">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address"
                           value="<?= htmlspecialchars($old['address']) ?>"
                           placeholder="Your delivery address">
                </div>

                <!-- Role -->
                <div class="field">
                    <label for="role">Account Role</label>
                    <select id="role" name="role">
                        <option value="customer" <?= $old['role'] === 'customer' ? 'selected' : '' ?>>Customer</option>
                        <option value="admin"    <?= $old['role'] === 'admin'    ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>

                <!-- Password + Confirm -->
                <div class="field-row">
                    <div class="field">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password"
                               placeholder="Min 8 characters" required>
                        <span class="field-error" id="passErr"></span>
                    </div>
                    <div class="field">
                        <label for="confirm_password">Confirm</label>
                        <input type="password" id="confirm_password" name="confirm_password"
                               placeholder="Repeat password" required>
                        <span class="field-error" id="confirmErr"></span>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Create Account</button>
            </form>

            <p class="auth-foot">
                Already have an account? <a href="index.php?page=login">Sign in</a>
            </p>
        </div>
    </div>

</div>

<script>
(function () {
    var form       = document.getElementById('regForm');
    var nameInput  = document.getElementById('name');
    var emailInput = document.getElementById('email');
    var passInput  = document.getElementById('password');
    var confInput  = document.getElementById('confirm_password');

    var nameErr  = document.getElementById('nameErr');
    var emailErr = document.getElementById('emailErr');
    var passErr  = document.getElementById('passErr');
    var confErr  = document.getElementById('confirmErr');

    function showErr(el, msg) { el.textContent = msg; el.style.display = 'block'; }
    function clearErr(el)     { el.textContent = ''; el.style.display = 'none'; }

    nameInput.addEventListener('input',  function () { clearErr(nameErr);  });
    emailInput.addEventListener('input', function () { clearErr(emailErr); });
    passInput.addEventListener('input',  function () { clearErr(passErr);  });
    confInput.addEventListener('input',  function () { clearErr(confErr);  });

    form.addEventListener('submit', function (e) {
        var valid = true;

        // Name
        if (nameInput.value.trim() === '') {
            showErr(nameErr, 'Full name is required.');
            valid = false;
        }

        // Email
        if (emailInput.value.trim() === '') {
            showErr(emailErr, 'Email is required.');
            valid = false;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value.trim())) {
            showErr(emailErr, 'Enter a valid email address.');
            valid = false;
        }

        // Password
        if (passInput.value === '') {
            showErr(passErr, 'Password is required.');
            valid = false;
        } else if (passInput.value.length < 8) {
            showErr(passErr, 'Password must be at least 8 characters.');
            valid = false;
        }

        // Confirm password
        if (confInput.value === '') {
            showErr(confErr, 'Please confirm your password.');
            valid = false;
        } else if (confInput.value !== passInput.value) {
            showErr(confErr, 'Passwords do not match.');
            valid = false;
        }

        if (!valid) e.preventDefault();
    });
})();
</script>

</body>
</html>
