<?php
require_once 'config.php';
require_once 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Proteção: Apenas admin
if (!isset($_SESSION['jurado_codigo_acesso']) || $_SESSION['jurado_codigo_acesso'] !== 'admin') {
    die('Acesso negado.');
}

// Pega os filtros da URL
$grupo_id = $_GET['grupo_id'] ?? null;
$categoria_id = $_GET['categoria_id'] ?? null; // Pode estar vazio agora
$tipo_relatorio = $_GET['tipo'] ?? null;

if (!$grupo_id || !$tipo_relatorio) {
    die('Parâmetros inválidos. Por favor, selecione ao menos o grupo e o tipo de relatório.');
}

// --- Funções Auxiliares ---
function getLogoAsBase64()
{
    $logoPath = __DIR__ . '/img/logo.png';
    if (file_exists($logoPath)) {
        return 'data:image/' . pathinfo($logoPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($logoPath));
    }
    return '';
}

// --- Funções de Geração de Relatório ---

// Função para gerar o relatório de RANKING CONSOLIDADO (NOVA)
function gerarRelatorioRankingMultiCategoria($pdo, $grupo_id)
{
    $grupo = obterGrupoPorId($grupo_id);
    $categorias = obterCategorias();
    $logoBase64 = getLogoAsBase64();

    // Busca os dados de ranking para cada categoria e armazena em um array
    $rankings_por_categoria = [];
    foreach ($categorias as $categoria) {
        $ranking_data = obterRanking($grupo_id, $categoria['id']);
        if (!empty($ranking_data)) {
            $rankings_por_categoria[$categoria['nome']] = $ranking_data;
        }
    }

    ob_start();
    include 'template_pdf_ranking.php'; // O mesmo template será reutilizado
    $html = ob_get_clean();

    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("Ranking_Consolidado_" . $grupo['nome'] . ".pdf", ["Attachment" => 0]);
    exit;
}


// Função para gerar o relatório de RANKING para UMA categoria (MODIFICADA)
function gerarRelatorioRanking($pdo, $grupo_id, $categoria_id)
{
    $grupo = obterGrupoPorId($grupo_id);
    $categoria = obterCategoriaPorId($categoria_id);
    $ranking = obterRanking($grupo_id, $categoria_id);
    $logoBase64 = getLogoAsBase64();

    ob_start();
    include 'template_pdf_ranking.php';
    $html = ob_get_clean();

    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("Ranking_" . $grupo['nome'] . "_" . $categoria['nome'] . ".pdf", ["Attachment" => 0]);
    exit;
}

// Função para gerar o relatório DETALHADO (sem alterações)
function gerarRelatorioDetalhado($pdo, $grupo_id, $categoria_id)
{
    // ... (código da função sem alterações) ...
    $grupo = obterGrupoPorId($grupo_id);
    $categoria = obterCategoriaPorId($categoria_id);
    $stmt = $pdo->prepare("SELECT t.aluno_nome, t.escola_nome, j.nome as jurado_nome, c.nome as criterio_nome, a.nota FROM avaliacoes a JOIN trabalhos t ON a.trabalho_id = t.id JOIN jurados j ON a.jurado_id = j.id JOIN criterios c ON a.criterio_id = c.id WHERE t.grupo_id = :grupo_id AND t.categoria_id = :categoria_id ORDER BY t.aluno_nome, j.nome, c.id");
    $stmt->execute(['grupo_id' => $grupo_id, 'categoria_id' => $categoria_id]);
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $dados_organizados = [];
    foreach ($dados as $dado) {
        $dados_organizados[$dado['aluno_nome']]['escola'] = $dado['escola_nome'];
        $dados_organizados[$dado['aluno_nome']]['avaliacoes'][$dado['jurado_nome']][] = ['criterio' => $dado['criterio_nome'], 'nota' => $dado['nota']];
    }
    $logoBase64 = getLogoAsBase64();
    ob_start();
    include 'template_pdf_detalhado.php';
    $html = ob_get_clean();
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("Relatorio_Detalhado_" . $grupo['nome'] . "_" . $categoria['nome'] . ".pdf", ["Attachment" => 0]);
    exit;
}

// --- Roteador Principal (MODIFICADO) ---
switch ($tipo_relatorio) {
    case 'ranking':
        if (empty($categoria_id)) {
            // Se nenhuma categoria foi escolhida, gera o relatório consolidado
            gerarRelatorioRankingMultiCategoria($pdo, $grupo_id);
        } else {
            // Se uma categoria foi escolhida, gera o relatório específico
            gerarRelatorioRanking($pdo, $grupo_id, $categoria_id);
        }
        break;
    case 'detalhado':
        if (empty($categoria_id)) {
            die('Para o relatório detalhado, é necessário selecionar uma categoria específica.');
        }
        gerarRelatorioDetalhado($pdo, $grupo_id, $categoria_id);
        break;
    default:
        die('Tipo de relatório inválido.');
}
