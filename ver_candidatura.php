<?php
$conn = new mysqli("localhost", "root", "", "sistema_recrutamento_db");
if ($conn->connect_error) { die("Conexão falhou: " . $conn->connect_error); }

// Captura o ID do histórico de candidatura
$log_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Busca os dados do histórico cruzando com a vaga correspondente
$query = "SELECT h.*, v.titulo, v.empresa, v.descricao, v.hard_skills_exigidas 
          FROM historico_candidaturas h
          JOIN vagas v ON h.vaga_id = v.id 
          WHERE h.id = $log_id";

$res = $conn->query($query);
if(!$res || $res->num_rows == 0) { die("Registro de candidatura não encontrado."); }
$dados = $res->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Detalhes da Operação #<?php echo $dados['id']; ?></title>
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
        
        .container { padding: 32px 24px; max-width: 800px; margin: 0 auto; }
        .btn-back { display: inline-block; text-decoration: none; color: var(--text-muted); font-size: 14px; font-weight: 500; margin-bottom: 16px; transition: color 0.2s; }
        .btn-back:hover { color: var(--text-main); }
        
        .vaga-card { background: var(--bg-primary); padding: 30px; border-radius: 6px; border: 1px solid var(--border-color); position: relative; }
        
        /* Cores dinâmicas na borda superior baseado no sucesso/erro */
        .border-Submetida { border-top: 5px solid #2ea44f; }
        .border-Ignorada { border-top: 5px solid #9a6700; }
        .border-Falha { border-top: 5px solid #cf222e; }

        .vaga-header h2 { margin: 0 0 8px 0; color: var(--text-main); font-size: 20px; font-weight: 600; }
        .empresa-badge { display: inline-block; background: var(--bg-secondary); color: var(--text-muted); border: 1px solid var(--border-color); padding: 4px 10px; border-radius: 2em; font-size: 12px; font-weight: 500; }
        
        .status-recibo { float: right; padding: 6px 12px; border-radius: 6px; font-weight: 600; font-size: 13px; border: 1px solid transparent; }
        .status-Submetida { background: #dafbe1; color: #116329; border-color: rgba(27, 111, 49, 0.4); }
        .status-Ignorada { background: #fff8c5; color: #9a6700; border-color: rgba(212, 167, 44, 0.4); }
        .status-Falha { background: #ffebe9; color: #cf222e; border-color: rgba(207, 34, 46, 0.4); }

        .seccao-titulo { font-size: 12px; color: var(--text-muted); border-bottom: 1px solid var(--border-color); padding-bottom: 6px; margin-top: 25px; margin-bottom: 12px; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; }
        
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; background: var(--bg-secondary); padding: 16px; border-radius: 6px; border: 1px solid var(--border-color); }
        .info-dado { font-size: 14px; color: var(--text-main); margin-top: 4px; }
        .info-label { font-size: 12px; color: var(--text-muted); text-transform: uppercase; font-weight: 600; }
    </style>
</head>
<body>

    <nav class="navbar">
        <a class="navbar-brand" href="index.php">🤖 Robô Recrutamento</a>
        <ul class="navbar-nav">
            <li><a href="index.php">Painel Principal</a></li>
        </ul>
    </nav>

    <div class="container">
        <a href="index.php" class="btn-back">← Voltar ao Painel</a>
        
        <div class="vaga-card border-<?php echo $dados['status_submissao']; ?>">
            
            <!-- Status no Topo Direito -->
            <div class="status-recibo status-<?php echo $dados['status_submissao']; ?>">
                <?php 
                    if($dados['status_submissao'] == 'Submetida') echo "✓ Candidatura Aceita";
                    elseif($dados['status_submissao'] == 'Ignorada') echo "⚠ Vaga Ignorada";
                    else echo "✕ Falha no Processo";
                ?>
            </div>

            <div class="vaga-header">
                <h2><?php echo htmlspecialchars($dados['titulo']); ?></h2>
                <div class="empresa-badge">💼 <?php echo htmlspecialchars($dados['empresa']); ?></div>
            </div>

            <div class="seccao-titulo">Métricas de Envio do Agente</div>
            <div class="info-grid">
                <div>
                    <div class="info-label">Data/Hora da Execução</div>
                    <div class="info-dado"><strong><?php echo date('d/m/Y H:i:s', strtotime($dados['executado_em'])); ?></strong></div>
                </div>
                <div>
                    <div class="info-label">Aderência Computada (Match)</div>
                    <div class="info-dado"><strong><?php echo number_format($dados['percentual_match'], 0); ?>%</strong></div>
                </div>
            </div>

            <div class="seccao-titulo">Relatório e Notas do Sistema</div>
            <p style="font-size: 14px; line-height: 1.5; color: var(--text-main);">
                <?php 
                    if($dados['status_submissao'] == 'Falha') {
                        echo "<strong>Erro Técnico Detalhado:</strong><br><span style='color:#cf222e; font-family:monospace;'>" . nl2br(htmlspecialchars($dados['detalhes_erro'])) . "</span>";
                    } elseif($dados['status_submissao'] == 'Ignorada') {
                        echo "O agente optou por não submeter o seu perfil porque o nível de match (" . number_format($dados['percentual_match'], 0) . "%) ficou abaixo do limiar mínimo configurado de 50%.";
                    } else {
                        echo "<strong>Sucesso:</strong> O robô preencheu os formulários externos, anexou o seu currículo e confirmou o envio dos dados no portal de recrutamento sem interrupções.";
                    }
                ?>
            </p>

            <div class="seccao-titulo">Link da Vaga Alvo</div>
            <p style="font-size: 14px; margin-bottom: 0;">
                <a href="<?php echo htmlspecialchars($dados['url_vaga']); ?>" target="_blank" style="color: var(--accent-blue); text-decoration: none; word-break: break-all;">
                    🔗 <?php echo htmlspecialchars($dados['url_vaga']); ?>
                </a>
            </p>
        </div>
    </div>

</body>
</html>
<?php $conn->close(); ?>