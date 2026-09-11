<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Mk.Students'; ?></title>
    
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        /* ========== NAVBAR ========== */
        .navbar {
            position: sticky;
            top: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 40px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(45, 58, 100, 0.08);
            border-bottom: 1px solid rgba(102, 126, 234, 0.08);
        }

        .navbar .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 20px;
            font-weight: 700;
            color: #17213c;
            text-decoration: none;
            letter-spacing: -0.3px;
            transition: 0.2s;
        }
        .navbar .logo:hover {
            color: #667eea;
        }
        .navbar .logo i {
            color: #667eea;
            font-size: 22px;
        }
        .navbar .logo span {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            padding: 3px 10px;
            border-radius: 20px;
            text-transform: uppercase;
            margin-left: 6px;
        }

        .navbar .nav-links {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .navbar .user-welcome {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 500;
            color: #555;
            padding: 8px 16px;
            background: #f4f7fc;
            border-radius: 30px;
        }
        .navbar .user-welcome i {
            color: #667eea;
            font-size: 16px;
        }

        .navbar .logout-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 18px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 600;
            border-radius: 10px;
            transition: 0.25s;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.25);
        }
        .navbar .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        /* Mobile */
        @media (max-width: 640px) {
            .navbar {
                padding: 12px 18px;
                flex-wrap: wrap;
                gap: 10px;
            }
            .navbar .logo {
                font-size: 17px;
            }
            .navbar .user-welcome {
                font-size: 12.5px;
                padding: 6px 12px;
            }
            .navbar .logout-btn {
                padding: 7px 14px;
                font-size: 12.5px;
            }
        }
    </style>
</head>
<body class="dashboard-body">

<nav class="navbar">
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <a href="../admin/dashboard.php" class="logo">
            <i class="fa-solid fa-graduation-cap"></i> Mk-StudentHub <span>Admin</span>
        </a>
    <?php else: ?>
        <a href="../user/dashboard.php" class="logo">
            <i class="fa-solid fa-graduation-cap"></i> StudentHub
        </a>
    <?php endif; ?>

    <div class="nav-links">
        <?php if (isset($_SESSION['first_name'])): ?>
            <span class="user-welcome">
                <i class="fa-solid fa-circle-user"></i>
                Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?>
            </span>
        <?php endif; ?>

        <a href="../logout.php" class="logout-btn">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </div>
</nav>

<main class="page-container">