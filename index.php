<?php
require_once 'config.php';
// Validar se o fluxo foi seguido
if (!isset($_SESSION['grupo_id']) || !isset($_SESSION['categoria_id'])) {
    header('Location: selecionar_grupo.php');
    exit;
}

// A função agora precisa do ID do jurado
$trabalhos = obterTrabalhos($_SESSION['grupo_id'], $_SESSION['categoria_id'], $_SESSION['jurado_id']);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Selecionar Candidato - Prêmio MPT</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="container">
        <div class="pagina-header">
            <img src="img/logo.png" alt="Logo do Prêmio MPT" class="logo-header">

            <div class="pagina-titulo">
                <h1>Bem vindo(a), <?php echo $_SESSION['jurado_nome'] ?></h1>
                <h3>Trabalhos para Avaliação</h3>
                <p>
                    <strong>Grupo:</strong> <?php echo htmlspecialchars($_SESSION['grupo_nome']); ?> /
                    <strong>Categoria:</strong> <?php echo htmlspecialchars($_SESSION['categoria_nome']); ?>
                </p>
            </div>
            <div class="pagina-acoes">
                <a href="dashboard.php" class="btn-acao primario"><i class="fas fa-chart-line"></i> Ver Ranking</a>
                <a href="selecionar_categoria.php" class="btn-acao neutro"><i class="fas fa-exchange-alt"></i> Trocar Filtros</a>
                <a href="login.php?logout=1" class="btn-acao erro"><i class="fas fa-sign-out-alt"></i> Sair</a>

            </div>
        </div>

        <?php if (empty($trabalhos)): ?>
            <div class="aviso-sem-trabalhos">
                <i class="fas fa-info-circle"></i>
                <p>Nenhum trabalho foi encontrado para os filtros selecionados.</p>
            </div>
        <?php else: ?>
            <div class="trabalhos-grid">
                <?php foreach ($trabalhos as $trabalho):
                    // Chama a nova função para obter a média de cada trabalho
                    $media_trabalho = obterMediaTrabalho($trabalho['id']);
                ?>
                    <div class="trabalho-card <?php echo ($trabalho['avaliado'] ? 'avaliado' : ''); ?>" onclick="window.location.href='avaliar.php?id=<?php echo $trabalho['id']; ?>'">

                        <div class="trabalho-preview">
                            <iframe src="<?php echo gerarEmbedGdrive($trabalho['link_gdrive']); ?>" loading="lazy"></iframe>
                        </div>

                        <div class="trabalho-info">
                            <h4><?php echo htmlspecialchars($trabalho['titulo_trabalho']); ?></h4>
                            <p><strong>Aluno(a):</strong> <?php echo htmlspecialchars($trabalho['aluno_nome']); ?></p>
                            <p><strong>Escola:</strong> <?php echo htmlspecialchars($trabalho['escola_nome']); ?></p>

                            <div class="trabalho-media">
                                <i class="fas fa-star"></i> Média Atual:
                                <strong><?php echo ($media_trabalho !== null) ? number_format($media_trabalho, 1, ',', '.') : 'N/A'; ?></strong>
                            </div>
                        </div>

                        <button class="btn-avaliar">
                            <?php echo ($trabalho['avaliado'] ? '<i class="fas fa-eye"></i> Ver Nota' : '<i class="fas fa-pen-to-square"></i> Avaliar'); ?>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</body>