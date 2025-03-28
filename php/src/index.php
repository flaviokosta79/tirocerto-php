<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiro Certo - Fantasy Game</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Estilos gerais */
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f7f6;
            font-size: 13px;
            color: #333;
            line-height: 1.4;
        }
        header {
            background-color: #2a3f54;
            color: #fff;
            padding: 0.7rem 0;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        header h1 { margin: 0; font-size: 1.4em; font-weight: 500; }
        header nav ul { list-style: none; padding: 0; margin-top: 4px;}
        header nav ul li { display: inline; margin: 0 8px; }
        header nav ul li a { color: #e0e0e0; text-decoration: none; font-size: 0.85em; }
        header nav ul li a:hover { color: #fff; }
        footer { text-align: center; margin-top: 20px; padding: 12px; color: #888; font-size: 0.75em; border-top: 1px solid #e0e0e0; }

        /* Layout com CSS Grid */
        .content-container {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 20px;
            padding: 20px;
            max-width: 1000px;
            margin: 20px auto;
            align-items: stretch;
        }

        .card {
            background-color: #fff;
            border-radius: 4px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
            padding: 15px;
            display: flex;
            flex-direction: column;
        }
        .card h3 {
            margin: 0 0 10px 0;
            padding: 0 0 8px 0;
            font-size: 1em;
            color: #333;
            text-align: center;
            font-weight: 600;
            border-bottom: 1px solid #eee;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .card-content {
             flex-grow: 1;
             overflow-x: auto;
        }


        /* Tabela */
        #tabela-card table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
            font-size: 0.75em;
        }
        #tabela-card th, #tabela-card td {
            border: none;
            border-bottom: 1px solid #f0f0f0;
            padding: 6px 4px; /* Aumenta um pouco o padding vertical da tabela */
            text-align: center;
            vertical-align: middle;
            white-space: nowrap;
        }
        #tabela-card tr:last-child td { border-bottom: none; }
        #tabela-card th {
             background-color: transparent;
             font-weight: 500;
             color: #999;
             font-size: 0.9em;
             border-bottom-width: 1px;
             border-color: #ddd;
             text-transform: uppercase;
             padding-bottom: 7px;
        }
        #tabela-card td:first-child { font-weight: 500; width: 25px; }
        #tabela-card td:nth-child(3) { text-align: left; }
        .team-logo { width: 14px; height: 14px; vertical-align: middle; margin-right: 5px; }
        .team-name { white-space: nowrap; }
        #tabela-card tbody tr:hover { background-color: #f8f9fa; }
        #tabela-card td b { font-weight: 600; }

        /* Jogos */
        .rodada-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid #eee;
        }
        .rodada-nav-button {
            background: none;
            border: none;
            font-size: 1.1em;
            cursor: pointer;
            color: #28a745;
            padding: 0 4px;
            font-weight: bold;
        }
        .rodada-nav-button:hover:not(:disabled) { color: #218838; }
        .rodada-nav-button:disabled { color: #ccc; cursor: default; }
        #rodada-nome { font-weight: 600; font-size: 0.95em; color: #333; text-transform: uppercase; }

        .games-list { margin-top: 5px; }
        .game-block {
             border-bottom: 1px solid #f0f0f0;
             padding-bottom: 4px; /* Diminui um pouco */
             margin-bottom: 4px; /* Diminui um pouco */
        }
        .game-block:last-child { border-bottom: none; margin-bottom: 0; }

        .game-meta-info {
            font-size: 0.7em;
            color: #777;
            text-align: center;
            margin-bottom: 2px; /* Diminui espaço */
            line-height: 1.2;
        }

        .game {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 5px;
            font-size: 0.8em;
            padding: 1px 0;
            line-height: 1.3;
        }
        .game-teams { display: contents; }
        .game .team-name { flex: 1; text-align: center; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .game .team-name:first-of-type { text-align: right; }
        .game .team-name:last-of-type { text-align: left; }

        .game-score { font-weight: bold; padding: 0 4px; color: #333; font-size: 1em; }
        /* Reativa o botão "FIQUE POR DENTRO" */
        .fique-por-dentro {
             display: block;
             font-size: 0.7em;
             color: #28a745;
             text-align: center;
             margin-top: 2px; /* Diminui espaço */
             cursor: pointer;
             text-decoration: none;
             font-weight: 600;
             letter-spacing: 0.5px;
             text-transform: uppercase;
             padding: 2px 0;
        }
        .fique-por-dentro:hover { text-decoration: underline; }

        /* Indicador de carregamento */
        #games-list-container.loading p {
            text-align: center;
            padding: 15px;
            color: #888;
            font-style: italic;
            font-size: 0.8em;
        }

        /* Media Query para telas menores */
        @media (max-width: 850px) {
            .content-container {
                grid-template-columns: 1fr;
                gap: 15px;
                padding: 15px;
            }
            .card { padding: 15px; }
            .card h3 { margin: -15px -15px 12px -15px; padding: 10px 15px; }
        }

    </style>
</head>
<body>
    <header>
        <h1>Tiro Certo</h1>
        <nav>
            <ul>
                <li><a href="index.php">Início</a></li>
                <li><a href="auth/login.php">Login</a></li>
                <li><a href="auth/register.php">Cadastro</a></li>
            </ul>
        </nav>
    </header>

    <div class="content-container">

        <div id="tabela-card" class="card">
            <h3>TABELA</h3>
            <div class="card-content">
                <?php
                // IDs e URLs
                $campeonatoId = 10; // ID do Brasileirão
                $nodeCacheUrlBase = getenv('NODEJS_CACHE_URL') ?: 'http://nodejs-cache:3000';
                $urlTabela = $nodeCacheUrlBase . "/api/cache/campeonatos/{$campeonatoId}/tabela";

                // Função para buscar e decodificar JSON (melhorada)
                function fetchJson($url) {
                    $context = stream_context_create(['http' => ['timeout' => 5]]);
                    $json = @file_get_contents($url, false, $context);
                    if ($json === false) {
                        error_log("Falha ao buscar URL: " . $url);
                        return ['error' => true, 'message' => "Não foi possível conectar ao serviço de cache ($url)."];
                    }
                    $data = json_decode($json, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                         error_log("Erro ao decodificar JSON da URL: " . $url . " - Erro: " . json_last_error_msg());
                         return ['error' => true, 'message' => "Resposta inválida do serviço de cache."];
                    }
                    // Verifica se a API retornou um erro encapsulado
                    if (isset($data['error'])) {
                        error_log("Erro retornado pela API de cache ($url): " . $data['error']);
                        return ['error' => true, 'message' => $data['error']];
                    }
                    return $data;
                }

                // --- Buscar e Exibir Tabela ---
                $tabelaResult = fetchJson($urlTabela);

                if (isset($tabelaResult['error'])) {
                    echo "<p style='color: red;'>Erro ao carregar tabela: " . htmlspecialchars($tabelaResult['message']) . "</p>";
                } elseif ($tabelaResult && is_array($tabelaResult)) {
                    echo "<table>";
                    echo "<thead><tr><th>#</th><th></th><th>Time</th><th>P</th><th>J</th><th>V</th><th>E</th><th>D</th><th>GP</th><th>GC</th><th>SG</th></tr></thead>";
                    echo "<tbody>";
                    foreach ($tabelaResult as $time) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($time['posicao'] ?? '-') . "</td>";
                        echo "<td><img src='" . htmlspecialchars($time['time']['escudo'] ?? '') . "' alt='' class='team-logo' onerror='this.style.display=\"none\"'></td>";
                        echo "<td class='team-name'>" . htmlspecialchars($time['time']['nome_popular'] ?? 'N/A') . "</td>";
                        echo "<td><b>" . htmlspecialchars($time['pontos'] ?? '-') . "</b></td>";
                        echo "<td>" . htmlspecialchars($time['jogos'] ?? '-') . "</td>";
                        echo "<td>" . htmlspecialchars($time['vitorias'] ?? '-') . "</td>";
                        echo "<td>" . htmlspecialchars($time['empates'] ?? '-') . "</td>";
                        echo "<td>" . htmlspecialchars($time['derrotas'] ?? '-') . "</td>";
                        echo "<td>" . htmlspecialchars($time['gols_pro'] ?? '-') . "</td>";
                        echo "<td>" . htmlspecialchars($time['gols_contra'] ?? '-') . "</td>";
                        echo "<td>" . htmlspecialchars($time['saldo_gols'] ?? '-') . "</td>";
                        echo "</tr>";
                    }
                    echo "</tbody></table>";
                } else {
                    echo "<p>Não foi possível carregar a tabela de classificação.</p>";
                }
                ?>
            </div>
        </div>

        <div id="jogos-card" class="card">
             <h3>JOGOS</h3>
             <div class="card-content">
                <?php
                // Determina a rodada inicial (padrão: 1, ou via GET)
                $rodadaInicial = isset($_GET['rodada']) ? (int)$_GET['rodada'] : 1;
                if ($rodadaInicial < 1) $rodadaInicial = 1;

                $urlJogosRodada = $nodeCacheUrlBase . "/api/cache/campeonatos/{$campeonatoId}/rodada/{$rodadaInicial}";
                $jogosResult = fetchJson($urlJogosRodada);

                $jogosDaRodada = [];
                $nomeRodada = "Rodada " . $rodadaInicial;
                $rodadaAtual = $rodadaInicial;
                $rodadaProxima = null;
                $rodadaAnterior = null;

                if (isset($jogosResult['error'])) {
                     // O erro será exibido abaixo
                } elseif ($jogosResult && isset($jogosResult['partidas']) && is_array($jogosResult['partidas'])) {
                    $jogosDaRodada = $jogosResult['partidas'];
                    $nomeRodada = $jogosResult['nome'] ?? $nomeRodada;
                    $rodadaAtual = $jogosResult['rodada'] ?? $rodadaInicial;
                    $rodadaProxima = $jogosResult['proxima_rodada']['rodada'] ?? null;
                    $rodadaAnterior = $jogosResult['rodada_anterior']['rodada'] ?? null;
                }

                // Função para renderizar um bloco de jogo (usada tanto no PHP inicial quanto no JS)
                // Garante que o HTML gerado seja idêntico em ambos os casos
                function renderGameBlockHTML($jogo) {
                    $homeTeamName = htmlspecialchars($jogo['time_mandante']['nome_popular'] ?? 'N/A');
                    $homeTeamLogo = htmlspecialchars($jogo['time_mandante']['escudo'] ?? '');
                    $awayTeamName = htmlspecialchars($jogo['time_visitante']['nome_popular'] ?? 'N/A');
                    $awayTeamLogo = htmlspecialchars($jogo['time_visitante']['escudo'] ?? '');
                    $score = (isset($jogo['placar_mandante']) && $jogo['placar_mandante'] !== null)
                             ? htmlspecialchars($jogo['placar_mandante'] . " x " . $jogo['placar_visitante'])
                             : 'x';
                    $gameDateTimeStr = '';
                    if (isset($jogo['data_realizacao_iso'])) {
                         try {
                             $gameDateTime = new DateTime($jogo['data_realizacao_iso']);
                             // Usar IntlDateFormatter se disponível, senão fallback
                             if (class_exists('IntlDateFormatter')) {
                                 // Formato mais curto: DiaSem • dd/MM • HH:mm
                                 $formatter = new IntlDateFormatter('pt_BR', IntlDateFormatter::FULL, IntlDateFormatter::FULL, 'America/Sao_Paulo', IntlDateFormatter::GREGORIAN, 'E • dd/MM • HH:mm');
                                 $gameDateTimeStr = $formatter->format($gameDateTime);
                             } else {
                                 $dias = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
                                 $gameDateTimeStr = $dias[$gameDateTime->format('w')] . ' • ' . $gameDateTime->format('d/m • H:i');
                             }
                         } catch (Exception $e) {
                             $gameDateTimeStr = htmlspecialchars($jogo['data_realizacao'] . ' ' . ($jogo['hora_realizacao'] ?? ''));
                         }
                    } elseif (isset($jogo['data_realizacao'])) {
                         $gameDateTimeStr = htmlspecialchars($jogo['data_realizacao'] . ' ' . ($jogo['hora_realizacao'] ?? ''));
                    }
                    $stadium = htmlspecialchars($jogo['estadio']['nome_popular'] ?? '');

                    // Retorna a string HTML formatada, garantindo espaços e quebras de linha idênticos ao JS
                    // Adiciona espaços antes e depois dos elementos internos e quebra de linha no final.
                    // Garante que as classes e a estrutura sejam EXATAMENTE as mesmas.
                    // Adiciona espaços extras dentro dos spans para garantir consistência com JS
                    // Usa trim() para remover espaços extras no início/fim da string final
                    return trim(<<<HTML

                    <div class="game-block">
                        <div class='game-meta-info'> {$stadium} • {$gameDateTimeStr} </div>
                        <div class='game'>
                            <div class='game-teams'>
                                <span class='team-name'> {$homeTeamName} </span>
                                <img src='{$homeTeamLogo}' alt='' class='team-logo' onerror='this.style.display="none"'>
                                <span class='game-score'> {$score} </span>
                                <img src='{$awayTeamLogo}' alt='' class='team-logo' onerror='this.style.display="none"'>
                                <span class='team-name'> {$awayTeamName} </span>
                            </div>
                        </div>
                        <a href="#" class='fique-por-dentro'> FIQUE POR DENTRO </a>
                    </div>

HTML);
                }
                ?>

                <div class="rodada-header">
                    <button id="prev-rodada-btn" class="rodada-nav-button" title="Rodada Anterior" <?php echo $rodadaAnterior ? '' : 'disabled'; ?>>&#10094;</button>
                    <span id="rodada-nome"><?php echo htmlspecialchars(strtoupper($nomeRodada)); ?></span>
                    <button id="next-rodada-btn" class="rodada-nav-button" title="Próxima Rodada" <?php echo $rodadaProxima ? '' : 'disabled'; ?>>&#10095;</button>
                </div>

                <div id="games-list-container">
                    <?php
                    if (!empty($jogosDaRodada)) {
                        echo "<div class='games-list'>";
                        foreach ($jogosDaRodada as $jogo) {
                            echo renderGameBlockHTML($jogo); // Chama a função de renderização PHP
                        }
                        echo "</div>";
                    } elseif (!isset($jogosResult['error'])) {
                        echo "<p>Nenhum jogo encontrado para esta rodada.</p>";
                    }
                     if (isset($jogosResult['error'])) {
                        echo "<p style='color: red;'>Erro ao carregar jogos: " . htmlspecialchars($jogosResult['message']) . "</p>";
                    }
                    ?>
                </div>
                <!-- Guarda dados para o JS -->
                <input type="hidden" id="campeonato-id" value="<?php echo $campeonatoId; ?>">
                <input type="hidden" id="rodada-atual" value="<?php echo $rodadaAtual; ?>">
                <input type="hidden" id="rodada-proxima" value="<?php echo $rodadaProxima; ?>">
                <input type="hidden" id="rodada-anterior" value="<?php echo $rodadaAnterior; ?>">
             </div>
        </div>

    </div> <!-- Fim content-container -->

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Tiro Certo. Todos os direitos reservados.</p>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>