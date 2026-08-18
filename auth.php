<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Conexão temporária para validação de segurança
$auth_conn = new mysqli("localhost", "root", "", "sistema_recrutamento_db");
$user_logged_id = $_SESSION['user_id'];

// Verifica se tem perfil
$has_profile_query = $auth_conn->query("SELECT id FROM perfil_candidato WHERE utilizador_id = $user_logged_id");
$has_profile = ($has_profile_query->num_rows > 0);

// Se não tiver perfil e NÃO estiver na própria página de perfil, obriga a ir criar
$current_page = basename($_SERVER['PHP_SELF']);
if (!$has_profile && $current_page != 'perfil.php') {
    header("Location: perfil.php?status=obrigatorio");
    exit;
}
$auth_conn->close();
?>