<?php
session_start();
$conn = new mysqli("localhost", "root", "", "sistema_recrutamento_db");
if ($conn->connect_error) { die("Conexão falhou: " . $conn->connect_error); }

$vaga_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// 1. Prioriza o perfil_id passado via GET pelo robô Python; se não houver, usa o da sessão
if (isset($_GET['perfil_id']) && !empty($_GET['perfil_id'])) {
    $perfil_id = (int)$_GET['perfil_id'];
} else {
    $user_id = isset($_SESSION['utilizador_id']) ? intval($_SESSION['utilizador_id']) : 0;
    if ($user_id > 0) {
        $res_perfil = $conn->query("SELECT id FROM perfil_candidato WHERE utilizador_id = $user_id");
        if ($res_perfil && $res_perfil->num_rows > 0) {
            $perfil_id = $res_perfil->fetch_assoc()['id'];
        } else {
            $perfil_id = 1;
        }
    } else {
        $perfil_id = 1;
    }
}

// Carrega os dados da vaga
$res = $conn->query("SELECT * FROM vagas WHERE id = $vaga_id");
if($res->num_rows == 0) { die("Vaga inexistente ou removida do sistema."); }
$vaga = $res->fetch_assoc();

// --- VERIFICAÇÃO DE DUPLICIDADE INDIVIDUAL POR PERFIL ---
$check_candidatura = $conn->query("SELECT id FROM historico_candidaturas WHERE vaga_id = $vaga_id AND perfil_id = $perfil_id AND status_submissao = 'Submetida'");
$ja_candidatado = ($check_candidatura && $check_candidatura->num_rows > 0);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($vaga['titulo']); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow p-4">
            <h2><?php echo htmlspecialchars($vaga['titulo']); ?></h2>
            <p class="text-muted"><?php echo nl2br(htmlspecialchars($vaga['descricao'])); ?></p>
            
          <hr>
            <h4>Requisitos da Vaga (Hard Skills)</h4>
            <!-- Elemento crucial mapeado pelo robô Selenium -->
            <ul id="requisitos" class="mb-3">
                <?php 
                if (!empty($vaga['hard_skills_exigidas'])) {
                    $reqs = explode(',', $vaga['hard_skills_exigidas']);
                    foreach($reqs as $r) {
                        echo "<li>" . trim(htmlspecialchars($r)) . "</li>";
                    }
                } else {
                    echo "<li>Nenhuma hard skill cadastrada.</li>";
                }
                ?>
            </ul>

            <h5 class="text-muted">Soft Skills Exigidas</h5>
            <ul class="mb-4">
                <?php 
                if (!empty($vaga['soft_skills_exigidas'])) {
                    $softs = explode(',', $vaga['soft_skills_exigidas']);
                    foreach($softs as $s) {
                        echo "<li>" . trim(htmlspecialchars($s)) . "</li>";
                    }
                } else {
                    echo "<li>Nenhuma soft skill cadastrada.</li>";
                }
                ?>
            </ul>

            <?php if (!$ja_candidatado): ?>
                <button id="btnCandidatar" class="btn btn-primary btn-lg">Candidatar-me Automaticamente</button>
            <?php else: ?>
                <div class="alert alert-success">Candidatura já submetida para esta vaga por este perfil.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>