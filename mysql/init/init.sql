-- Criação da tabela cache_data
CREATE TABLE IF NOT EXISTS cache_data (
    cache_key VARCHAR(255) NOT NULL,
    data JSON NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    PRIMARY KEY (cache_key),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
