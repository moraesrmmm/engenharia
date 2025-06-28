<?php
session_start();

// Limpa os dados do formulário da sessão
if (isset($_SESSION['form_data'])) {
    unset($_SESSION['form_data']);
}

// Limpa mensagens de erro também
if (isset($_SESSION['error_message'])) {
    unset($_SESSION['error_message']);
}
if (isset($_SESSION['success_message'])) {
    unset($_SESSION['success_message']);
}

// Redireciona para a página anterior ou dashboard
$redirect = $_GET['redirect'] ?? '../dashboard.php';

// Se o redirect não começar com http ou /, assume que é relativo
if (!preg_match('/^(https?:\/\/|\/)/i', $redirect)) {
    $redirect = $redirect;
}

header("Location: $redirect");
exit;
