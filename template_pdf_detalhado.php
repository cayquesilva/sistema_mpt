<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Relatório Detalhado</title>
    <style>
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 10pt;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #00509e;
            padding-bottom: 10px;
        }

        .header img {
            max-width: 150px;
            margin-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 18pt;
            color: #00509e;
        }

        .header p {
            margin: 5px 0;
            font-size: 12pt;
        }

        .aluno-bloco {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        .aluno-bloco h2 {
            font-size: 14pt;
            color: #333;
            background-color: #f2f2f2;
            padding: 5px;
            border-radius: 3px;
        }

        .jurado-tabela {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-left: 15px;
        }

        .jurado-tabela th,
        .jurado-tabela td {
            border: 1px solid #ccc;
            padding: 6px;
        }

        .jurado-tabela th {
            background-color: #e9ecef;
        }

        .nota {
            text-align: center;
            font-weight: bold;
        }

        .media-jurado {
            text-align: right;
            font-weight: bold;
            margin-top: 5px;
            margin-left: 15px;
        }
    </style>
</head>

<body>
    <div class="header">
        <?php if (!empty($logoBase64)): ?><img src="<?php echo $logoBase64; ?>"><?php endif; ?>
        <h1>Relatório Detalhado - Prêmio MPT na Escola</h1>
        <p><strong>Grupo:</strong> <?php echo htmlspecialchars($grupo['nome']); ?></p>
        <p><strong>Categoria:</strong> <?php echo htmlspecialchars($categoria['nome']); ?></p>
    </div>

    <?php foreach ($dados_organizados as $aluno_nome => $dados_aluno): ?>
        <div class="aluno-bloco">
            <h2><?php echo htmlspecialchars($aluno_nome); ?> <small>(<?php echo htmlspecialchars($dados_aluno['escola']); ?>)</small></h2>
            <?php foreach ($dados_aluno['avaliacoes'] as $jurado_nome => $notas): ?>
                <table class="jurado-tabela">
                    <thead>
                        <tr>
                            <th colspan="2">Avaliação de: <?php echo htmlspecialchars($jurado_nome); ?></th>
                        </tr>
                        <tr>
                            <th>Critério</th>
                            <th class="nota">Nota</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $soma_notas = 0;
                        foreach ($notas as $item_nota):
                            $soma_notas += $item_nota['nota'];
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item_nota['criterio']); ?></td>
                                <td class="nota"><?php echo number_format($item_nota['nota'], 2, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="media-jurado">Média do Jurado: <?php echo number_format($soma_notas / count($notas), 2, ',', '.'); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</body>

</html>