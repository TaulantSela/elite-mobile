<?php
// Server-side admin gate. Require this at the very top of every admin page,
// before any output, so an unauthenticated request is redirected and stopped
// instead of relying on client-side JavaScript. The redirect is relative, so
// it resolves correctly whether the caller lives at the web root or in /admin.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username'])) {
    header('Location: LoginForm.php');
    exit;
}
