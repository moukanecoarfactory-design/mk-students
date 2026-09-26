<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isAdmin    = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
$roleLabel  = $isAdmin ? 'Admin' : 'User';

// Current page for active-link highlighting
$currentPage = basename($_SERVER['PHP_SELF']);

// Detect if we're inside /admin/ or /user/ subfolder
$inSubfolder = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false
             || strpos($_SERVER['PHP_SELF'], '/user/') !== false);
$prefix = $inSubfolder ? '../' : '';

// Build nav links based on role
if ($isAdmin) {
    $navLinks = [
        ['href' => $prefix . 'admin/dashboard.php',       'icon' => 'fa-gauge-high', 'label' => 'Dashboard',     'match' => 'dashboard.php'],
        ['href' => $prefix . 'admin/announcements.php',   'icon' => 'fa-bullhorn',   'label' => 'Announcements', 'match' => 'announcements.php'],
        ['href' => $prefix . 'admin/courses.php',         'icon' => 'fa-book',       'label' => 'Courses',       'match' => 'courses.php'],
        ['href' => $prefix . 'user/profile.php',          'icon' => 'fa-user',       'label' => 'Profile',       'match' => 'profile.php'],
        ['href' => $prefix . 'user/change-password.php',  'icon' => 'fa-lock',       'label' => 'Security',      'match' => 'change-password.php'],
    ];
} else {
    $navLinks = [
        ['href' => $prefix . 'user/dashboard.php',        'icon' => 'fa-house',         'label' => 'Dashboard',     'match' => 'dashboard.php'],
        ['href' => $prefix . 'announcements.php',         'icon' => 'fa-bullhorn',      'label' => 'Announcements', 'match' => 'announcements.php'],
        ['href' => $prefix . 'courses.php',               'icon' => 'fa-book',          'label' => 'Courses',       'match' => 'courses.php'],
        ['href' => $prefix . 'user/my-courses.php',       'icon' => 'fa-graduation-cap','label' => 'My Courses',    'match' => 'my-courses.php'],
        ['href' => $prefix . 'user/profile.php',          'icon' => 'fa-user',          'label' => 'Profile',       'match' => 'profile.php'],
        ['href' => $prefix . 'user/skills.php',           'icon' => 'fa-lightbulb',     'label' => 'Skills',        'match' => 'skills.php'],
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?php echo $pageTitle ?? 'Mk.Students'; ?></title>

    <!-- PWA -->
    <link rel="manifest" href="<?php echo $prefix; ?>manifest.json">
    <meta name="theme-color" content="#667eea">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="MK Students">
    <link rel="icon" type="image/png" href="<?php echo $prefix; ?>assets/icon-192.png">
    <link rel="apple-touch-icon" href="<?php echo $prefix; ?>assets/icon-192.png">

    <link rel="stylesheet" href="<?php echo $prefix; ?>css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        /* ================= PREMIUM NAVBAR ================= */
        .navbar {
            position: sticky; top: 0; z-index: 999;
            display: flex; align-items: center; justify-content: space-between;
            gap: 20px; padding: 14px 40px;
            background: rgba(20, 25, 55, 0.75);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
        }
        .navbar .brand { display: flex; align-items: center; gap: 12px; text-decoration: none; flex-shrink: 0; }
        .navbar .brand-icon {
            width: 40px; height: 40px; border-radius: 11px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff; display: flex; align-items: center; justify-content: center;
            font-size: 18px; box-shadow: 0 8px 20px rgba(102, 126, 234, 0.45);
        }
        .navbar .brand-text {
            font-size: 18px; font-weight: 800; color: #fff;
            letter-spacing: -0.3px; line-height: 1;
            display: flex; align-items: center; gap: 10px;
        }
        .navbar .role-pill {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff; padding: 4px 12px; border-radius: 20px;
            font-size: 10.5px; font-weight: 800; letter-spacing: 1.5px;
            text-transform: uppercase; box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        .navbar .role-pill.user {
            background: linear-gradient(135deg, #06b6d4, #0891b2);
            box-shadow: 0 4px 12px rgba(6, 182, 212, 0.4);
        }
        .navbar .center-links {
            display: flex; align-items: center; gap: 6px;
            flex: 1; justify-content: center;
        }
        .navbar .center-links a {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 9px 16px; border-radius: 11px;
            color: rgba(255, 255, 255, 0.7); text-decoration: none;
            font-size: 13.5px; font-weight: 600; transition: 0.22s;
        }
        .navbar .center-links a:hover { background: rgba(255, 255, 255, 0.08); color: #fff; }
        .navbar .center-links a.active {
            background: rgba(102, 126, 234, 0.2);
            color: #fff;
            box-shadow: inset 0 0 0 1px rgba(102, 126, 234, 0.4);
        }
        .navbar .right-group { display: flex; align-items: center; gap: 12px; flex-shrink: 0; }
        .navbar .icon-btn {
            position: relative; width: 40px; height: 40px;
            border-radius: 11px; background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.8);
            display: flex; align-items: center; justify-content: center;
            font-size: 15px; cursor: pointer; text-decoration: none;
            transition: 0.22s;
        }
        .navbar .icon-btn:hover { background: rgba(255, 255, 255, 0.12); color: #fff; transform: translateY(-2px); }
        .navbar .icon-btn .dot {
            position: absolute; top: 7px; right: 7px;
            width: 8px; height: 8px; background: #ef4444;
            border-radius: 50%; border: 2px solid rgba(20, 25, 55, 1);
            box-shadow: 0 0 8px #ef4444;
        }
        .navbar .user-menu { position: relative; }
        .navbar .user-btn {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 7px 14px 7px 8px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 30px; color: #fff;
            font-size: 13.5px; font-weight: 600;
            cursor: pointer; transition: 0.22s; font-family: inherit;
        }
        .navbar .user-btn:hover { background: rgba(255, 255, 255, 0.12); }
        .navbar .user-avatar {
            width: 32px; height: 32px; border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff; display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 800; flex-shrink: 0;
        }
        .navbar .user-btn .caret { font-size: 11px; opacity: 0.6; transition: 0.22s; }
        .navbar .user-btn.open .caret { transform: rotate(180deg); }
        .navbar .dropdown {
            position: absolute; top: calc(100% + 12px); right: 0;
            min-width: 220px;
            background: rgba(30, 35, 64, 0.98);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 14px; padding: 8px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
            opacity: 0; visibility: hidden;
            transform: translateY(-8px);
            transition: 0.22s; z-index: 1000;
        }
        .navbar .user-menu.open .dropdown { opacity: 1; visibility: visible; transform: translateY(0); }
        .navbar .dropdown a {
            display: flex; align-items: center; gap: 12px;
            padding: 11px 14px; color: rgba(255, 255, 255, 0.85);
            text-decoration: none; font-size: 13.5px;
            font-weight: 500; border-radius: 10px; transition: 0.18s;
        }
        .navbar .dropdown a:hover { background: rgba(102, 126, 234, 0.2); color: #fff; }
        .navbar .dropdown a i { width: 18px; color: #a8b8ff; font-size: 14px; }
        .navbar .dropdown .divider { height: 1px; background: rgba(255, 255, 255, 0.08); margin: 6px 8px; }
        .navbar .dropdown a.danger { color: #ff9b9b; }
        .navbar .dropdown a.danger i { color: #ff9b9b; }
        .navbar .dropdown a.danger:hover { background: rgba(239, 68, 68, 0.15); color: #ffb3b3; }
        .navbar .hamburger {
            display: none; width: 40px; height: 40px;
            border-radius: 11px; background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff; align-items: center; justify-content: center;
            font-size: 17px; cursor: pointer;
        }
        .mobile-panel {
            display: none; position: fixed;
            top: 68px; left: 0; right: 0;
            background: rgba(20, 25, 55, 0.98);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding: 16px 20px; z-index: 998;
            flex-direction: column; gap: 6px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        }
        .mobile-panel.open { display: flex; }
        .mobile-panel a {
            display: flex; align-items: center; gap: 12px;
            padding: 13px 16px; border-radius: 11px;
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none; font-size: 14px; font-weight: 600;
        }
        .mobile-panel a:hover, .mobile-panel a.active {
            background: rgba(102, 126, 234, 0.2); color: #fff;
        }

        /* ===== SHOW / HIDE PASSWORD ===== */
        .pwd-wrap { position: relative; width: 100%; }
        .pwd-wrap input { padding-right: 46px !important; }
        .pwd-toggle {
            position: absolute; right: 14px; top: 50%;
            transform: translateY(-50%); cursor: pointer;
            font-size: 16px; color: #a0a8be;
            transition: 0.2s; user-select: none;
            z-index: 2; background: transparent;
            border: none; padding: 0; line-height: 1;
        }
        .pwd-toggle:hover { color: #667eea; transform: translateY(-50%) scale(1.15); }
        .pwd-toggle.showing { color: #667eea; }

        /* =====================================================
           MOBILE BOTTOM NAVBAR (Instagram-style)
        ===================================================== */
        .mobile-bottom-nav {
            display: none;
            position: fixed;
            bottom: 0; left: 0; right: 0;
            height: 66px;
            background: rgba(20, 25, 55, 0.98);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            z-index: 997;
            padding: 6px 8px;
            padding-bottom: calc(6px + env(safe-area-inset-bottom));
        }
        .mobile-bottom-nav .bn-inner {
            display: flex;
            align-items: center;
            justify-content: space-around;
            height: 100%;
            max-width: 600px;
            margin: 0 auto;
        }
        .mobile-bottom-nav a {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 3px;
            flex: 1;
            padding: 6px 0;
            color: rgba(255, 255, 255, 0.55);
            text-decoration: none;
            font-size: 9.5px;
            font-weight: 600;
            border-radius: 10px;
            transition: 0.22s;
            position: relative;
        }
        .mobile-bottom-nav a i {
            font-size: 18px;
            transition: 0.22s;
        }
        .mobile-bottom-nav a.active {
            color: #fff;
        }
        .mobile-bottom-nav a.active i {
            color: #a8b8ff;
            transform: scale(1.1);
        }
        .mobile-bottom-nav a.active::after {
            content: "";
            position: absolute;
            top: 0;
            width: 22px;
            height: 3px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 0 0 3px 3px;
        }

        @media (max-width: 900px) {
            .navbar { padding: 12px 20px; }
            .navbar .center-links { display: none; }
            .navbar .brand-text { font-size: 16px; }
            .navbar .hamburger { display: none; }
            .mobile-bottom-nav { display: block; }
            body { padding-bottom: 80px !important; }
        }
        @media (max-width: 500px) {
            .navbar .user-btn .uname { display: none; }
            .navbar .role-pill { display: none; }
        }
    </style>
</head>
<body class="dashboard-body">

<nav class="navbar">

    <!-- BRAND -->
    <a href="<?php echo $prefix . ($isAdmin ? 'admin/dashboard.php' : 'user/dashboard.php'); ?>" class="brand">
        <div class="brand-icon"><i class="fa-solid fa-graduation-cap"></i></div>
        <div class="brand-text">
            Mk-StudentHub
            <span class="role-pill <?php echo $isAdmin ? '' : 'user'; ?>">
                <?php echo $roleLabel; ?>
            </span>
        </div>
    </a>

    <!-- CENTER LINKS -->
    <div class="center-links">
        <?php foreach ($navLinks as $link):
            $active = ($currentPage === $link['match']) ? 'active' : '';
        ?>
            <a href="<?php echo $link['href']; ?>" class="<?php echo $active; ?>">
                <i class="fa-solid <?php echo $link['icon']; ?>"></i>
                <?php echo $link['label']; ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- RIGHT SIDE -->
    <div class="right-group">

        <a href="#" class="icon-btn" title="Notifications">
            <i class="fa-solid fa-bell"></i>
            <span class="dot"></span>
        </a>

        <div class="user-menu" id="userMenu">
            <button type="button" class="user-btn" id="userBtn">
                <span class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['first_name'] ?? 'U', 0, 1)); ?>
                </span>
                <span class="uname"><?php echo htmlspecialchars($_SESSION['first_name'] ?? 'User'); ?></span>
                <i class="fa-solid fa-chevron-down caret"></i>
            </button>

            <div class="dropdown">
                <a href="<?php echo $prefix; ?>user/profile.php">
                    <i class="fa-solid fa-user"></i> My Profile
                </a>
                <a href="<?php echo $prefix; ?>user/change-password.php">
                    <i class="fa-solid fa-key"></i> Change Password
                </a>
                <?php if ($isAdmin): ?>
                    <a href="<?php echo $prefix; ?>admin/dashboard.php">
                        <i class="fa-solid fa-users-gear"></i> User Management
                    </a>
                <?php endif; ?>
                <div class="divider"></div>
                <a href="<?php echo $prefix; ?>logout.php" class="danger">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a>
            </div>
        </div>

        <button type="button" class="hamburger" id="hamburger">
            <i class="fa-solid fa-bars"></i>
        </button>
    </div>

</nav>

<!-- Mobile panel (legacy) -->
<div class="mobile-panel" id="mobilePanel">
    <?php foreach ($navLinks as $link):
        $active = ($currentPage === $link['match']) ? 'active' : '';
    ?>
        <a href="<?php echo $link['href']; ?>" class="<?php echo $active; ?>">
            <i class="fa-solid <?php echo $link['icon']; ?>"></i>
            <?php echo $link['label']; ?>
        </a>
    <?php endforeach; ?>
    <a href="<?php echo $prefix; ?>logout.php" style="color:#ff9b9b;">
        <i class="fa-solid fa-right-from-bracket"></i> Logout
    </a>
</div>

<?php if (isset($_SESSION['user_id'])): ?>
<!-- MOBILE BOTTOM NAVBAR -->
<nav class="mobile-bottom-nav">
    <div class="bn-inner">
        <?php 
        // Show only first 4 nav links + logout on mobile
        $mobileLinks = array_slice($navLinks, 0, 4);
        foreach ($mobileLinks as $link): 
            $active = ($currentPage === $link['match']) ? 'active' : '';
            $short = $link['label'];
            if ($short === 'Dashboard') $short = 'Home';
            if ($short === 'Announcements') $short = 'News';
            if ($short === 'My Courses') $short = 'Courses';
        ?>
            <a href="<?php echo $link['href']; ?>" class="<?php echo $active; ?>">
                <i class="fa-solid <?php echo $link['icon']; ?>"></i>
                <span><?php echo $short; ?></span>
            </a>
        <?php endforeach; ?>

        <a href="<?php echo $prefix; ?>user/profile.php">
            <i class="fa-solid fa-user"></i>
            <span>Profile</span>
        </a>
    </div>
</nav>
<?php endif; ?>

<script>
    // User dropdown toggle
    const userMenu = document.getElementById('userMenu');
    const userBtn  = document.getElementById('userBtn');

    if (userBtn) {
        userBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            userMenu.classList.toggle('open');
            userBtn.classList.toggle('open');
        });
        document.addEventListener('click', () => {
            userMenu.classList.remove('open');
            userBtn.classList.remove('open');
        });
    }

    // Mobile hamburger
    const hamburger = document.getElementById('hamburger');
    const mobilePanel = document.getElementById('mobilePanel');
    if (hamburger) {
        hamburger.addEventListener('click', () => {
            mobilePanel.classList.toggle('open');
        });
    }

    // ===== SHOW / HIDE PASSWORD =====
    function togglePassword(btn) {
        const wrap = btn.closest('.pwd-wrap');
        const input = wrap.querySelector('input');
        if (input.type === 'password') {
            input.type = 'text';
            btn.textContent = '🙈';
            btn.classList.add('showing');
        } else {
            input.type = 'password';
            btn.textContent = '👁️';
            btn.classList.remove('showing');
        }
    }

    // Register service worker for PWA
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('<?php echo $prefix; ?>sw.js')
                .catch((err) => console.log('SW registration failed:', err));
        });
    }
</script>

<main class="page-container">