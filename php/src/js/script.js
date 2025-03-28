// php/src/js/script.js
console.log("Script JS carregado.");

document.addEventListener('DOMContentLoaded', () => {
    const prevButton = document.getElementById('prev-rodada-btn');
    const nextButton = document.getElementById('next-rodada-btn');
    const rodadaNomeEl = document.getElementById('rodada-nome');
    const gamesListContainer = document.getElementById('games-list-container');
    const campeonatoIdInput = document.getElementById('campeonato-id');
    const rodadaAtualInput = document.getElementById('rodada-atual');
    const rodadaProximaInput = document.getElementById('rodada-proxima');
    const rodadaAnteriorInput = document.getElementById('rodada-anterior');

    // Função para escapar HTML (segurança)
    const escapeHTML = (str) => {
        if (str === null || str === undefined) return '';
        // Simplificado para template literals
        return String(str).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '<', '>': '>', '"': '"', "'": '&#39;' })[m]);
    }

    // Função para formatar data/hora (similar ao PHP, com fallback)
    const formatGameDateTime = (isoDateTime, dateStr, timeStr) => {
        if (isoDateTime) {
            try {
                const gameDate = new Date(isoDateTime);
                if (isNaN(gameDate.getTime())) throw new Error('Invalid Date');

                // Tenta usar Intl.DateTimeFormat se disponível
                if (typeof Intl !== 'undefined' && typeof Intl.DateTimeFormat !== 'undefined') {
                    const options = { weekday: 'short', day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit', hour12: false, timeZone: 'America/Sao_Paulo' };
                    let formatted = new Intl.DateTimeFormat('pt-BR', options).format(gameDate);
                    formatted = formatted.charAt(0).toUpperCase() + formatted.slice(1);
                    formatted = formatted.replace(/\.$/, ''); // Remove ponto final
                    return formatted; // Ex: Qua • 28/03 • 15:30
                } else {
                    // Fallback manual (pode não ter o dia da semana correto)
                    const d = gameDate;
                    const pad = (num) => num.toString().padStart(2, '0');
                    const dias = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
                    return `${dias[d.getDay()]} • ${pad(d.getDate())}/${pad(d.getMonth() + 1)} • ${pad(d.getHours())}:${pad(d.getMinutes())}`;
                }
            } catch (e) {
                // Fallback final para strings originais
                return escapeHTML(`${dateStr || ''} ${timeStr || ''}`);
            }
        } else if (dateStr) {
            return escapeHTML(`${dateStr} ${timeStr || ''}`);
        }
        return '';
    };


    // Função para gerar o HTML de um bloco de jogo (DEVE ser idêntica à do PHP)
    // Usando template literals para clareza
    const renderGameBlockJS = (jogo) => {
        const homeTeamName = escapeHTML(jogo.time_mandante?.nome_popular || 'N/A');
        const homeTeamLogo = escapeHTML(jogo.time_mandante?.escudo || '');
        const awayTeamName = escapeHTML(jogo.time_visitante?.nome_popular || 'N/A');
        const awayTeamLogo = escapeHTML(jogo.time_visitante?.escudo || '');
        const score = (jogo.placar_mandante !== null && jogo.placar_visitante !== null)
                     ? escapeHTML(`${jogo.placar_mandante} x ${jogo.placar_visitante}`)
                     : 'x';
        const gameDateTimeStr = formatGameDateTime(jogo.data_realizacao_iso, jogo.data_realizacao, jogo.hora_realizacao);
        const stadium = escapeHTML(jogo.estadio?.nome_popular || '');

        // Retorna a string HTML formatada, idêntica à função PHP
        // Adiciona quebra de linha no final para garantir consistência com o PHP (embora não afete a renderização)
        return `
                    <div class="game-block">
                        <div class='game-meta-info'>${stadium} • ${gameDateTimeStr}</div>
                        <div class='game'>
                            <div class='game-teams'>
                                <span class='team-name'>${homeTeamName}</span>
                                <img src='${homeTeamLogo}' alt='' class='team-logo' onerror='this.style.display="none"'>
                                <span class='game-score'>${score}</span>
                                <img src='${awayTeamLogo}' alt='' class='team-logo' onerror='this.style.display="none"'>
                                <span class='team-name'>${awayTeamName}</span>
                            </div>
                        </div>
                        <a href="#" class='fique-por-dentro'>FIQUE POR DENTRO</a>
                    </div>\n`;
    }


    const fetchAndRenderRodada = async (rodadaNum) => {
        if (!rodadaNum) return;

        const campeonatoId = campeonatoIdInput.value;
        const apiUrl = `api_jogos.php?rodada=${rodadaNum}&campeonato=${campeonatoId}`;

        // Adiciona classe de carregamento
        gamesListContainer.innerHTML = '<p>Carregando jogos...</p>';
        gamesListContainer.classList.add('loading');
        prevButton.disabled = true;
        nextButton.disabled = true;

        try {
            const response = await fetch(apiUrl);
            if (!response.ok) {
                let errorMsg = `Erro HTTP: ${response.status}`;
                try {
                    const errorData = await response.json();
                    errorMsg = errorData.message || errorMsg;
                } catch (e) { /* Ignora erro */ }
                throw new Error(errorMsg);
            }

            const data = await response.json();

            if (data.error) {
                throw new Error(data.message || "Erro ao buscar dados da rodada.");
            }

            // Atualiza o nome da rodada
            rodadaNomeEl.textContent = (data.nome || `Rodada ${data.rodada || rodadaNum}`).toUpperCase();

            // Atualiza os inputs hidden
            rodadaAtualInput.value = data.rodada || rodadaNum;
            rodadaProximaInput.value = data.proxima_rodada?.rodada || '';
            rodadaAnteriorInput.value = data.rodada_anterior?.rodada || '';

            // Renderiza os jogos usando a função JS
            renderGamesList(data.partidas || []);

            // Habilita/desabilita botões
            updateNavButtons();

        } catch (error) {
            console.error("Erro ao buscar/renderizar rodada:", error);
            gamesListContainer.innerHTML = `<p style='color: red;'>Erro ao carregar jogos: ${escapeHTML(error.message)}</p>`;
            // Mantém botões desabilitados em caso de erro total? Ou reabilita com valores antigos?
            // Reabilitar com valores antigos pode ser confuso. Melhor deixar desabilitado.
        } finally {
             // Remove classe de carregamento
             gamesListContainer.classList.remove('loading');
        }
    };

    // Função para renderizar a lista completa de jogos
    const renderGamesList = (partidas) => {
        if (!partidas || partidas.length === 0) {
            gamesListContainer.innerHTML = '<p>Nenhum jogo encontrado para esta rodada.</p>';
            return;
        }

        // Constrói o HTML usando a função renderGameBlockJS
        const gamesHTML = partidas.map(jogo => renderGameBlockJS(jogo)).join('');
        // Envolve a lista na div .games-list
        gamesListContainer.innerHTML = `<div class='games-list'>${gamesHTML}</div>`;
    };

    const updateNavButtons = () => {
        prevButton.disabled = !rodadaAnteriorInput.value;
        nextButton.disabled = !rodadaProximaInput.value;
    };

    // Event Listeners para os botões
    if (prevButton) {
        prevButton.addEventListener('click', () => {
            const rodadaAnterior = rodadaAnteriorInput.value;
            fetchAndRenderRodada(rodadaAnterior);
        });
    }

    if (nextButton) {
        nextButton.addEventListener('click', () => {
            const rodadaProxima = rodadaProximaInput.value;
            fetchAndRenderRodada(rodadaProxima);
        });
    }

    // Garante que os botões estejam no estado correto no carregamento inicial
    updateNavButtons();
});