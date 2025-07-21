<?php
require_once 'config.php';
if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
    header('Location: login.php');
    exit;
}

// Processar a seleção do grupo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['grupo_id'])) {
    $grupo_id_selecionado = $_POST['grupo_id'];

    // Busca o nome do grupo no banco de dados usando o ID
    $grupo_selecionado = obterGrupoPorId($grupo_id_selecionado);

    if ($grupo_selecionado) {
        // Define as variáveis de sessão com os dados corretos do banco
        $_SESSION['grupo_id'] = $grupo_selecionado['id'];
        $_SESSION['grupo_nome'] = $grupo_selecionado['nome'];

        // Limpa a seleção de categoria anterior para forçar uma nova escolha
        unset($_SESSION['categoria_id']);
        unset($_SESSION['categoria_nome']);

        header('Location: selecionar_categoria.php');
        exit;
    }
}

$grupos = obterGrupos();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Selecionar Grupo - Prêmio MPT</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="container">
        <div class="selecao-container">
            <div class="flex">
                <img src="img/logo.png" alt="Logo do Prêmio MPT" class="logo-header">
                <h3>Bem vindo(a), <?php echo $_SESSION['jurado_nome'] ?></h3>
                <p class="subtitle">Escolha o grupo que deseja avaliar.</p>
            </div>

            <form method="post" action="">
                <div class="opcoes-grid">
                    <?php
                    $icones = [
                        1 => 'fa-child',
                        2 => 'fa-briefcase',
                        3 => 'fa-helmet-safety'
                    ];

                    foreach ($grupos as $grupo):
                        $icone_classe = isset($icones[$grupo['id']]) ? $icones[$grupo['id']] : 'fa-star';
                    ?>
                        <div class="opcao-selecao">
                            <input type="radio" id="grupo_<?php echo $grupo['id']; ?>" name="grupo_id" value="<?php echo $grupo['id']; ?>" required>
                            <label for="grupo_<?php echo $grupo['id']; ?>">
                                <div class="opcao-icone">
                                    <i class="fas <?php echo $icone_classe; ?>"></i>
                                </div>
                                <div class="opcao-info">
                                    <h4><?php echo htmlspecialchars($grupo['nome']); ?></h4>
                                    <p><?php echo htmlspecialchars($grupo['descricao']); ?></p>
                                </div>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="botoes-navegacao">
                    <button type="submit" class="btn-continuar">
                        <i class="fas fa-arrow-right"></i> Prosseguir para Categorias
                    </button>
                    <a href="login.php?logout=1" class="btn-acao erro"><i class="fas fa-sign-out-alt"></i> Sair</a>
                </div>
            </form>
        </div>
    </div>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</body>

</html>