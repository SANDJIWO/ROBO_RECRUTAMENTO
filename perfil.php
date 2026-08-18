<?php
require_once 'auth.php'; // Garante o bloqueio de segurança e inicia a sessão

$conn = new mysqli("localhost", "root", "", "sistema_recrutamento_db");
if ($conn->connect_error) { die("Falha na conexão: " . $conn->connect_error); }

$user_id = $_SESSION['user_id'];
$msg = "";

// Controla se exibe o formulário ou a ficha de leitura
// Se não tem perfil, entra obrigatoriamente em modo de edição
$modo_edicao = isset($_GET['action']) && $_GET['action'] == 'editar' ? true : !$has_profile;

// Exibe aviso se o utilizador foi redirecionado forçadamente
if (isset($_GET['status']) && $_GET['status'] == 'obrigatorio') {
    $msg = "<div class='alert danger'>⚠️ Atenção: Para visualizar vagas e utilizar o Robô, necessita criar o seu perfil primeiro!</div>";
}

// --- SALVAR OU ATUALIZAR O PERFIL DO UTILIZADOR LOGADO ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['salvar_perfil'])) {
    $nome = $conn->real_escape_string($_POST['nome_completo']);
    $cidade = $conn->real_escape_string($_POST['cidade_pais']);
    $email = $conn->real_escape_string($_POST['email']);
    $tel = $conn->real_escape_string($_POST['telefone']);
    $port = $conn->real_escape_string($_POST['portfolio_url']);
    $formacao = $conn->real_escape_string($_POST['formacao_academica']);
    $exp = $conn->real_escape_string($_POST['experiencia_laboral']);
    $conq = $conn->real_escape_string($_POST['principais_conquistas']);
    $hard = $conn->real_escape_string($_POST['hard_skills']);
    $cert = $conn->real_escape_string($_POST['certificacoes']);
    $soft = $conn->real_escape_string($_POST['soft_skills']);
    $valores = $conn->real_escape_string($_POST['valores_estilo']);
    $obj = $conn->real_escape_string($_POST['objetivo_profissional']);

    if ($has_profile) {
        $sql = "UPDATE perfil_candidato SET 
                nome_completo='$nome', cidade_pais='$cidade', email='$email', telefone='$tel', portfolio_url='$port',
                formacao_academica='$formacao', experiencia_laboral='$exp', principais_conquistas='$conq',
                hard_skills='$hard', certificacoes='$cert', soft_skills='$soft', valores_estilo='$valores', objetivo_profissional='$obj' 
                WHERE utilizador_id=$user_id";
        if($conn->query($sql)) {
            $msg = "<div class='alert success'>O seu perfil foi atualizado com sucesso!</div>";
            $modo_edicao = false; // Volta para o modo visualização
        }
    } else {
        $sql = "INSERT INTO perfil_candidato (utilizador_id, nome_completo, cidade_pais, email, telefone, portfolio_url, formacao_academica, experiencia_laboral, principais_conquistas, hard_skills, certificacoes, soft_skills, valores_estilo, objetivo_profissional) 
                VALUES ($user_id, '$nome', '$cidade', '$email', '$tel', '$port', '$formacao', '$exp', '$conq', '$hard', '$cert', '$soft', '$valores', '$obj')";
        if($conn->query($sql)) {
            header("Location: index.php?bemvindo=1");
            exit;
        }
    }
    $has_profile = true;
}

