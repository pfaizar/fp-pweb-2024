<?php
function loginUser($email, $password)
{
    $conn = getDBConnection();

    $stmt = $conn->prepare("SELECT id, name, email, password, role, photo_path FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['photo_path'] = $user['photo_path'];
        return true;
    }
    return false;
}

function registerUser($name, $email, $password, $photo_path = null)
{
    $conn = getDBConnection();

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return false;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, photo_path, role) VALUES (?, ?, ?, ?, 'customer')");
    return $stmt->execute([$name, $email, $hashed_password, $photo_path]);
}


function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function isAdmin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: /auth/login.php');
        exit;
    }
}

function requireAdmin()
{
    requireLogin();
    if (!isAdmin()) {
        header('Location: /customer/index.php');
        exit;
    }
}

function logout()
{
    session_destroy();
    header('Location: /index.php');
    exit;
}