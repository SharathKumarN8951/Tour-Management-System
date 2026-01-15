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
    <title>Tour Management System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Load saved theme before UI appears -->
    <script>
        (function () {
            const saved = localStorage.getItem('tms-theme');
            document.documentElement.setAttribute('data-theme', saved === 'light' ? 'light' : 'dark');
        })();
    </script>

    <style>
        :root {
            --bg-body: radial-gradient(circle at top left, #123b80 0%, #020818 55%, #000000 100%);
            --bg-nav: #020818;
            --nav-border: #0b1220;
            --text-main: #e5e7eb;
            --text-muted: #9ca3af;
            --brand-primary: #27b0ff;
            --brand-accent: #ffae1a;
            --link-normal: #e5e7eb;
            --link-active: #27b0ff;
            --btn-primary-bg: linear-gradient(135deg, #00c6ff, #0072ff);
            --btn-primary-shadow: 0 12px 24px rgba(0, 114, 255, 0.45);
            --btn-admin-bg: #ff8c32;
            --btn-admin-shadow: 0 10px 22px rgba(255, 140, 50, 0.55);
            --card-bg: rgba(15, 23, 42, 0.96);
            --border-soft: rgba(148,163,184,0.35);
        }

        [data-theme="light"] {
            --bg-body: #f3f4f6;
            --bg-nav: #ffffff;
            --nav-border: #e5e7eb;
            --text-main: #111827;
            --text-muted: #6b7280;
            --brand-primary: #2563eb;
            --brand-accent: #f97316;
            --link-normal: #374151;
            --link-active: #2563eb;
            --btn-primary-bg: linear-gradient(135deg, #2563eb, #0891b2);
            --btn-primary-shadow: 0 10px 20px rgba(37, 99, 235, 0.35);
            --btn-admin-bg: #f97316;
            --btn-admin-shadow: 0 8px 18px rgba(249,115,22,0.45);
            --card-bg: #ffffff;
            --border-soft: #e5e7eb;
        }

        body {
            margin: 0;
            background: var(--bg-body);
            color: var(--text-main);
            font-family: "Poppins", sans-serif;
        }

        /* ================= NAVBAR ================= */
        .main-navbar {
            background: var(--bg-nav);
            border-bottom: 1px solid var(--nav-border);
            box-shadow: 0 6px 26px rgba(0,0,0,0.55);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .navbar-brand {
            font-weight: 600;
            font-size: 1.25rem;
        }
        .brand-primary { color: var(--brand-primary); }
        .brand-accent  { color: var(--brand-accent); }

        .navbar-nav .nav-link {
            color: var(--link-normal) !important;
            padding-inline: 1rem;
            transition: 0.25s;
            position: relative;
        }
        .navbar-nav .nav-link:hover,
        .nav-link-active {
            color: var(--link-active) !important;
        }
        .navbar-nav .nav-link::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--brand-primary), var(--brand-accent));
            transition: width 0.22s ease;
        }
        .navbar-nav .nav-link:hover::after,
        .nav-link-active::after { width: 60%; }

        /* Buttons */
        .btn-main, .btn-admin, .btn-logout {
            border-radius: 999px;
            padding: 0.45rem 1.3rem;
            display: inline-flex;
            align-items: center;
            font-size: .9rem;
            font-weight: 500;
            color: #fff !important;
            text-decoration: none;
            transition: 0.25s;
        }
        .btn-main   { background: var(--btn-primary-bg); box-shadow: var(--btn-primary-shadow); }
        .btn-admin  { background: var(--btn-admin-bg);  box-shadow: var(--btn-admin-shadow); }
        .btn-logout { background: #ef4444; box-shadow: 0 8px 22px rgba(239,68,68,.45); }
        .btn-main:hover, .btn-admin:hover, .btn-logout:hover {
            transform: translateY(-2px); filter: brightness(1.08);
        }

        /* Hamburger */
        .navbar-toggler {
            background: rgba(15,23,42,0.9);
            border: 2px solid #f9fafb;
            border-radius: 14px;
        }
        .navbar-toggler-icon svg path { fill: #fff; }

        /* Theme Switch */
        .theme-toggle-btn {
            width: 36px; height: 36px; border-radius: 50%;
            border: 2px solid rgba(148,163,184,.7);
            background: transparent;
            display: flex; align-items: center; justify-content: center;
            transition: 0.3s;
        }
        .theme-toggle-btn:hover {
            border-color: var(--brand-primary);
            background: rgba(148,163,184,0.2);
        }
        .theme-toggle-icon { font-size: 1.15rem; pointer-events: none; }

        .page-wrapper { padding: 26px 0 40px; }

        @media (max-width:575px) {
            .btn-main, .btn-admin, .btn-logout {
                width: 100%; justify-content: center;
            }
        }

        /* =================== Card design =================== */
        .card, .card-shadow, .auth-card, .login-card {
            border-radius: 22px !important;
            background: var(--card-bg) !important;
            border: 1px solid var(--border-soft);
            backdrop-filter: blur(14px);
            box-shadow: 0 14px 32px rgba(0,0,0,0.35);
            transition: .35s;
            position: relative;
            overflow: hidden;
        }
        .card:hover, .card-shadow:hover, .auth-card:hover, .login-card:hover {
            transform: translateY(-4px) scale(1.01);
            box-shadow: 0 22px 48px rgba(0,0,0,.55);
            border-color: rgba(59,130,246,.45);
        }

        /* 🛠 Gradient border — with pointer-events fix */
        .card:hover::before,
        .card-shadow:hover::before,
        .auth-card:hover::before,
        .login-card:hover::before {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: inherit;
            background: linear-gradient(120deg, #00eaff, #ff8c32, #00eaff) border-box;
            border: 2px solid transparent;
            -webkit-mask: linear-gradient(#fff 0 0) padding-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            animation: borderFlow 1.8s linear infinite;
            pointer-events: none; /* <-- doesn't block clicks */
        }
        @keyframes borderFlow {
            0% {background-position:0% 50%}
            50%{background-position:100% 50%}
            100%{background-position:0% 50%}
        }

        /* ✨ Improve readability inside dark cards */
        [data-theme="dark"] .card-body,
        [data-theme="dark"] .card-body p,
        [data-theme="dark"] .card-body li,
        [data-theme="dark"] .card-body span,
        [data-theme="dark"] .card-body small {
            color: #e5e7eb !important;   /* light grey text */
        }
        [data-theme="dark"] .card-body h1,
        [data-theme="dark"] .card-body h2,
        [data-theme="dark"] .card-body h3,
        [data-theme="dark"] .card-body h4,
        [data-theme="dark"] .card-body h5 {
            color: #f9fafb !important;   /* brighter headings */
        }
        [data-theme="dark"] .card-body .text-muted {
            color: #a5b4fc !important;   /* soft light blue for muted text */
        }

        /* 📌 Fix system overview light boxes text */
        [data-theme="dark"] .stat-card,
        [data-theme="dark"] .stat-card * {
            color: #0f172a !important;
        }
        [data-theme="light"] .stat-card,
        [data-theme="light"] .stat-card * {
            color: #0f172a !important;
        }
    </style>
</head>

<body>
<nav class="navbar navbar-expand-lg main-navbar">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <span class="brand-primary">Tour</span><span class="brand-accent">Management</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
            <span class="navbar-toggler-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M1.5 3.5A.5.5 0 012 3h12a.5.5 0 010 1H2a.5.5 0 01-.5-.5zm0 4A.5.5 0 012 7h12a.5.5 0 010 1H2a.5.5 0 01-.5-.5zm0 4A.5.5 0 012 11h12a.5.5 0 010 1H2a.5.5 0 01-.5-.5z"/>
                </svg>
            </span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='index.php'?'nav-link-active':'' ?>" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='packages.php'?'nav-link-active':'' ?>" href="packages.php">Packages</a></li>
                <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='booking_history.php'?'nav-link-active':'' ?>" href="booking_history.php">My Bookings</a></li>
            </ul>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <?php if (!empty($_SESSION['customer_id'])): ?>
                    <a href="dashboard.php" class="btn-main">Dashboard</a>
                    <a href="logout.php" class="btn-logout">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn-main">Customer Login</a>
                <?php endif; ?>

                <a href="admin/login.php" class="btn-admin">Admin Login</a>

                <button class="theme-toggle-btn" id="themeToggle" type="button">
                    <span class="theme-toggle-icon" id="themeIcon">🌙</span>
                </button>
            </div>
        </div>
    </div>
</nav>

<div class="container page-wrapper">
<script>
(function () {
    const btn = document.getElementById('themeToggle');
    const icon = document.getElementById('themeIcon');
    const getTheme = () => document.documentElement.getAttribute('data-theme');

    function sync() {
        icon.textContent = getTheme() === 'light' ? '☀️' : '🌙';
    }
    sync();

    btn.addEventListener("click", () => {
        const next = getTheme() === "light" ? "dark" : "light";
        document.documentElement.setAttribute("data-theme", next);
        localStorage.setItem("tms-theme", next);
        sync();
    });
})();
</script>