// --- BUSCA O PERFIL COMPLETO DO UTILIZADOR LOGADO ---
$perfil_query = $conn->query("SELECT * FROM perfil_candidato WHERE utilizador_id = $user_id");
$perfil_data = $perfil_query->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>O Meu Perfil Profissional</title>
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
        
        .container { padding: 24px; max-width: 950px; margin: 0 auto; }
        .card { background: var(--bg-primary); padding: 30px; border-radius: 6px; border: 1px solid var(--border-color); position: relative; }
        
        .header-box { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 15px; margin-bottom: 20px; }
        h2 { color: var(--text-main); margin: 0; font-size: 20px; font-weight: 600; }
        
        .form-group-title { background: var(--bg-secondary); padding: 10px 14px; margin: 25px 0 15px 0; border-left: 4px solid var(--accent-blue); font-weight: 600; font-size: 13px; color: var(--text-main); border-top: 1px solid var(--border-color); border-right: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); border-radius: 0 6px 6px 0; }
        
        /* Grid Layout */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .form-full { grid-column: span 2; }
        
        label { font-weight: 600; font-size: 12px; color: var(--text-muted); display: block; margin-bottom: 6px; }
        input[type="text"], input[type="email"], textarea { width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; box-sizing: border-box; font-size: 14px; font-family: inherit; background-color: var(--bg-primary); color: var(--text-main); transition: border-color 0.2s, box-shadow 0.2s; }
        input[type="text"]:focus, input[type="email"]:focus, textarea:focus { outline: none; border-color: var(--accent-blue); box-shadow: 0 0 0 3px rgba(9, 105, 218, 0.3); }
        
        /* Estilos do Modo de Visualização */
        .view-data { padding: 10px 12px; background: var(--bg-secondary); border-radius: 6px; border: 1px solid var(--border-color); min-height: 20px; font-size: 14px; color: var(--text-main); white-space: pre-line; }
        .skill-badge { display: inline-block; background: #ddf4ff; color: #0969da; border: 1px solid rgba(54, 144, 234, 0.35); padding: 3px 10px; border-radius: 2em; font-size: 12px; font-weight: 500; margin: 3px; }
        
        /* Botões */
        .btn { background: #2ea44f; color: white; padding: 8px 16px; border: 1px solid rgba(27, 31, 36, 0.15); border-radius: 6px; cursor: pointer; font-weight: 500; font-size: 14px; text-decoration: none; display: inline-block; transition: background 0.2s; }
        .btn:hover { background: #2c974b; }
        .btn-secondary { background: #f3f4f6; color: var(--text-main); border: 1px solid rgba(27, 31, 36, 0.15); margin-left: 8px; }
        .btn-secondary:hover { background: #ebecf0; }
        .btn-edit-top { background: #f3f4f6; color: var(--text-main); border: 1px solid rgba(27, 31, 36, 0.15); padding: 5px 12px; font-size: 13px; font-weight: 500; }
        .btn-edit-top:hover { background: #ebecf0; }
        
        .alert { padding: 12px 16px; margin-bottom: 20px; border-radius: 6px; font-weight: 500; font-size: 14px; border: 1px solid transparent; }
        .success { background: #dafbe1; color: #116329; border-color: rgba(27, 111, 49, 0.4); }
        .danger { background: #ffebe9; color: #cf222e; border-color: rgba(207, 34, 46, 0.4); }
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
        <?php echo $msg; ?>
        
        <div class="card">
            <div class="header-box">
                <h2><?php echo $modo_edicao ? "✏️ Editar Perfil do Candidato" : "📋 Perfil Profissional Cadastrado"; ?></h2>
                <?php if(!$modo_edicao && $has_profile): ?>
                    <a href="perfil.php?action=editar" class="btn btn-edit-top">⚙️ Alterar Dados</a>
                <?php endif; ?>
            </div>

            <?php if ($modo_edicao): ?>
                <!-- ================= MODO DE EDIÇÃO (FORMULÁRIO) ================= -->
                <form method="POST" action="perfil.php">
                    <div class="form-group-title">1. Dados Pessoais e de Contacto</div>
                    <div class="form-grid">
                        <div>
                            <label>Nome Completo:</label>
                            <input type="text" name="nome_completo" value="<?php echo $perfil_data ? htmlspecialchars($perfil_data['nome_completo']) : ''; ?>" required>
                        </div>
                        <div>
                            <label>E-mail Profissional:</label>
                            <input type="email" name="email" value="<?php echo $perfil_data ? htmlspecialchars($perfil_data['email']) : ''; ?>" required>
                        </div>
                        <div>
                            <label>Cidade e País:</label>
                            <input type="text" name="cidade_pais" value="<?php echo $perfil_data ? htmlspecialchars($perfil_data['cidade_pais']) : ''; ?>">
                        </div>
                        <div>
                            <label>Telefone ou WhatsApp:</label>
                            <input type="text" name="telefone" value="<?php echo $perfil_data ? htmlspecialchars($perfil_data['telefone']) : ''; ?>">
                        </div>
                        <div class="form-full">
                            <label>Link para LinkedIn ou Portfólio (Opcional):</label>
                            <input type="text" name="portfolio_url" value="<?php echo $perfil_data ? htmlspecialchars($perfil_data['portfolio_url']) : ''; ?>">
                        </div>
                    </div>

                    <div class="form-group-title">2. Resumo da Experiência Profissional</div>
                    <div class="form-grid">
                        <div class="form-full">
                            <label>Formação Académica (Curso, Instituição, Ano):</label>
                            <textarea name="formacao_academica" rows="3"><?php echo $perfil_data ? htmlspecialchars($perfil_data['formacao_academica']) : ''; ?></textarea>
                        </div>
                        <div class="form-full">
                            <label>Experiência Laboral Relevante:</label>
                            <textarea name="experiencia_laboral" rows="4"><?php echo $perfil_data ? htmlspecialchars($perfil_data['experiencia_laboral']) : ''; ?></textarea>
                        </div>
                        <div class="form-full">
                            <label>Principais Conquistas ou Resultados Alcançados:</label>
                            <textarea name="principais_conquistas" rows="3"><?php echo $perfil_data ? htmlspecialchars($perfil_data['principais_conquistas']) : ''; ?></textarea>
                        </div>
                    </div>

                    <div class="form-group-title">3 & 4. Competências e Qualificações</div>
                    <div class="form-grid">
                        <div class="form-full">
                            <label>Competências Técnicas / Hard Skills (Separadas por vírgula):</label>
                            <input type="text" name="hard_skills" value="<?php echo $perfil_data ? htmlspecialchars($perfil_data['hard_skills']) : ''; ?>">
                        </div>
                        <div class="form-full">
                            <label>Certificações e Treinamentos Relevantes:</label>
                            <textarea name="certificacoes" rows="2"><?php echo $perfil_data ? htmlspecialchars($perfil_data['certificacoes']) : ''; ?></textarea>
                        </div>
                        <div class="form-full">
                            <label>Competências Comportamentais / Soft Skills (Separadas por vírgula):</label>
                            <input type="text" name="soft_skills" value="<?php echo $perfil_data ? htmlspecialchars($perfil_data['soft_skills']) : ''; ?>">
                        </div>
                    </div>

                    <div class="form-group-title">5 & 6. Alinhamento Organizacional e Objetivo</div>
                    <div class="form-grid">
                        <div class="form-full">
                            <label>Valores e Estilo de Trabalho Compatíveis:</label>
                            <textarea name="valores_estilo" rows="3"><?php echo $perfil_data ? htmlspecialchars($perfil_data['valores_estilo']) : ''; ?></textarea>
                        </div>
                        <div class="form-full">
                            <label>Objetivo Profissional (Frase curta):</label>
                            <input type="text" name="objetivo_profissional" value="<?php echo $perfil_data ? htmlspecialchars($perfil_data['objetivo_profissional']) : ''; ?>">
                        </div>
                    </div>

                    <div style="margin-top: 30px; border-top: 1px solid var(--border-color); padding-top: 20px;">
                        <button type="submit" name="salvar_perfil" class="btn">💾 Gravar Alterações</button>
                        <?php if ($has_profile): ?>
                            <a href="perfil.php" class="btn btn-secondary">Cancelar</a>
                        <?php endif; ?>
                    </div>
                </form>

            <?php else: ?>
                <!-- ================= MODO DE VISUALIZAÇÃO (READ) ================= -->
                <div class="form-group-title">1. Dados Pessoais e de Contacto</div>
                <div class="form-grid">
                    <div>
                        <label>Nome Completo:</label>
                        <div class="view-data"><?php echo htmlspecialchars($perfil_data['nome_completo']); ?></div>
                    </div>
                    <div>
                        <label>E-mail Profissional:</label>
                        <div class="view-data"><?php echo htmlspecialchars($perfil_data['email']); ?></div>
                    </div>
                    <div>
                        <label>Cidade e País:</label>
                        <div class="view-data"><?php echo htmlspecialchars($perfil_data['cidade_pais'] ?: 'Não Informado'); ?></div>
                    </div>
                    <div>
                        <label>Telefone ou WhatsApp:</label>
                        <div class="view-data"><?php echo htmlspecialchars($perfil_data['telefone'] ?: 'Não Informado'); ?></div>
                    </div>
                    <div class="form-full">
                        <label>Link para LinkedIn ou Portfólio:</label>
                        <div class="view-data">
                            <?php if($perfil_data['portfolio_url']): ?>
                                <a href="<?php echo htmlspecialchars($perfil_data['portfolio_url']); ?>" target="_blank" style="color: var(--accent-blue); text-decoration: none;"><?php echo htmlspecialchars($perfil_data['portfolio_url']); ?></a>
                            <?php else: echo 'Não Informado'; endif; ?>
                        </div>
                    </div>
                </div>

                <div class="form-group-title">2. Resumo da Experiência Profissional</div>
                <div class="form-grid">
                    <div class="form-full">
                        <label>Formação Académica:</label>
                        <div class="view-data"><?php echo htmlspecialchars($perfil_data['formacao_academica'] ?: 'Nenhuma listada.'); ?></div>
                    </div>
                    <div class="form-full">
                        <label>Experiência Laboral Relevante:</label>
                        <div class="view-data"><?php echo htmlspecialchars($perfil_data['experiencia_laboral'] ?: 'Nenhuma listada.'); ?></div>
                    </div>
                    <div class="form-full">
                        <label>Principais Conquistas ou Resultados:</label>
                        <div class="view-data"><?php echo htmlspecialchars($perfil_data['principais_conquistas'] ?: 'Nenhuma listada.'); ?></div>
                    </div>
                </div>

                <div class="form-group-title">3 & 4. Competências e Qualificações</div>
                <div class="form-grid">
                    <div class="form-full">
                        <label>Competências Técnicas / Hard Skills:</label>
                        <div class="view-data">
                            <?php 
                            if(!empty($perfil_data['hard_skills'])) {
                                $skills = explode(',', $perfil_data['hard_skills']);
                                foreach($skills as $skill) {
                                    echo "<span class='skill-badge'>" . trim(htmlspecialchars($skill)) . "</span>";
                                }
                            } else { echo 'Nenhuma informada.'; }
                            ?>
                        </div>
                    </div>
                    <div class="form-full">
                        <label>Certificações e Treinamentos:</label>
                        <div class="view-data"><?php echo htmlspecialchars($perfil_data['certificacoes'] ?: 'Nenhuma informada.'); ?></div>
                    </div>
                    <div class="form-full">
                        <label>Competências Comportamentais / Soft Skills:</label>
                        <div class="view-data">
                            <?php 
                            if(!empty($perfil_data['soft_skills'])) {
                                $skills = explode(',', $perfil_data['soft_skills']);
                                foreach($skills as $skill) {
                                    echo "<span class='skill-badge' style='background: #fff8c5; color: #9a6700; border-color: rgba(212, 167, 44, 0.4);'>" . trim(htmlspecialchars($skill)) . "</span>";
                                }
                            } else { echo 'Nenhuma informada.'; }
                            ?>
                        </div>
                    </div>
                </div>

                <div class="form-group-title">5 & 6. Alinhamento Organizacional e Objetivo</div>
                <div class="form-grid">
                    <div class="form-full">
                        <label>Valores e Estilo de Trabalho Compatíveis:</label>
                        <div class="view-data"><?php echo htmlspecialchars($perfil_data['valores_estilo'] ?: 'Não preenchido.'); ?></div>
                    </div>
                    <div class="form-full">
                        <label>Objetivo Profissional:</label>
                        <div class="view-data" style="font-weight: 600; color: var(--accent-blue);"><?php echo htmlspecialchars($perfil_data['objetivo_profissional'] ?: 'Não definido.'); ?></div>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>