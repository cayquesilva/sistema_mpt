<?php
require_once 'config.php';

// --- VERIFICAÇÕES INICIAIS ---
if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
    header('Location: login.php');
    exit;
}
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

// --- BUSCA DE DADOS ---
$trabalho_id = intval($_GET['id']);
$jurado_id = $_SESSION['jurado_id'];
$trabalho = obterTrabalhoParaAvaliacao($trabalho_id, $jurado_id);

if (!$trabalho) {
    header('Location: index.php');
    exit;
}

$media_geral = obterMediaTrabalho($trabalho_id);

// Busca os critérios específicos da categoria deste trabalho
$criterios = obterCriteriosPorCategoria($trabalho['categoria_id']);

$erro = '';
$sucesso = '';

// --- PROCESSAMENTO DO FORMULÁRIO (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$trabalho['avaliado']) {
    $notas = $_POST['notas'] ?? [];
    $observacao = trim($_POST['observacao']);
    $form_valido = true;

    // Validação para garantir que todos os critérios foram avaliados
    if (count($notas) != count($criterios)) {
        $erro = 'Todos os critérios precisam de uma nota.';
        $form_valido = false;
    } else {
        // Validação do valor de cada nota
        foreach ($notas as $nota_str) {
            if (empty($nota_str)) {
                $erro = 'Todos os critérios precisam ser preenchidos.';
                $form_valido = false;
                break;
            }
            $nota = floatval(str_replace(',', '.', $nota_str));
            if ($nota < 7.0 || $nota > 10.0) {
                $erro = 'Todas as notas devem ser um valor entre 7,0 e 10,0.';
                $form_valido = false;
                break;
            }
        }
    }

    if ($form_valido) {
        // Converte as notas para o formato float antes de salvar
        $notas_float = array_map(function ($n) {
            return floatval(str_replace(',', '.', $n));
        }, $notas);

        if (salvarAvaliacao($jurado_id, $trabalho_id, $notas_float, $observacao)) {
            $sucesso = 'Avaliação salva com sucesso!';
            // Recarrega os dados para exibir a avaliação salva
            $trabalho = obterTrabalhoParaAvaliacao($trabalho_id, $jurado_id);
        } else {
            $erro = 'Ocorreu um erro ao salvar. Você pode já ter avaliado este trabalho.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avaliar Trabalho - <?php echo htmlspecialchars($trabalho['titulo_trabalho']); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
                <div class="media-geral-display">
                    Média Geral Atual: <strong><?php echo ($media_geral !== null) ? number_format($media_geral, 1, ',', '.') : 'Aguardando avaliações'; ?></strong>
                </div>
            </div>
            <div class="pagina-acoes">
                <a href="index.php" class="btn-acao neutro"><i class="fas fa-arrow-left"></i> Voltar para a Lista</a>
                <a href="login.php?logout=1" class="btn-acao erro"><i class="fas fa-sign-out-alt"></i> Sair</a>
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

                <?php if ($trabalho['avaliado']):
                    // Se já foi avaliado, busca as notas dadas
                    $notas_dadas = obterNotasDadas($trabalho_id, $jurado_id);
                ?>
                    <div class="resultado-box">
                        <h3><i class="fas fa-check-circle"></i> Avaliação Concluída</h3>
                        <div class="criterios-resultado-lista">
                            <?php foreach ($notas_dadas as $item): ?>
                                <div class="criterio-resultado-item">
                                    <span><?php echo htmlspecialchars($item['nome']); ?></span>
                                    <strong><?php echo number_format($item['nota'], 1, ',', '.'); ?></strong>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="resultado-item">
                            <span>Sua Observação Geral</span>
                            <p><?php echo !empty($trabalho['observacao']) ? nl2br(htmlspecialchars($trabalho['observacao'])) : 'Nenhuma observação foi feita.'; ?></p>
                        </div>
                    </div>
                <?php else: ?>
                    <form method="post" class="avaliacao-form">
                        <h3><i class="fas fa-pen-to-square"></i> Notas por Critério</h3>

                        <?php foreach ($criterios as $criterio): ?>
                            <div class="form-group-criterio">
                                <label for="nota_<?php echo $criterio['id']; ?>">
                                    <?php echo htmlspecialchars($criterio['nome']); ?>
                                </label>
                                <input type="text"
                                    id="nota_<?php echo $criterio['id']; ?>"
                                    name="notas[<?php echo $criterio['id']; ?>]"
                                    required
                                    placeholder="7,0 a 10,0"
                                    pattern="^(7|8|9|10)(,[0-9])?$"
                                    class="nota-input-criterio">
                            </div>
                        <?php endforeach; ?>

                        <div class="form-group" style="margin-top: 20px;">
                            <label for="observacao">Observações Gerais (opcional)</label>
                            <textarea id="observacao" name="observacao" rows="4"
                                placeholder="Adicione seus comentários gerais sobre o trabalho aqui..."></textarea>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn-salvar">
                                <i class="fas fa-save"></i> Salvar Avaliação Completa
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>