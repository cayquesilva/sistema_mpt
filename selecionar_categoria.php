<?php
require_once 'config.php';
// Verifica se o grupo foi selecionado
if (!isset($_SESSION['grupo_id'])) {
    header('Location: selecionar_grupo.php');
    exit;
}

// Processar a seleção da categoria
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['categoria_id'])) {
    $categoria_id_selecionada = $_POST['categoria_id'];

    // Busca o nome da categoria no banco de dados usando o ID
    $categoria_selecionada = obterCategoriaPorId($categoria_id_selecionada);

    if ($categoria_selecionada) {
        // Define as variáveis de sessão com os dados corretos
        $_SESSION['categoria_id'] = $categoria_selecionada['id'];
        $_SESSION['categoria_nome'] = $categoria_selecionada['nome'];

        header('Location: index.php');
        exit;
    }
}

$categorias = obterCategorias();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Selecionar Categoria - Prêmio MPT</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="container">
        <div class="selecao-container">
            <div class="flex">
                <img src="img/logo.png" alt="Logo do Prêmio MPT" class="logo-header">
                <h3>Bem vindo(a), <?php echo $_SESSION['jurado_nome'] ?></h3>
                <p class="subtitle">Agora, escolha o tipo de trabalho que deseja avaliar para o grupo selecionado.</p>
            </div>

            <form method="post" action="">
                <div class="opcoes-grid-categorias">
                    <?php
                    $icones_cat = [
                        'Conto'   => 'fa-book-open',
                        'Poesia'  => 'fa-feather-pointed',
                        'Música'  => 'fa-music',
                        'Desenho' => 'fa-palette'
                    ];

                    foreach ($categorias as $categoria):
                        $icone_classe = $icones_cat[$categoria['nome']] ?? 'fa-star';
                    ?>
                        <div class="opcao-selecao">
                            <input type="radio" id="cat_<?php echo $categoria['id']; ?>" name="categoria_id" value="<?php echo $categoria['id']; ?>" required>
                            <label for="cat_<?php echo $categoria['id']; ?>">
                                <div class="opcao-icone">
                                    <i class="fas <?php echo $icone_classe; ?>"></i>
                                </div>
                                <div class="opcao-info">
                                    <h4><?php echo htmlspecialchars($categoria['nome']); ?></h4>
                                </div>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="botoes-navegacao">
                    <a href="selecionar_grupo.php" class="btn-acao neutro">
                        <i class="fas fa-arrow-left"></i> Trocar Grupo
                    </a>
                    <button type="submit" class="btn-continuar">
                        <i class="fas fa-list-check"></i> Ver Trabalhos
                    </button>
                    <a href="login.php?logout=1" class="btn-acao erro"><i class="fas fa-sign-out-alt"></i> Sair</a>
                </div>
            </form>
        </div>
    </div>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</body>

</html>