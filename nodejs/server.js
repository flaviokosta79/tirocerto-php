// nodejs/server.js
require('dotenv').config();
const express = require('express');
const mysql = require('mysql2/promise');
const axios = require('axios');
const cron = require('node-cron');
const cors = require('cors');

const app = express();
app.use(cors());
app.use(express.json());

// Configurações do MySQL
const dbConfig = {
    host: process.env.MYSQL_HOST || 'mysql',
    user: process.env.MYSQL_USER || 'root',
    password: process.env.MYSQL_PASSWORD || 'password',
    database: process.env.MYSQL_DATABASE || 'tirocerto',
    port: process.env.MYSQL_PORT || 3306
};

// Configurações da API externa
const API_BASE_URL = process.env.API_BASE_URL || 'https://api.api-futebol.com.br/v1';
const API_KEY = process.env.API_FOOTBALL_KEY || '';

// Configuração do Axios para a API externa
const api = axios.create({
    baseURL: API_BASE_URL,
    headers: {
        'Authorization': `Bearer ${API_KEY}`,
        'Content-Type': 'application/json'
    }
});

// Tempos de cache em segundos (24 horas)
const CACHE_TEMPO_TABELA = 86400; // 24 horas
const CACHE_TEMPO_RODADA = 86400; // 24 horas

// Conecta ao MySQL com retentativas
let db;
const MAX_RETRIES = 5;
const RETRY_DELAY = 5000; // 5 segundos

async function connectDB() {
    let retries = 0;
    while (retries < MAX_RETRIES) {
        try {
            db = await mysql.createConnection(dbConfig);
            console.log('Conectado ao banco de dados MySQL (Node.js)');
            return;
        } catch (err) {
            retries++;
            console.error(`Tentativa ${retries}/${MAX_RETRIES} - Erro ao conectar ao MySQL:`, err.message);
            if (retries < MAX_RETRIES) {
                console.log(`Aguardando ${RETRY_DELAY/1000} segundos antes de tentar novamente...`);
                await new Promise(resolve => setTimeout(resolve, RETRY_DELAY));
            } else {
                console.error('Número máximo de tentativas alcançado. Encerrando aplicação.');
                process.exit(1);
            }
        }
    }
}

// Função para buscar dados da API e salvar no cache
async function fetchAndCache(endpoint, cacheKey, cacheTime) {
    try {
        console.log(`Cache MISS para ${cacheKey}. Buscando na API: ${endpoint}`);
        const response = await api.get(endpoint);
        const data = response.data;
        
        // Verifica se os dados estão em formato válido
        if (typeof data !== 'object' || data === null) {
            throw new Error('Dados retornados pela API não estão em formato válido');
        }
        
        // Converte para string JSON
        const jsonString = JSON.stringify(data);
        
        // Salva no cache
        await db.execute(
            'INSERT INTO cache_data (cache_key, data, expires_at) VALUES (?, ?, ?) ' +
            'ON DUPLICATE KEY UPDATE data = ?, expires_at = ?',
            [
                cacheKey,
                jsonString,
                new Date(Date.now() + cacheTime * 1000),
                jsonString,
                new Date(Date.now() + cacheTime * 1000)
            ]
        );
        console.log(`Cache SAVED para ${cacheKey}`);
        return data;
    } catch (error) {
        console.error(`Erro ao buscar/processar dados para ${cacheKey} (API: ${endpoint}):`, error.message);
        throw error;
    }
}

// Função para obter dados do cache (com fallback para API se necessário)
async function getCachedData(endpoint, cacheKey, cacheTime) {
    try {
        // Tenta pegar do cache
        const [rows] = await db.execute(
            'SELECT data FROM cache_data WHERE cache_key = ? AND expires_at > NOW()',
            [cacheKey]
        );
        
        if (rows.length > 0) {
            console.log(`Cache HIT para ${cacheKey}`);
            try {
                const parsedData = JSON.parse(rows[0].data);
                return parsedData;
            } catch (parseError) {
                console.error(`Erro ao parsear JSON do cache para ${cacheKey}:`, parseError.message);
                // Se o JSON estiver corrompido, tenta buscar na API
                return await fetchAndCache(endpoint, cacheKey, cacheTime);
            }
        }
        
        // Se não tiver no cache ou estiver expirado, busca na API
        return await fetchAndCache(endpoint, cacheKey, cacheTime);
    } catch (error) {
        console.error(`Erro ao acessar cache para ${cacheKey}:`, error.message);
        
        // Tenta retornar cache expirado como fallback
        try {
            const [rows] = await db.execute(
                'SELECT data FROM cache_data WHERE cache_key = ? ORDER BY expires_at DESC LIMIT 1',
                [cacheKey]
            );
            if (rows.length > 0) {
                console.log(`Retornando cache antigo para ${cacheKey} devido a erro na API.`);
                try {
                    const parsedData = JSON.parse(rows[0].data);
                    return parsedData;
                } catch (parseError) {
                    console.error(`Erro ao parsear JSON do cache antigo para ${cacheKey}:`, parseError.message);
                    // Se o JSON estiver corrompido, tenta buscar na API
                    return await fetchAndCache(endpoint, cacheKey, cacheTime);
                }
            }
        } catch (fallbackError) {
            console.error('Erro no fallback do cache:', fallbackError.message);
        }
        
        throw error;
    }
}

