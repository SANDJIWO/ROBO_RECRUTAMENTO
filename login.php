<?php
// Garante o início da sessão sem interferências
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "sistema_recrutamento_db");
if ($conn->connect_error) { 
    die("Erro de conexão à base de dados: " . $conn->connect_error); 
}

$msg = "";

if (isset($_GET['sucesso'])) {
    $msg = "<div class='alert success'>Conta criada com sucesso! Faça o seu login.</div>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM utilizadores WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows > 0) {
        $user = $res->fetch_assoc();
        
        // Verifica a palavra-passe
        if (password_verify($password, $user['password'])) {
            // Atribui AMBAS as variáveis de sessão para garantir compatibilidade total com qualquer script antigo ou novo
            $_SESSION['utilizador_id'] = $user['id'];
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            // Verifica se o utilizador já tem um perfil preenchido
            $stmt_perfil = $conn->prepare("SELECT id FROM perfil_candidato WHERE utilizador_id = ?");
            $stmt_perfil->bind_param("i", $user['id']);
            $stmt_perfil->execute();
            $perfil = $stmt_perfil->get_result();

            if ($perfil && $perfil->num_rows == 0) {
                header("Location: perfil.php?status=novo");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $msg = "<div class='alert danger'>Senha incorreta!</div>";
        }
    } else {
        $msg = "<div class='alert danger'>Utilizador não encontrado!</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Login - Robô Recrutamento</title>
    <style>
        :root {
            --bg-primary: #ffffff;
            --bg-secondary: #f6f8fa;
            --border-color: #d0d7de;
            --text-main: #24292f;
            --text-muted: #57606a;
            --accent-blue: #0969da;
            --accent-blue-hover: #0353a4;
        }

        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Noto Sans", Helvetica, Arial, sans-serif; 
            background: var(--bg-secondary); 
            color: var(--text-main); 
            display: flex; 
            height: 100vh; 
            justify-content: center; 
            align-items: center; 
            margin: 0; 
            position: relative;
            overflow: hidden;
        }

        /* Efeito de Fundo com Robô / Marca d'água Transparente */
        body::before {
            content: "";
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            /* Pode substituir o URL abaixo por um link direto de uma imagem PNG transparente de robô se desejar */
            background-image: url('https://img.icons8.com/clouds/500/chatbot.png');
            background-repeat: no-repeat;
            background-position: center;
            background-size: 450px;
            opacity: 0.08; /* Deixa a imagem bem suave e transparente no fundo */
            pointer-events: none;
            z-index: 0;
        }

        .auth-card { 
            background: var(--bg-primary); 
            padding: 30px; 
            border-radius: 6px; 
            border: 1px solid var(--border-color); 
            width: 100%; 
            max-width: 400px; 
            box-sizing: border-box;
            position: relative;
            z-index: 1;
            box-shadow: 0 8px 24px rgba(140, 149, 159, 0.2);
        }

        h2 { 
            margin-top: 0; 
            color: var(--text-main); 
            text-align: center; 
            font-size: 20px; 
            font-weight: 600; 
            margin-bottom: 24px;
        }

        label { 
            font-weight: 600; 
            font-size: 12px; 
            color: var(--text-muted); 
            display: block; 
            margin-top: 15px; 
            margin-bottom: 6px;
        }

        input { 
            width: 100%; 
            padding: 8px 12px; 
            border: 1px solid var(--border-color); 
            border-radius: 6px; 
            box-sizing: border-box; 
            font-size: 14px; 
            font-family: inherit; 
            background-color: var(--bg-primary); 
            color: var(--text-main); 
            transition: border-color 0.2s, box-shadow 0.2s; 
        }

        input:focus { 
            outline: none; 
            border-color: var(--accent-blue); 
            box-shadow: 0 0 0 3px rgba(9, 105, 218, 0.3); 
        }

        .btn { 
            width: 100%; 
            background: #2ea44f; 
            color: white; 
            border: 1px solid rgba(27, 31, 36, 0.15); 
            padding: 10px 16px; 
            border-radius: 6px; 
            font-weight: 500; 
            font-size: 14px; 
            cursor: pointer; 
            margin-top: 24px; 
            transition: background 0.2s; 
        }

        .btn:hover { 
            background: #2c974b; 
        }

        .alert { 
            padding: 12px 16px; 
            margin-bottom: 20px; 
            border-radius: 6px; 
            font-weight: 500; 
            font-size: 14px; 
            border: 1px solid transparent; 
        }

        .success { 
            background: #dafbe1; 
            color: #116329; 
            border-color: rgba(27, 111, 49, 0.4); 
        }

        .danger { 
            background: #ffebe9; 
            color: #cf222e; 
            border-color: rgba(207, 34, 46, 0.4); 
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <h2>🤖 Aceder ao Painel</h2>
        <?php echo $msg; ?>
        <form method="POST">
            <label>Utilizador:</label>
            <input type="text" name="username" required>
            
            <label>Senha:</label>
            <input type="password" name="password" required>
            
            <button type="submit" class="btn">Entrar</button>
        </form>
        <p style="text-align:center; font-size:13px; margin-top:20px; color: var(--text-muted);">Não tem uma conta? <a href="registo.php" style="color: var(--accent-blue); text-decoration: none;">Crie uma aqui</a></p>
    </div>
</body>
</html>