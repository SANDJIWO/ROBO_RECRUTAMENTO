<?php
require_once 'auth.php';
$conn = new mysqli("localhost", "root", "", "sistema_recrutamento_db");
if ($conn->connect_error) { die("Conexão falhou: " . $conn->connect_error); }

$utilizador_logado_id = $_SESSION['utilizador_id'] ?? $_SESSION['user_id'] ?? 1;

// Busca o perfil e obtém o ID do perfil (perfil_id) correspondente ao utilizador logado
$query_perfil = $conn->prepare("SELECT id, nome_completo, email FROM perfil_candidato WHERE utilizador_id = ?");
$query_perfil->bind_param("i", $utilizador_logado_id);
$query_perfil->execute();
$dados_perfil = $query_perfil->get_result()->fetch_assoc();
$query_perfil->close();

$perfil_id = $dados_perfil['id'] ?? 0;

// Métricas baseadas estritamente no perfil_id da candidata logada
$total_encontradas = $conn->query("SELECT COUNT(DISTINCT h.url_vaga) as total FROM historico_candidaturas h WHERE h.perfil_id = $perfil_id")->fetch_assoc()['total'];
$total_submetidas  = $conn->query("SELECT COUNT(*) as total FROM historico_candidaturas h WHERE h.status_submissao = 'Submetida' AND h.perfil_id = $perfil_id")->fetch_assoc()['total'];
$total_ignoradas   = $conn->query("SELECT COUNT(*) as total FROM historico_candidaturas h WHERE h.status_submissao = 'Ignorada' AND h.perfil_id = $perfil_id")->fetch_assoc()['total'];
$total_falhas      = $conn->query("SELECT COUNT(*) as total FROM historico_candidaturas h WHERE h.status_submissao = 'Falha' AND h.perfil_id = $perfil_id")->fetch_assoc()['total'];

$media_match_res = $conn->query("SELECT AVG(percentual_match) as media FROM historico_candidaturas WHERE status_submissao != 'Falha' AND perfil_id = $perfil_id")->fetch_assoc();
$media_match = $media_match_res['media'] ?? 0;

// Consulta todas as submissões/histórico associadas ao perfil_id da candidata
$query_logs = "SELECT h.*, v.titulo, v.empresa 
               FROM historico_candidaturas h
               JOIN vagas v ON h.vaga_id = v.id 
               WHERE h.status_submissao = 'Submetida' AND h.perfil_id = $perfil_id
               ORDER BY h.executado_em DESC";
