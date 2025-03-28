-- mysql/init/01-schema.sql

-- O banco de dados definido em MYSQL_DATABASE já é selecionado
-- pelo script de entrada do container MySQL.

-- Tabela de Usuários
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `reset_token` VARCHAR(255) NULL,
    `reset_token_expires_at` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Times
CREATE TABLE IF NOT EXISTS `teams` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `api_team_id` INT UNIQUE, -- ID da API externa para referência
    `name` VARCHAR(255) NOT NULL,
    `short_name` VARCHAR(50),
    `logo_url` VARCHAR(512),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Jogadores
CREATE TABLE IF NOT EXISTS `players` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `api_player_id` INT UNIQUE, -- ID da API externa
    `name` VARCHAR(255) NOT NULL,
    `position` VARCHAR(50), -- Ex: 'Atacante', 'Meio-campista', 'Defensor', 'Goleiro'
    `team_id` INT, -- Pode ser NULL se o jogador estiver sem clube ou a informação não for relevante inicialmente
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`team_id`) REFERENCES `teams`(`id`) ON DELETE SET NULL -- Se o time for deletado, mantém o jogador mas sem time
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Rodadas
CREATE TABLE IF NOT EXISTS `rounds` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL, -- Ex: "Rodada 1", "Rodada 2"
    `api_round_name` VARCHAR(255) UNIQUE, -- Nome da rodada na API externa (se houver)
    `start_date` DATETIME,
    `end_date` DATETIME,
    `status` ENUM('SCHEDULED', 'OPEN', 'CLOSED', 'FINISHED') DEFAULT 'SCHEDULED', -- SCHEDULED: Futura, OPEN: Palpites abertos, CLOSED: Palpites fechados, FINISHED: Resultados apurados
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Jogos
CREATE TABLE IF NOT EXISTS `games` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `api_game_id` INT UNIQUE, -- ID do jogo na API externa
    `round_id` INT NOT NULL,
    `home_team_id` INT NOT NULL,
    `away_team_id` INT NOT NULL,
    `game_datetime` DATETIME NOT NULL,
    `status` ENUM('SCHEDULED', 'LIVE', 'FINISHED', 'POSTPONED', 'CANCELLED') DEFAULT 'SCHEDULED', -- Status do jogo
    `home_score` INT NULL, -- Placar final (inserido pelo admin ou API)
    `away_score` INT NULL, -- Placar final (inserido pelo admin ou API)
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`round_id`) REFERENCES `rounds`(`id`) ON DELETE CASCADE, -- Se a rodada for deletada, os jogos também são
    FOREIGN KEY (`home_team_id`) REFERENCES `teams`(`id`) ON DELETE RESTRICT, -- Não permite deletar time se ele tem jogos
    FOREIGN KEY (`away_team_id`) REFERENCES `teams`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Palpites (Bets)
CREATE TABLE IF NOT EXISTS `bets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `game_id` INT NOT NULL,
    `predicted_result` ENUM('H', 'D', 'A') NOT NULL, -- H: Home win, D: Draw, A: Away win
    `predicted_home_score` INT NOT NULL,
    `predicted_away_score` INT NOT NULL,
    `points_result` INT DEFAULT 0, -- Pontos ganhos pelo resultado (100 ou 0)
    `points_score` INT DEFAULT 0, -- Pontos ganhos pelo placar exato (200 ou 0)
    `points_players` INT DEFAULT 0, -- Pontos ganhos pelos jogadores que marcaram
    `total_points` INT GENERATED ALWAYS AS (`points_result` + `points_score` + `points_players`) STORED, -- Coluna calculada
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `user_game_bet` (`user_id`, `game_id`), -- Garante que um usuário só faça um palpite por jogo
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE, -- Se o usuário for deletado, seus palpites também são
    FOREIGN KEY (`game_id`) REFERENCES `games`(`id`) ON DELETE CASCADE -- Se o jogo for deletado, os palpites também são
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela associativa para Jogadores que o usuário palpitou que marcariam gol
CREATE TABLE IF NOT EXISTS `bet_players` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `bet_id` INT NOT NULL,
    `player_id` INT NOT NULL,
    `is_correct` BOOLEAN NULL, -- Marcado como true/false após apuração do resultado
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `bet_player_unique` (`bet_id`, `player_id`), -- Garante que o mesmo jogador não seja adicionado duas vezes ao mesmo palpite
    FOREIGN KEY (`bet_id`) REFERENCES `bets`(`id`) ON DELETE CASCADE, -- Se o palpite for deletado, essa associação também é
    FOREIGN KEY (`player_id`) REFERENCES `players`(`id`) ON DELETE CASCADE -- Se o jogador for deletado, essa associação também é
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela para armazenar os resultados reais (inseridos pelo admin ou via API)
CREATE TABLE IF NOT EXISTS `game_results` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `game_id` INT NOT NULL UNIQUE, -- Garante um resultado por jogo
    `actual_home_score` INT NOT NULL,
    `actual_away_score` INT NOT NULL,
    `processed` BOOLEAN DEFAULT FALSE, -- Indica se os pontos dos palpites já foram calculados para este resultado
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`game_id`) REFERENCES `games`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela para armazenar os jogadores que realmente marcaram gols (inseridos pelo admin ou via API)
CREATE TABLE IF NOT EXISTS `game_scorers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `game_id` INT NOT NULL,
    `player_id` INT NOT NULL,
    `team_id` INT NOT NULL, -- Time que marcou o gol (útil para gols contra, talvez?)
    `minute` VARCHAR(10), -- Minuto do gol (pode ser '45+2', por isso VARCHAR)
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`game_id`) REFERENCES `games`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`player_id`) REFERENCES `players`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`team_id`) REFERENCES `teams`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabelas para o Cache do Node.js (Estrutura inicial, pode ser refinada)
-- Armazenar a resposta JSON completa pode ser mais simples inicialmente.
CREATE TABLE IF NOT EXISTS `cache_data` (
    `cache_key` VARCHAR(255) PRIMARY KEY, -- Ex: 'games_round_1', 'players_team_5', 'standings_league_10'
    `data` JSON NOT NULL,
    `fetched_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `expires_at` TIMESTAMP NOT NULL -- Calculado no Node.js (fetched_at + TTL)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela para log de requisições da API (Controle de limite)
CREATE TABLE IF NOT EXISTS `api_request_log` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `request_timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `endpoint` VARCHAR(255), -- Qual endpoint da API externa foi chamado
    `status_code` INT -- Código de status da resposta da API externa
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Adicionar índices para otimizar consultas comuns
ALTER TABLE `games` ADD INDEX `idx_game_datetime` (`game_datetime`);
ALTER TABLE `bets` ADD INDEX `idx_bet_game_id` (`game_id`);
ALTER TABLE `bet_players` ADD INDEX `idx_betplayer_bet_id` (`bet_id`);
ALTER TABLE `game_scorers` ADD INDEX `idx_scorer_game_id` (`game_id`);
ALTER TABLE `cache_data` ADD INDEX `idx_cache_expires_at` (`expires_at`);