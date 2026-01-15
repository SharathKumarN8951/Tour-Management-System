<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Tour Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Theme before paint -->
    <script>
        (function () {
            const saved = localStorage.getItem('tms-admin-theme');
            const theme = saved === 'light' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>

    <style>
        :root {
            --bg-body: radial-gradient(circle at top left, #123b80 0%, #020818 55%, #000000 100%);
            --nav-bg: #020818;
            --nav-border: #0b1220;
            --text-main: #e5e7eb;
            --text-muted: #9ca3af;
            --brand-primary: #27b0ff;
            --brand-accent: #ffae1a;
            --card-bg: rgba(15, 23, 42, 0.9);
            --border-soft: rgba(148, 163, 184, 0.4);
            --btn-primary-bg: linear-gradient(135deg, #00c6ff, #0072ff);
            --btn-admin-bg: #ff8c32;
        }

        [data-theme="light"] {
            --bg-body: #eef3ff;
            --nav-bg: #ffffff;
            --nav-border: #e5e7eb;
            --text-main: #111827;
            --text-muted: #6b7280;
            --brand-primary: #2563eb;
            --brand-accent: #f97316;
            --card-bg: #ffffff;
            --border-soft: #d1d5db;
            --btn-primary-bg: linear-gradient(135deg, #2563eb, #0891b2);
            --btn-admin-bg: #f97316;
        }

        body {
            margin: 0;
            font-family: "Poppins", sans-serif;
            background: var(--bg-body);
            color: var(--text-main);
        }

        /* NAVBAR */
        .admin-navbar {
            background: var(--nav-bg);
            border-bottom: 1px solid var(--nav-border);
            box-shadow: 0 8px 24px rgba(0,0,0,0.55);
        }

        .navbar-brand {
            font-weight: 600;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        .navbar-brand span:first-child { color: var(--brand-primary); }
        .navbar-brand span:last-child  { color: var(--brand-accent); }

        .admin-navbar .nav-link {
            color: var(--text-muted) !important;
            transition: 0.25s;
        }
        .admin-navbar .nav-link.active,
        .admin-navbar .nav-link:hover {
            color: var(--brand-primary) !important;
        }

        .admin-name {
            font-size: 0.9rem;
            color: var(--text-main);
        }

        /* ================================ */
        /* ⭐ FIX: Hamburger icon visible in light theme */
        /* ================================ */
        [data-theme="light"] .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='%23000000' viewBox='0 0 30 30'%3e%3cpath stroke='%23000000' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }
        /* keep dark theme default white icon */
        [data-theme="dark"] .navbar-toggler-icon {
            background-image: none;
        }
        /* ================================ */

        .theme-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 2px solid rgba(148,163,184,0.7);
            background: transparent;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: 0.25s;
        }

        .theme-btn:hover {
            border-color: var(--brand-primary);
            background: rgba(148,163,184,0.2);
        }

        .theme-icon { font-size: 1.15rem; }

        .admin-page-wrapper {
            padding: 22px 0 42px;
        }
        /* ================================ */
/* PERFECT HAMBURGER ICON FIX       */
/* ================================ */

/* Default (dark theme) → WHITE ICON */
.navbar-toggler-icon {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='%23ffffff' viewBox='0 0 30 30'%3e%3cpath stroke='%23ffffff' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e") !important;
}

/* Light theme → BLACK ICON */
[data-theme="light"] .navbar-toggler-icon {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='%23000000' viewBox='0 0 30 30'%3e%3cpath stroke='%23000000' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e") !important;
}

    </style>

</head>
<body>

<nav class="navbar navbar-expand-lg admin-navbar navbar-dark">
    <div class="container-fluid">

        <a class="navbar-brand" href="dashboard.php">
            <span>Tour</span><span>Admin</span>
        </a>

        <!-- HAMBURGER BUTTON -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- MENU -->
        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link <?=basename($_SERVER['PHP_SELF'])==='dashboard.php'?'active':''?>" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link <?=basename($_SERVER['PHP_SELF'])==='packages.php'?'active':''?>" href="packages.php">Packages</a></li>
                <li class="nav-item"><a class="nav-link <?=basename($_SERVER['PHP_SELF'])==='bookings.php'?'active':''?>" href="bookings.php">Bookings</a></li>
                <li class="nav-item"><a class="nav-link <?=basename($_SERVER['PHP_SELF'])==='payment_requests.php'?'active':''?>" href="payment_requests.php">Payment Requests</a></li>
                <li class="nav-item"><a class="nav-link <?=basename($_SERVER['PHP_SELF'])==='payment_settings.php'?'active':''?>" href="payment_settings.php">Payment Settings</a></li>
                <li class="nav-item"><a class="nav-link <?=basename($_SERVER['PHP_SELF'])==='customers.php'?'active':''?>" href="customers.php">Customers</a></li>
            </ul>

            <div class="d-flex align-items-center gap-3">
                <span class="admin-name"><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></span>

                <button class="theme-btn" id="themeSwitch">
                    <span id="themeIcon">🌙</span>
                </button>

                <a class="nav-link text-danger" href="logout.php">Logout</a>
            </div>
        </div>

    </div>
</nav>

<div class="container admin-page-wrapper">

<script>
    // Admin theme toggle
    (function () {
        const btn = document.getElementById("themeSwitch");
        const icon = document.getElementById("themeIcon");

        function currentTheme() {
            return document.documentElement.getAttribute("data-theme");
        }
        function updateIcon() {
            icon.textContent = currentTheme() === "light" ? "☀️" : "🌙";
        }
        updateIcon();

        btn.addEventListener("click", () => {
            const next = currentTheme() === "light" ? "dark" : "light";
            document.documentElement.setAttribute("data-theme", next);
            localStorage.setItem("tms-admin-theme", next);
            updateIcon();
        });
    })();
</script>
