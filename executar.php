<?php
session_start();

// CAPTURA DINÂMICA SEGURA: Valida se o utilizador está realmente logado. 
// Se não estiver, redireciona para o login em vez de assumir um ID fixo.
if (!isset($_SESSION['utilizador_id']) || empty($_SESSION['utilizador_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = intval($_SESSION['utilizador_id']);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Disparar Agente Autónomo</title>
    <style>
        :root {
            --bg-primary: #ffffff;
            --bg-secondary: #f6f8fa;
            --border-color: #d0d7de;
            --text-main: #24292f;
            --text-muted: #57606a;
            --accent-blue: #0969da;
            --accent-blue-hover: #0353a4;
            --nav-bg: #24292f;
        }

        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Noto Sans", Helvetica, Arial, sans-serif; background: var(--bg-secondary); color: var(--text-main); margin: 0; padding: 0; }
        
        /* Navbar */
        .navbar { background-color: var(--nav-bg); padding: 0 24px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border-color); height: 60px; }
        .navbar-brand { color: #fff; font-size: 16px; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 8px; }
        .navbar-nav { list-style: none; display: flex; margin: 0; padding: 0; gap: 8px; height: 100%; align-items: center; }
        .navbar-nav li { height: 100%; display: flex; align-items: center; }
        .navbar-nav li a { display: flex; align-items: center; color: #8b949e; padding: 0 12px; height: 100%; text-decoration: none; font-size: 14px; font-weight: 500; transition: color 0.2s; border-bottom: 2px solid transparent; }
        .navbar-nav li a:hover, .navbar-nav li a.active { color: #fff; border-bottom-color: #f78166; }
        
        .container { padding: 32px; max-width: 600px; margin: 50px auto; background: var(--bg-primary); border-radius: 6px; border: 1px solid var(--border-color); text-align: center; }
        h2 { color: var(--text-main); font-size: 20px; font-weight: 600; margin-top: 0; }
        
        .btn-play { background-color: #2ea44f; color: white; border: 1px solid rgba(27, 31, 36, 0.15); padding: 10px 24px; font-size: 16px; font-weight: 500; border-radius: 6px; cursor: pointer; transition: background 0.2s; }
        .btn-play:hover { background-color: #2c974b; }
        
        .status-msg { margin-top: 20px; padding: 12px 16px; border-radius: 6px; font-size: 14px; text-align: left; }
        .success { background-color: #dafbe1; color: #116329; border: 1px solid rgba(27, 111, 49, 0.4); }

        /* Estilos do Overlay de Carregamento Estilo AngoSchool */
        #loading-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(24, 29, 38, 0.92);
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            color: #ffffff;
            font-family: inherit;
        }

        .spinner-container {
            position: relative;
            width: 100px;
            height: 100px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
        }

        .spinner-ring {
            position: absolute;
            width: 100%;
            height: 100%;
            border: 4px solid transparent;
            border-top-color: #2ea44f;
            border-right-color: #0969da;
            border-radius: 50%;
            animation: spin 1.2s linear infinite;
        }

        .spinner-icon {
            font-size: 32px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loading-text {
            font-size: 16px;
            font-weight: 600;
            letter-spacing: 1px;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .loading-bar-wrapper {
            width: 200px;
            height: 4px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 2px;
            margin-top: 15px;
            overflow: hidden;
        }

        .loading-bar-progress {
            width: 40%;
            height: 100%;
            background: #2ea44f;
            border-radius: 2px;
            animation: progress-slide 1.5s ease-in-out infinite alternate;
        }

        @keyframes progress-slide {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(250%); }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <a class="navbar-brand" href="index.php">🤖 Robô Recrutamento</a>
        <ul class="navbar-nav">
            <li><a href="index.php">Painel Principal</a></li>
            <li><a href="perfil.php">Meu Perfil</a></li>
            <li><a href="executar.php" class="active">Iniciar Robô</a></li>
        </ul>
    </nav>

    <!-- Overlay de Carregamento Ativado Diretamente no Clique -->
    <div id="loading-overlay">
        <div class="spinner-container">
            <div class="spinner-ring"></div>
            <div class="spinner-icon">🤖</div>
        </div>
        <div class="loading-text">A INICIAR AGENTE AUTÓNOMO...</div>
        <div class="loading-bar-wrapper">
            <div class="loading-bar-progress"></div>
        </div>
    </div>

    <div class="container">
        <h2>Controlo do Agente Autónomo</h2>
        <p style="color: var(--text-muted); margin-bottom: 24px; font-size: 14px;">Clique no botão abaixo para iniciar a varredura e aplicação automatizada de vagas utilizando o Microsoft Edge com os dados do seu perfil.</p>
        
        <form method="POST" id="form-robo" onsubmit="mostrarLoading()">
            <button type="submit" name="iniciar_robo" class="btn-play">▶ Iniciar Robô Agora</button>
        </form>

        <?php
        if (isset($_POST['iniciar_robo'])) {
            // Envia explicitamente o ID dinâmico da sessão para o script Python através de sys.argv[1]
            $comando = 'cd /d C:\xampp\htdocs\robo_recrutamento && python worker_recrutamento.py ' . $user_id;
            
            pclose(popen("start /B " . $comando, "r")); 

            // Script PHP gera a caixa de sucesso e oculta o overlay instantaneamente
            echo "<div class='status-msg success' style='display:block;'>";
            echo "<strong>[+] Comando enviado!</strong> O robô foi iniciado dinamicamente para o utilizador ID: <strong>{$user_id}</strong>.<br>";
            echo "<a href='monitor.php' target='_blank' style='color: #116329; font-weight:600; text-decoration: underline;'>Clique aqui para abrir o Monitor Live</a> e ver o progresso.";
            echo "</div>";

            echo "<script>
                window.addEventListener('DOMContentLoaded', (event) => {
                    const overlay = document.getElementById('loading-overlay');
                    if(overlay) { overlay.style.display = 'none'; }
                });
            </script>";
        }
        ?>
    </div>

    <script>
        function mostrarLoading() {
            const overlay = document.getElementById('loading-overlay');
            if(overlay) {
                overlay.style.display = 'flex';
            }
        }
    </script>

</body>
</html>