$dados_logs = $conn->query($query_logs);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Painel Central do Agente: Dashboard</title>
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
        
        .navbar { background-color: var(--nav-bg); padding: 0 24px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border-color); height: 60px; }
        .navbar-brand { color: #fff; font-size: 16px; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 8px; }
        .navbar-nav { list-style: none; display: flex; margin: 0; padding: 0; gap: 8px; height: 100%; align-items: center; }
        .navbar-nav li { height: 100%; display: flex; align-items: center; }
        .navbar-nav li a { display: flex; align-items: center; color: #8b949e; padding: 0 12px; height: 100%; text-decoration: none; font-size: 14px; font-weight: 500; transition: color 0.2s; border-bottom: 2px solid transparent; }
        .navbar-nav li a:hover, .navbar-nav li a.active { color: #fff; border-bottom-color: #f78166; }
        
        .container { padding: 24px; max-width: 1200px; margin: 0 auto; }
        
        .user-profile-card { background: var(--bg-primary); padding: 16px 20px; border-radius: 6px; border: 1px solid var(--border-color); margin-bottom: 24px; display: flex; align-items: center; }
        .user-profile-info h4 { margin: 0; color: var(--text-main); font-size: 15px; font-weight: 600; }
        .user-profile-info p { margin: 4px 0 0 0; color: var(--text-muted); font-size: 13px; }

        .grid-cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
        .card { background: var(--bg-primary); padding: 16px; border-radius: 6px; border: 1px solid var(--border-color); position: relative; }
        .card::before { content: ""; position: absolute; top: 0; left: 0; bottom: 0; width: 4px; border-top-left-radius: 6px; border-bottom-left-radius: 6px; }
        .card.encontradas::before { background-color: #54aeff; }
        .card.submetidas::before { background-color: #2ea44f; }
        .card.ignoradas::before { background-color: #bf8700; }
        .card.falhas::before { background-color: #cf222e; }
        
        .card h3 { margin: 0; color: var(--text-muted); font-size: 12px; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; }
        .card .numero { font-size: 24px; font-weight: 600; margin: 8px 0 4px 0; color: var(--text-main); }
        .card .detalhe { font-size: 12px; color: var(--text-muted); }
        
        .content-box { background: var(--bg-primary); padding: 20px; border-radius: 6px; border: 1px solid var(--border-color); }
        h2 { margin-top: 0; color: var(--text-main); font-size: 20px; font-weight: 600; }
        h3 { font-size: 16px; font-weight: 600; color: var(--text-main); margin-top: 0; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 12px; font-size: 14px; }
        th, td { padding: 12px 16px; border-bottom: 1px solid var(--border-color); text-align: left; }
        th { background: var(--bg-secondary); color: var(--text-muted); font-weight: 600; font-size: 13px; }
        
        .status { padding: 3px 8px; border-radius: 2em; font-size: 12px; font-weight: 500; display: inline-block; }
        .status-Submetida { background: #dafbe1; color: #116329; border: 1px solid rgba(27, 111, 49, 0.4); }
        .status-Ignorada { background: #fff8c5; color: #9a6700; border: 1px solid rgba(212, 167, 44, 0.4); }
        .status-Falha { background: #ffebe9; color: #cf222e; border: 1px solid rgba(207, 34, 46, 0.4); }

        /* Estilos exatos para as condições de adequação solicitadas */
        .badge-adequada { background: #dafbe1; color: #116329; border: 1px solid rgba(27, 111, 49, 0.4); padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; display: inline-block; }
        .badge-nao-adequada { background: #fff8c5; color: #9a6700; border: 1px solid rgba(212, 167, 44, 0.4); padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; display: inline-block; }

        .btn-view { display: inline-block; background-color: #f3f4f6; color: var(--text-main); border: 1px solid rgba(27, 31, 36, 0.15); text-decoration: none; padding: 4px 10px; font-size: 12px; border-radius: 6px; font-weight: 500; transition: background 0.2s; }
        .btn-view:hover { background-color: #ebecf0; }
        .col-num { font-weight: 500; color: var(--text-muted); width: 30px; text-align: center; }
    </style>
</head>
<body>

    <nav class="navbar">
        <a class="navbar-brand" href="index.php">🤖 Robô Recrutamento</a>
        <ul class="navbar-nav">
            <li><a href="index.php">Painel Principal</a></li>
            <li><a href="perfil.php" class="active">Meu Perfil</a></li>
            <li><a href="executar.php">Iniciar Robô</a></li>
            <li><a href="logout.php" style="color: #ff7b72; font-weight: 600;">Sair (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a></li>
        </ul>
    </nav>

    <div class="container">
        <h2>Dashboard de Performance do Agente</h2>
        <p style="color: var(--text-muted); margin-bottom: 20px; font-size: 14px;">Métricas consolidadas de aderência de perfil e submissões automatizadas.</p>

        <?php if($dados_perfil): ?>
            <div class="user-profile-card">
                <div class="user-profile-info">
                    <h4>👤 <?php echo htmlspecialchars($dados_perfil['nome_completo']); ?></h4>
                    <p>✉️ <?php echo htmlspecialchars($dados_perfil['email']); ?></p>
                </div>
            </div>
        <?php else: ?>
            <div class="user-profile-card" style="border-left: 4px solid #cf222e;">
                <div class="user-profile-info">
                    <h4 style="color: #cf222e;">⚠️ Perfil não encontrado</h4>
                    <p>Não foi encontrado nenhum registo na tabela perfil_candidato associado ao ID de utilizador atual (<?php echo $utilizador_logado_id; ?>).</p>
                </div>
            </div>
        <?php endif; ?>

        <div class="grid-cards">
            <div class="card encontradas">
                <h3>Vagas Mapeadas</h3>
                <div class="numero"><?php echo $total_encontradas; ?></div>
                <div class="detalhe">Média de Match: <?php echo number_format($media_match, 1); ?>%</div>
            </div>
            
            <div class="card submetidas">
                <h3>Candidaturas</h3>
                <div class="numero"><?php echo $total_submetidas; ?></div>
                <div class="detalhe">Perfil enviado com sucesso</div>
            </div>

            <div class="card ignoradas">
                <h3>Ignoradas</h3>
                <div class="numero"><?php echo $total_ignoradas; ?></div>
                <div class="detalhe">Abaixo do critério mínimo (50%)</div>
            </div>

            <div class="card falhas">
                <h3>Erros do Edge</h3>
                <div class="numero"><?php echo $total_falhas; ?></div>
                <div class="detalhe">Falhas de timeout ou seletor</div>
            </div>
        </div>

        <div class="content-box">
            <h3>Histórico de Candidaturas Submetidas</h3>
            <table>
                <thead>
                    <tr>
                        <th class="col-num">#</th>
                        <th>Data/Hora</th>
                        <th>Vaga / Empresa</th>
                        <th>Match (%)</th>
                        <th>Adequação ao Perfil</th>
                        <th>Resultado do Agente</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($dados_logs && $dados_logs->num_rows > 0): ?>
                        <?php 
                        $contador = 1; 
                        while($row = $dados_logs->fetch_assoc()): 
                            $match = (float)$row['percentual_match'];
                        ?>
                            <tr>
                                <td class="col-num"><?php echo $contador++; ?></td>
                                <td><small style="color: var(--text-muted);"><?php echo date('d/m/Y H:i:s', strtotime($row['executado_em'])); ?></small></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['titulo']); ?></strong><br>
                                    <span style="font-size: 12px; color: var(--text-muted);"><?php echo htmlspecialchars($row['empresa']); ?></span>
                                </td>
                                <td><strong><?php echo number_format($match, 0); ?>%</strong></td>
                                <td>
                                    <?php if ($match >= 50): ?>
                                        <span class="badge-adequada">✔ Adequada ao Perfil (>= 50%)</span>
                                    <?php else: ?>
                                        <span class="badge-nao-adequada">não adequada ao Perfil (< 50%)</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status status-<?php echo $row['status_submissao']; ?>">
                                        <?php echo $row['status_submissao']; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="ver_candidatura.php?id=<?php echo $row['id']; ?>" class="btn-view">🔍 Ver Detalhes</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" style="text-align: center; color: var(--text-muted); padding: 24px;">O robô ainda não submeteu nenhuma candidatura associada a este perfil.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
<?php $conn->close(); ?>