// Rotas da API de cache
app.get('/api/cache/campeonatos/:campeonatoId/tabela', async (req, res) => {
    try {
        const campeonatoId = req.params.campeonatoId;
        const cacheKey = `tabela_campeonato_${campeonatoId}`;
        const endpoint = `/campeonatos/${campeonatoId}/tabela`;
        
        const data = await getCachedData(endpoint, cacheKey, CACHE_TEMPO_TABELA);
        res.json(data);
    } catch (error) {
        res.status(500).json({ error: true, message: error.message });
    }
});

app.get('/api/cache/campeonatos/:campeonatoId/rodada/:rodadaNumero', async (req, res) => {
    try {
        const { campeonatoId, rodadaNumero } = req.params;
        const cacheKey = `jogos_campeonato_${campeonatoId}_rodada_${rodadaNumero}`;
        const endpoint = `/campeonatos/${campeonatoId}/rodadas/${rodadaNumero}`;
        
        const data = await getCachedData(endpoint, cacheKey, CACHE_TEMPO_RODADA);
        res.json(data);
    } catch (error) {
        res.status(500).json({ error: true, message: error.message });
    }
});

// Jobs agendados para manter o cache atualizado
// Tabela - atualiza uma vez por dia às 8h
cron.schedule('0 8 * * *', async () => {
    try {
        console.log('Executando cron job para atualizar cache da tabela (Brasileirão)...');
        const campeonatoId = 10; // ID do Brasileirão
        const cacheKey = `tabela_campeonato_${campeonatoId}`;
        const endpoint = `/campeonatos/${campeonatoId}/tabela`;
        
        await fetchAndCache(endpoint, cacheKey, CACHE_TEMPO_TABELA);
        console.log('Cache da tabela atualizado via cron.');
    } catch (error) {
        console.error('Erro no cron job da tabela:', error.message);
    }
});

// Rodadas - atualiza uma vez por dia às 8h
cron.schedule('0 8 * * *', async () => {
    try {
        console.log('Executando cron job para atualizar cache de rodadas (Brasileirão)...');
        const campeonatoId = 10; // ID do Brasileirão
        
        // Primeiro busca a rodada atual para saber quantas rodadas existem
        const rodadaAtual = await getCachedData(
            `/campeonatos/${campeonatoId}/rodadas/atual`,
            `rodada_atual_campeonato_${campeonatoId}`,
            CACHE_TEMPO_RODADA
        );
        
        // Atualiza as últimas 5 rodadas (atual, +2 e -2)
        const rodadaNum = rodadaAtual.rodada;
        const rodadasParaAtualizar = [
            Math.max(1, rodadaNum - 2),
            Math.max(1, rodadaNum - 1),
            rodadaNum,
            rodadaNum + 1,
            rodadaNum + 2
        ];
        
        for (const rodada of rodadasParaAtualizar) {
            try {
                const cacheKey = `jogos_campeonato_${campeonatoId}_rodada_${rodada}`;
                const endpoint = `/campeonatos/${campeonatoId}/rodadas/${rodada}`;
                await fetchAndCache(endpoint, cacheKey, CACHE_TEMPO_RODADA);
                console.log(`Cache de jogos da rodada ${rodada} atualizado via cron.`);
            } catch (err) {
                console.error(`Erro ao atualizar cache da rodada ${rodada}:`, err.message);
            }
        }
    } catch (error) {
        console.error('Erro no cron job das rodadas:', error.message);
    }
});

// Inicia o servidor
const PORT = process.env.PORT || 3000;
connectDB().then(() => {
    app.listen(PORT, () => {
        console.log(`Serviço de Cache Node.js rodando na porta ${PORT}`);
    });
});