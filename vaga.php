<?php
$conn = new mysqli("localhost", "root", "", "sistema_recrutamento_db");
if ($conn->connect_error) { die("Falha na conexão: " . $conn->connect_error); }

$msg = "";
$vaga_edit = null;

// --- OPERAÇÕES DO CRUD (Apontando sempre para vaga.php) ---

// 1. CREATE / UPDATE (Gravar ou Atualizar)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['salvar_vaga'])) {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $titulo = $conn->real_escape_string($_POST['titulo']);
    $empresa = $conn->real_escape_string($_POST['empresa']);
    $descricao = $conn->real_escape_string($_POST['descricao']);
    $hard = $conn->real_escape_string($_POST['hard_skills_exigidas']);
    $soft = $conn->real_escape_string($_POST['soft_skills_exigidas']);
    $status = $conn->real_escape_string($_POST['status_vaga']);

    if ($id > 0) {
        // Update
        $sql = "UPDATE vagas SET titulo='$titulo', empresa='$empresa', descricao='$descricao', hard_skills_exigidas='$hard', soft_skills_exigidas='$soft', status_vaga='$status' WHERE id=$id";
        if($conn->query($sql)) $msg = "<div class='alert success'>Vaga atualizada com sucesso!</div>";
    } else {
        // CORRIGIDO: alterado 'description' para 'descricao' conforme o padrão da tabela
        $sql = "INSERT INTO vagas (titulo, empresa, descricao, hard_skills_exigidas, soft_skills_exigidas, status_vaga) VALUES ('$titulo', '$empresa', '$descricao', '$hard', '$soft', '$status')";
        if($conn->query($sql)) $msg = "<div class='alert success'>Nova vaga publicada com sucesso!</div>";
    }
}

// 2. READ (Carregar dados para Edição no formulário)
if (isset($_GET['editar'])) {
    $id_editar = (int)$_GET['editar'];
    $result = $conn->query("SELECT * FROM vagas WHERE id=$id_editar");
    if ($result->num_rows > 0) {
        $vaga_edit = $result->fetch_assoc();
    }
}

// 3. DELETE (Eliminar vaga)
if (isset($_GET['eliminar'])) {
    $id_eliminar = (int)$_GET['eliminar'];
    if($conn->query("DELETE FROM vagas WHERE id=$id_eliminar")) {
        $msg = "<div class='alert danger'>Vaga eliminada do sistema!</div>";
    }
}

