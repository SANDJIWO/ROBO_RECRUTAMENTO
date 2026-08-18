<?php
$conn = new mysqli("localhost", "root", "", "sistema_recrutamento_db");
$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Verifica se já existe o utilizador ou email
    $check = $conn->query("SELECT id FROM utilizadores WHERE username='$username' OR email='$email'");
    if ($check->num_rows > 0) {
        $msg = "<div class='alert danger'>Utilizador ou E-mail já estão em uso.</div>";
    } else {
        $sql = "INSERT INTO utilizadores (username, email, password) VALUES ('$username', '$email', '$password')";
        if ($conn->query($sql)) {
            header("Location: login.php?sucesso=1");
            exit;
        } else {
            $msg = "<div class='alert danger'>Erro ao criar conta.</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Criar Conta - Robô Recrutamento</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; display: flex; height: 100vh; justify-content: center; align-items: center; margin: 0; }
        .auth-card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 100%; max-width: 400px; border-top: 4px solid #007bff; }
        h2 { margin-top: 0; color: #2c3e50; text-align: center; }
        label { font-weight: bold; font-size: 13px; color: #6c757d; display: block; margin-top: 15px; }
        input { width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 6px; box-sizing: border-box; margin-top: 5px; }
        .btn { width: 100%; background: #007bff; color: white; border: none; padding: 12px; border-radius: 6px; font-weight: bold; cursor: pointer; margin-top: 20px; }
        .btn:hover { background: #0056b3; }
        .alert { padding: 10px; border-radius: 6px; font-size: 14px; margin-bottom: 15px; font-weight: bold; text-align: center; }
        .danger { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="auth-card">
        <h2>Criar Conta</h2>
        <?php echo $msg; ?>
        <form method="POST">
            <label>Nome de Utilizador (Username):</label>
            <input type="text" name="username" required>
            
            <label>E-mail:</label>
            <input type="email" name="email" required>
            
            <label>Senha:</label>
            <input type="password" name="password" required>
            
            <button type="submit" class="btn">Registrar e Aceder</button>
        </form>
        <p style="text-align:center; font-size:13px; margin-top:20px;">Já tem conta? <a href="login.php">Entrar aqui</a></p>
    </div>
</body>
</html>