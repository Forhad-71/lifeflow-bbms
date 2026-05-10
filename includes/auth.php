<?php
// -------------------------------------------------------------
// Simple role-based access helpers (Admin vs User)
// -------------------------------------------------------------

function ensure_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function require_admin(): void {
    ensure_session();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header('Location: index.php');
        exit;
    }
}

function require_user(): void {
    ensure_session();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user' || !isset($_SESSION['username'])) {
        header('Location: index.php');
        exit;
    }
}

function require_login_any(): void {
    ensure_session();
    if (!isset($_SESSION['role'])) {
        header('Location: index.php');
        exit;
    }
}
