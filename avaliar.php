<?php
require_once 'config.php';
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$trabalho_id = intval($_GET['id']);
$jurado_id = $_SESSION['jurado_id'];
$trabalho = obterTrabalhoParaAvaliacao($trabalho_id, $jurado_id);

if (!$trabalho) {
    header('Location: index.php');
    exit;
}

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$trabalho['avaliado']) {
    $nota = floatval(str_replace(',', '.', $_POST['nota']));
    $observacao = trim($_POST['observacao']);

    if ($nota < 7.0 || $nota > 10.0) {
        $erro = 'A nota deve ser um valor entre 7,0 e 10,0.';
    } else {
        if (salvarAvaliacao($jurado_id, $trabalho_id, $nota, $observacao)) {
            $sucesso = 'Avaliação salva com sucesso!';
            // Recarrega os dados para exibir a nota salva e bloquear os campos
            $trabalho = obterTrabalhoParaAvaliacao($trabalho_id, $jurado_id);
        } else {
            $erro = 'Ocorreu um erro ao salvar. Talvez você já tenha avaliado este trabalho.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Avaliar Trabalho - <?php echo htmlspecialchars($trabalho['titulo_trabalho']); ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="container">
        <div class="pagina-header">
            <img src="img/logo.png" alt="Logo do Prêmio MPT" class="logo-header">

            <div class="pagina-titulo">
                <h1><?php echo htmlspecialchars($trabalho['titulo_trabalho']); ?></h1>
                <p>
                    <strong>Aluno(a):</strong> <?php echo htmlspecialchars($trabalho['aluno_nome']); ?> |
                    <strong>Escola:</strong> <?php echo htmlspecialchars($trabalho['escola_nome']); ?>
                </p>
            </div>
            <div class="pagina-acoes">
                <a href="index.php" class="btn-acao neutro"><i class="fas fa-arrow-left"></i>Voltar para a Lista</a>
                <a href="login.php?logout=1" class="btn-acao erro"><i class="fas fa-sign-out-alt"></i>Sair</a>
            </div>
        </div>


        <div class="avaliacao-grid">
            <div class="gdrive-preview-container">
                <div class="gdrive-preview">
                    <iframe src="<?php echo gerarEmbedGdrive($trabalho['link_gdrive']); ?>" loading="lazy"></iframe>
                </div>
            </div>

            <div class="form-container">
                <?php if (!empty($erro)): ?>
                    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo $erro; ?></div>
                <?php endif; ?>
                <?php if (!empty($sucesso)): ?>
                    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $sucesso; ?></div>
                <?php endif; ?>

                <?php if ($trabalho['avaliado']): ?>
                    <div class="resultado-box">
                        <h3><i class="fas fa-check-circle"></i> Avaliação Concluída</h3>
                        <div class="resultado-item">
                            <span>Sua Nota</span>
                            <p><?php echo number_format($trabalho['nota'], 1, ',', '.'); ?></p>
                        </div>
                        <div class="resultado-item">
                            <span>Sua Observação</span>
                            <p><?php echo !empty($trabalho['observacao']) ? nl2br(htmlspecialchars($trabalho['observacao'])) : 'Nenhuma observação foi feita.'; ?></p>
                        </div>
                        <p class="aviso-bloqueio">
                            <em>Esta avaliação já foi registrada e não pode ser alterada.</em>
                        </p>
                    </div>
                <?php else: ?>
                    <form method="post" class="avaliacao-form">
                        <h3><i class="fas fa-pen-to-square"></i> Sua Avaliação</h3>
                        <div class="form-group">
                            <label for="nota">Nota (de 7,0 a 10,0)</label>
                            <input type="text" id="nota" name="nota" required placeholder="Ex: 8,5"
                                pattern="^(7|8|9|10)(,[0-9])?$">
                        </div>
                        <div class="form-group">
                            <label for="observacao">Observações (opcional)</label>
                            <textarea id="observacao" name="observacao" rows="6"
                                placeholder="Adicione seus comentários sobre o trabalho aqui..."></textarea>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn-salvar">
                                <i class="fas fa-save"></i> Salvar Avaliação
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>