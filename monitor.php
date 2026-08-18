<?php
// Lógica para ler o log via AJAX
if (isset($_GET['ajax'])) {
    $logfile = 'robo_recrutamento.log';
    if (file_exists($logfile)) {
        // Lê as últimas 50 linhas do arquivo de log
        $file = file($logfile);
        $lines = array_slice($file, -50);
        echo implode("", $lines);
    } else {
        echo "[*] Nenhuma atividade registrada no log ainda.";
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Painel de Controle: Monitor do Agente</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; background: #121212; color: #00ff00; margin: 20px; }
        .console { background: #000; border: 2px solid #333; padding: 20px; border-radius: 6px; height: 500px; overflow-y: scroll; white-space: pre-wrap; box-shadow: 0 4px 10px rgba(0,0,0,0.5); }
        h2 { font-family: Arial, sans-serif; color: #fff; margin-bottom: 5px; }
        .status { font-family: Arial, sans-serif; font-size: 13px; color: #aaa; margin-bottom: 15px; }
        .badge-live { background: #dc3545; color: white; padding: 2px 6px; border-radius: 3px; font-weight: bold; animation: pisca 1.5s infinite; }
        @keyframes pisca { 0% { opacity: 0.3; } 50% { opacity: 1; } 100% { opacity: 0.3; } }
    </style>
</head>
<body>

    <h2>Terminal de Monitoramento do Agente Autónomo</h2>
    <div class="status"><span class="badge-live">LIVE</span> Atualizando a cada 2 segundos via WebSocket/Polling...</div>

    <div class="console" id="logConsole">Carregando logs do sistema...</div>

    <script>
        function atualizarLogs() {
            fetch('monitor.php?ajax=1')
                .then(response => response.text())
                .then(data => {
                    const consoleDiv = document.getElementById('logConsole');
                    
                    // Controla o scroll para ficar sempre embaixo se o usuário não subiu manualmente
                    const totalScroll = consoleDiv.scrollHeight - consoleDiv.clientHeight;
                    const estaNoFinal = consoleDiv.scrollTop >= totalScroll - 50;

                    consoleDiv.innerText = data;

                    if (estaNoFinal) {
                        consoleDiv.scrollTop = consoleDiv.scrollHeight;
                    }
                });
        }

        // Executa o monitoramento contínuo
        setInterval(atualizarLogs, 2000);
        window.onload = atualizarLogs;
    </script>
</body>
</html>