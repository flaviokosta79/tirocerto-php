# php/Dockerfile
FROM php:8.2-apache

# Instala dependências do sistema para a extensão intl (Internationalization)
# e outras extensões comuns que podem ser úteis (como gd para imagens)
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Instala extensões PHP necessárias
# mysqli: para conexão procedural/orientada a objetos com MySQL
# pdo: PHP Data Objects, uma camada de abstração de acesso a dados
# pdo_mysql: driver PDO específico para MySQL
# intl: para funcionalidades de internacionalização (formatação de data/hora, etc.)
# zip: para manipulação de arquivos zip
RUN docker-php-ext-configure intl && \
    docker-php-ext-install mysqli pdo pdo_mysql intl zip && \
    docker-php-ext-enable mysqli intl

# Habilita mod_rewrite para URLs amigáveis (útil para frameworks ou roteamento personalizado)
RUN a2enmod rewrite

# Copia o arquivo de configuração do Virtual Host do Apache
COPY apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Define o diretório de trabalho padrão dentro do container
WORKDIR /var/www/html

# O código-fonte será montado via volume no docker-compose.yml