// 4. READ (Listagem Geral)
$vagas_listagem = $conn->query("SELECT * FROM vagas ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Painel de Gestão de Vagas - CRUD</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; color: #333; margin: 0; padding: 0; }
        
        /* Estilos do Menu de Navegação Global */
        .navbar { background-color: #2c3e50; padding: 0 20px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .navbar-brand { color: #fff; font-size: 18px; font-weight: bold; text-decoration: none; }
        .navbar-nav { list-style: none; display: flex; margin: 0; padding: 0; }
        .navbar-nav li a { display: block; color: #adb5bd; padding: 20px 15px; text-decoration: none; font-size: 14px; transition: all 0.3s; }
        .navbar-nav li a:hover, .navbar-nav li a.active { color: #fff; background-color: #1a252f; border-bottom: 3px solid #28a745; }

        .wrapper { padding: 30px; max-width: 1300px; margin: 0 auto; }
        .container { display: flex; gap: 30px; }
        .form-section { flex: 1; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); height: fit-content; border-top: 4px solid #28a745; }
        .list-section { flex: 1.6; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border-top: 4px solid #007bff; }
        
        h2 { color: #2c3e50; margin-bottom: 25px; }
        h3 { padding-bottom: 8px; color: #2c3e50; margin-top: 0; border-bottom: 1px solid #dee2e6; }
        
        label { font-weight: bold; font-size: 13px; color: #495057; display: block; margin-top: 12px; }
        input[type="text"], textarea, select { width: 100%; padding: 10px; margin: 6px 0 12px 0; border: 1px solid #ced4da; border-radius: 6px; box-sizing: border-box; font-size: 14px; }
        input[type="text"]:focus, textarea:focus, select:focus { border-color: #80bdff; outline: 0; box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25); }
        
        .btn { display: inline-block; background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; font-size: 14px; font-weight: bold; transition: background 0.2s; }
        .btn:hover { background: #218838; }
        .btn-secondary { background: #6c757d; }
        .btn-secondary:hover { background: #5a6268; }
        .btn-danger { background: #dc3545; padding: 6px 12px; font-size: 12px; }
        .btn-danger:hover { background: #bd2130; }
        .btn-edit { background: #007bff; padding: 6px 12px; font-size: 12px; }
        .btn-edit:hover { background: #0069d9; }
        
        .alert { padding: 12px; margin-bottom: 20px; border-radius: 6px; font-weight: bold; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6; }
        th { background-color: #f8f9fa; color: #495057; }
        
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .badge-aberta { background: #d4edda; color: #155724; }
        .badge-fechada { background: #f8d7da; color: #721c24; }
        .skill-tag { background: #eef2f7; color: #475569; padding: 2px 6px; border-radius: 4px; font-family: monospace; font-size: 13px; border: 1px solid #cbd5e1; }
    </style>
</head>
<body>

    <!-- MENÚ SUPERIOR GLOBAL -->
    <nav class="navbar">
        <a class="navbar-brand" href="index.php">🤖 Robô Recrutamento</a>
        <ul class="navbar-nav">
            <li><a href="index.php">Painel Principal</a></li>
            <li><a href="perfil.php">Meu Perfil</a></li>
            <li><a href="vaga.php" class="active">Gestão de Vagas (CRUD)</a></li>
            <li><a href="executar.php">Iniciar Robô</a></li>
            <li><a href="monitor.php" target="_blank">Monitor Live</a></li>
        </ul>
    </nav>

    <div class="wrapper">
        <h2>Módulo Administrativo: Gestão de Vagas Automatizadas</h2>
        
        <?php echo $msg; ?>
        
        <div class="container">
            <!-- FORMULÁRIO: Inserção e Edição (Apontando para vaga.php) -->
            <div class="form-section">
                <h3><?php echo $vaga_edit ? "Editar Vaga #".$vaga_edit['id'] : "Publicar Nova Vaga"; ?></h3>
                <form method="POST" action="vaga.php">
                    <?php if($vaga_edit): ?>
                        <input type="hidden" name="id" value="<?php echo $vaga_edit['id']; ?>">
                    <?php endif; ?>

                    <label>Título do Cargo:</label>
                    <input type="text" name="titulo" value="<?php echo $vaga_edit ? htmlspecialchars($vaga_edit['titulo']) : ''; ?>" required placeholder="Ex: Engenheiro de Software">

                    <label>Empresa Ofertante:</label>
                    <input type="text" name="empresa" value="<?php echo $vaga_edit ? htmlspecialchars($vaga_edit['empresa']) : ''; ?>" required placeholder="Ex: TechAngola">

                    <label>Descrição da Função:</label>
                    <textarea name="descricao" rows="4" required placeholder="Escreva os detalhes sobre as responsabilidades..."><?php echo $vaga_edit ? htmlspecialchars($vaga_edit['descricao']) : ''; ?></textarea>

                    <label>Hard Skills Exigidas (Separadas por vírgula):</label>
                    <input type="text" name="hard_skills_exigidas" value="<?php echo $vaga_edit ? htmlspecialchars($vaga_edit['hard_skills_exigidas']) : ''; ?>" required placeholder="PHP, MySQL, Docker">

                    <label>Soft Skills Recomendadas (Separadas por vírgula):</label>
                    <input type="text" name="soft_skills_exigidas" value="<?php echo $vaga_edit ? htmlspecialchars($vaga_edit['soft_skills_exigidas']) : ''; ?>" placeholder="Autonomia, Adaptabilidade">

                    <label>Status Operacional:</label>
                    <select name="status_vaga">
                        <option value="Aberta" <?php echo ($vaga_edit && $vaga_edit['status_vaga'] == 'Aberta') ? 'selected' : ''; ?>>Aberta</option>
                        <option value="Fechada" <?php echo ($vaga_edit && $vaga_edit['status_vaga'] == 'Fechada') ? 'selected' : ''; ?>>Fechada</option>
                    </select>

                    <div style="margin-top: 20px;">
                        <button type="submit" name="salvar_vaga" class="btn">Salvar Registro</button>
                        <?php if($vaga_edit): ?>
                            <a href="vaga.php" class="btn btn-secondary">Cancelar</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- TABELA LISTAGEM: Visualização e Ações (Apontando para vaga.php) -->
            <div class="list-section">
                <h3>Vagas Ativas no Sistema</h3>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 40px;">ID</th>
                            <th>Vaga / Empresa</th>
                            <th>Hard Skills</th>
                            <th style="width: 80px;">Status</th>
                            <th style="width: 130px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($vagas_listagem && $vagas_listagem->num_rows > 0): ?>
                            <?php while($row = $vagas_listagem->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo $row['id']; ?></strong></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['titulo']); ?></strong><br>
                                        <small style="color:#666; font-size: 12px;">💼 <?php echo htmlspecialchars($row['empresa']); ?></small>
                                    </td>
                                    <td>
                                        <span class="skill-tag"><?php echo htmlspecialchars($row['hard_skills_exigidas']); ?></span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo strtolower($row['status_vaga']); ?>">
                                            <?php echo $row['status_vaga']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="vaga.php?editar=<?php echo $row['id']; ?>" class="btn btn-edit">Editar</a>
                                        <a href="vaga.php?eliminar=<?php echo $row['id']; ?>" class="btn btn-danger" onclick="return confirm('Deseja realmente eliminar esta vaga permanentemente?')">Excluir</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align:center; color: #999;">Nenhuma vaga cadastrada até o momento.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>