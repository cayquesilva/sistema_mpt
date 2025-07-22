<?php
require_once 'config.php';
// Validar se o fluxo foi seguido
if (!isset($_SESSION['grupo_id']) || !isset($_SESSION['categoria_id'])) {
    header('Location: selecionar_grupo.php');
    exit;
}
$ranking = obterRanking($_SESSION['grupo_id'], $_SESSION['categoria_id']);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranking - Prêmio MPT</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="container">
        <div class="pagina-header">
            <img src="img/logo.png" alt="Logo do Prêmio MPT" class="logo-header">

            <div class="pagina-titulo">
                <h1><i class="fas fa-trophy"></i> Ranking dos Trabalhos</h1>
                <p>
                    <strong>Grupo:</strong> <?php echo htmlspecialchars($_SESSION['grupo_nome']); ?> /
                    <strong>Categoria:</strong> <?php echo htmlspecialchars($_SESSION['categoria_nome']); ?>
                </p>
            </div>
            <div class="pagina-acoes">
                <a href="index.php" class="btn-acao neutro"><i class="fas fa-arrow-left"></i> Voltar para os Trabalhos</a>
                <a href="login.php?logout=1" class="btn-acao erro"><i class="fas fa-sign-out-alt"></i> Sair</a>
            </div>
        </div>

        <div class="ranking-container">
            <?php if (empty($ranking)): ?>
                <div class="aviso-sem-trabalhos">
                    <i class="fas fa-info-circle"></i>
                    <p>Ainda não há avaliações suficientes para gerar um ranking para esta seleção.</p>
                </div>
            <?php else: ?>
                <table class="ranking-table-polished">
                    <thead>
                        <tr>
                            <th class="col-posicao">Pos.</th>
                            <th class="col-medalha"></th>
                            <th>Aluno(a)</th>
                            <th>Título do Trabalho</th>
                            <th>Escola</th>
                            <th class="col-pontuacao">Pontuação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ranking as $item):
                            $classe_posicao = '';
                            if ($item['posicao'] <= 3) {
                                $classe_posicao = 'posicao-' . $item['posicao'];
                            }
                        ?>
                            <tr class="<?php echo $classe_posicao; ?>">
                                <td class="col-posicao"><?php echo $item['posicao']; ?>º</td>
                                <td class="col-medalha">
                                    <?php
                                    // Adiciona o ícone da medalha/troféu
                                    switch ($item['posicao']) {
                                        case 1:
                                            echo '<i class="fas fa-trophy medalha-icone ouro"></i>';
                                            break;
                                        case 2:
                                            echo '<i class="fas fa-medal medalha-icone prata"></i>';
                                            break;
                                        case 3:
                                            echo '<i class="fas fa-medal medalha-icone bronze"></i>';
                                            break;
                                    }
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($item['aluno_nome']); ?></td>
                                <td><?php echo htmlspecialchars($item['titulo_trabalho']); ?></td>
                                <td><?php echo htmlspecialchars($item['escola_nome']); ?></td>
                                <td class="col-pontuacao"><?php echo number_format($item['pontuacao_final'], 1, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>