<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Relatório de Ranking</title>
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

        h2.categoria-titulo {
            font-size: 14pt;
            color: #333;
            margin-top: 25px;
            margin-bottom: 10px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .posicao {
            text-align: center;
            font-weight: bold;
        }

        .pontuacao {
            text-align: center;
            font-weight: bold;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <div class="header">
        <?php if (!empty($logoBase64)): ?><img src="<?php echo $logoBase64; ?>"><?php endif; ?>
        <h1>Ranking Final - Prêmio MPT na Escola</h1>
        <p><strong>Grupo:</strong> <?php echo htmlspecialchars($grupo['nome']); ?></p>
    </div>

    <?php
    // Se a variável $rankings_por_categoria existir, é o relatório consolidado
    if (isset($rankings_por_categoria) && !empty($rankings_por_categoria)):
        $total_categorias = count($rankings_por_categoria);
        $count = 0;
        foreach ($rankings_por_categoria as $nome_categoria => $ranking_da_categoria):
            $count++;
    ?>
            <h2 class="categoria-titulo">Categoria: <?php echo htmlspecialchars($nome_categoria); ?></h2>
            <table>
                <thead>
                    <tr>
                        <th class="posicao">Pos.</th>
                        <th>Aluno(a)</th>
                        <th>Escola</th>
                        <th class="pontuacao">Pontuação Final</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ranking_da_categoria as $item): ?>
                        <tr>
                            <td class="posicao"><?php echo $item['posicao']; ?>º</td>
                            <td><?php echo htmlspecialchars($item['aluno_nome']); ?></td>
                            <td><?php echo htmlspecialchars($item['escola_nome']); ?></td>
                            <td class="pontuacao"><?php echo number_format($item['pontuacao_final'], 2, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if ($count < $total_categorias): // Adiciona quebra de página entre as categorias 
            ?>
                <div class="page-break"></div>
            <?php endif; ?>
        <?php
        endforeach;
    // Senão, é o relatório de categoria única
    elseif (isset($ranking) && !empty($ranking)):
        ?>
        <h2 class="categoria-titulo">Categoria: <?php echo htmlspecialchars($categoria['nome']); ?></h2>
        <table>
            <thead>
                <tr>
                    <th class="posicao">Pos.</th>
                    <th>Aluno(a)</th>
                    <th>Escola</th>
                    <th class="pontuacao">Pontuação Final</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ranking as $item): ?>
                    <tr>
                        <td class="posicao"><?php echo $item['posicao']; ?>º</td>
                        <td><?php echo htmlspecialchars($item['aluno_nome']); ?></td>
                        <td><?php echo htmlspecialchars($item['escola_nome']); ?></td>
                        <td class="pontuacao"><?php echo number_format($item['pontuacao_final'], 2, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Não foram encontradas avaliações para gerar o ranking com os filtros selecionados.</p>
    <?php endif; ?>
</body>

</html>