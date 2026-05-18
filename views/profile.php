<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile &mdash; MediShop</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="app-body">

<!-- Navbar -->
<header class="navbar">
    <div class="navbar-inner">
        <a class="brand" href="index.php?page=home">
            <span class="brand-icon">&#128138;</span>
            <span>MediShop</span>
        </a>
        <nav class="nav-links">
            <a class="nav-link" href="index.php?page=home">Home</a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a class="nav-link" href="index.php?page=dashboard">Dashboard</a>
            <?php endif; ?>
        </nav>
        <div class="nav-user">
            <a href="index.php?page=profile">
                <span class="user-pill">
                    <span class="user-avatar">
                        <?php if (!empty($user['profile_picture'])): ?>
                            <img src="public/uploads/profiles/<?= htmlspecialchars($user['profile_picture']) ?>"
                                 alt="<?= htmlspecialchars($user['name']) ?>">
                        <?php else: ?>
                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                        <?php endif; ?>
                    </span>
                    <span class="user-meta">
                        <span class="user-name"><?= htmlspecialchars($user['name']) ?></span>
                        <span class="user-role"><?= htmlspecialchars($user['role']) ?></span>
                    </span>
                </span>
            </a>
            <a href="index.php?page=logout" class="btn-logout">Logout</a>
        </div>
    </div>
</header>

<main class="main-content">

    <div class="page-header">
        <div>
            <h1 class="page-title">My Profile</h1>
            <p class="page-sub">Update your account details, picture and password</p>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!--  Profile Info -->
    <div class="card form-card">
        <h3 class="card-title">Account Information</h3>
        <form method="POST" action="index.php?page=profile" class="form" novalidate id="infoForm">
            <input type="hidden" name="action" value="update_info">
            <div class="field-row">
                <div class="field">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name"
                           value="<?= htmlspecialchars($user['name']) ?>"
                           placeholder="Full name" required>
                </div>
                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email"
                           value="<?= htmlspecialchars($user['email']) ?>"
                           placeholder="Email address" required>
                </div>
            </div>
            <div class="field-row">
                <div class="field">
                    <label for="phone">Phone</label>
                    <input type="text" id="phone" name="phone"
                           value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                           placeholder="Phone number">
                </div>
                <div class="field">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address"
                           value="<?= htmlspecialchars($user['address'] ?? '') ?>"
                           placeholder="Your address">
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>

    <!-- Profile Picture -->
    <div class="card form-card">
        <h3 class="card-title">Profile Picture</h3>
        <form method="POST" action="index.php?page=profile" class="form"
              enctype="multipart/form-data" novalidate id="picForm">
            <input type="hidden" name="action" value="update_picture">
            <div style="display:flex;align-items:center;gap:20px;margin-bottom:16px;">
                <div class="user-avatar" style="width:64px;height:64px;font-size:28px;border-radius:50%;flex-shrink:0;">
                    <?php if (!empty($user['profile_picture'])): ?>
                        <img src="public/uploads/profiles/<?= htmlspecialchars($user['profile_picture']) ?>"
                             alt="<?= htmlspecialchars($user['name']) ?>">
                    <?php else: ?>
                        <?= strtoupper(substr($user['name'], 0, 1)) ?>
                    <?php endif; ?>
                </div>
                <div style="color:var(--text-muted);font-size:13px;">
                    JPEG or PNG only &middot; Max 2MB
                </div>
            </div>
            <div class="field">
                <label for="profile_picture">Choose Image</label>
                <input type="file" id="profile_picture" name="profile_picture" accept="image/jpeg,image/png">
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Upload Picture</button>
            </div>
        </form>
    </div>

    <!-- Change Password  -->
    <div class="card form-card">
        <h3 class="card-title">Change Password</h3>
        <form method="POST" action="index.php?page=profile" class="form" novalidate id="pwForm">
            <input type="hidden" name="action" value="change_password">
            <div class="field">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password"
                       placeholder="Enter current password" required>
            </div>
            <div class="field-row">
                <div class="field">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password"
                           placeholder="Min 8 characters" required>
                </div>
                <div class="field">
                    <label for="confirm_password">Confirm New</label>
                    <input type="password" id="confirm_password" name="confirm_password"
                           placeholder="Repeat new password" required>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Change Password</button>
            </div>
        </form>
    </div>

</main>

<footer class="footer">&copy; <?= date('Y') ?> MediShop. All rights reserved.</footer>

<script>
// JS validation 
// profile info 
document.getElementById('infoForm').addEventListener('submit', function (e) {
    var name  = document.getElementById('name').value.trim();
    var email = document.getElementById('email').value.trim();
    var re    = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (name === '' || email === '') {
        e.preventDefault();
        alert('Name and email are required.');
        return;
    }
    if (!re.test(email)) {
        e.preventDefault();
        alert('Please enter a valid email address.');
    }
});

//picture 
document.getElementById('picForm').addEventListener('submit', function (e) {
    var file = document.getElementById('profile_picture').files[0];
    if (!file) {
        e.preventDefault();
        alert('Please choose an image file.');
        return;
    }
    var allowed = ['image/jpeg', 'image/png', 'image/jpg'];
    if (!allowed.includes(file.type)) {
        e.preventDefault();
        alert('Only JPEG and PNG images are allowed.');
        return;
    }
    if (file.size > 2 * 1024 * 1024) {
        e.preventDefault();
        alert('Image must be under 2MB.');
    }
});

//password 
document.getElementById('pwForm').addEventListener('submit', function (e) {
    var current = document.getElementById('current_password').value;
    var newPw   = document.getElementById('new_password').value;
    var confirm = document.getElementById('confirm_password').value;

    if (current === '' || newPw === '' || confirm === '') {
        e.preventDefault();
        alert('All password fields are required.');
        return;
    }
    if (newPw.length < 8) {
        e.preventDefault();
        alert('New password must be at least 8 characters.');
        return;
    }
    if (!/[A-Z]/.test(newPw)) {
        e.preventDefault();
        alert('Password must contain at least one uppercase letter.');
        return;
    }
    if (!/[a-z]/.test(newPw)) {
        e.preventDefault();
        alert('Password must contain at least one lowercase letter.');
        return;
    }
    if (!/[0-9]/.test(newPw)) {
        e.preventDefault();
        alert('Password must contain at least one number.');
        return;
    }
    if (!/[\W_]/.test(newPw)) {
        e.preventDefault();
        alert('Password must contain at least one special character.');
        return;
    }
    if (newPw !== confirm) {
        e.preventDefault();
        alert('New passwords do not match.');
    }
});
</script>

</body>
</html>