<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user has specific role
function hasRole($role) {
    return isLoggedIn() && $_SESSION['user_role'] === $role;
}

// Check if user has any of the specified roles
function hasAnyRole($roles) {
    if (!isLoggedIn()) {
        return false;
    }
    
    return in_array($_SESSION['user_role'], $roles);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireRole($role) {
    requireLogin();
    
    if (!hasRole($role)) {
        header('Location: unauthorized.php');
        exit;
    }
}

function requireAnyRole($roles) {
    requireLogin();
    
    if (!hasAnyRole($roles)) {
        header('Location: unauthorized.php');
        exit;
    }
}
?>
