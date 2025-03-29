# Tiro Certo - Fantasy Game

Aplicação completa para palpites esportivos com sistema de pontuação.

## Pré-requisitos

- Docker Desktop instalado (versão 2.0 ou superior)
- Git instalado
- Windows 10 ou superior
- Visual Studio Code (recomendado)

## Configuração do Ambiente

1. **Clone o repositório**:
```bash
git clone https://github.com/flaviokosta79/tirocerto-php.git
cd tirocerto-php
```

2. **Configure as variáveis de ambiente**:
   - Copie o arquivo de exemplo:
   ```bash
   cp .env.example .env
   ```
   
   - Edite o arquivo `.env` com as seguintes configurações:
   ```bash
   # Configurações do Banco de Dados MySQL
   MYSQL_ROOT_PASSWORD=rootsecret
   MYSQL_DATABASE=tirocerto_db
   MYSQL_USER=tirocerto_user
   MYSQL_PASSWORD=usersecret

   # Chave da API Externa (api-futebol.com.br)
   API_FOOTBALL_KEY=sua_chave_aqui

   # Limite de requisições diárias para a API Externa
   API_REQUEST_LIMIT=100
   ```

3. **Inicie os serviços**:
```bash
# Inicie todos os containers
docker-compose up -d --build
```

4. **Verifique se os serviços estão rodando**:
```bash
docker ps
```

## Acessando a Aplicação

- Frontend: http://localhost:8080
- API Node.js (cache): http://localhost:3000
- Banco de dados MySQL: localhost:3306

## Comandos Úteis

### Gerenciamento dos Containers

```bash
# Iniciar todos os serviços
docker-compose up -d --build

# Parar todos os serviços
docker-compose down

# Reiniciar um serviço específico
docker-compose restart [nome_do_servico]

# Visualizar logs em tempo real
docker-compose logs -f

# Limpar cache e reconstruir tudo
docker-compose down -v
docker-compose up -d --build
```

### Acesso ao Banco de Dados

```bash
# Acessar o MySQL
docker exec -it tirocerto_mysql mysql -u root -prootsecret

# Verificar tabelas existentes
USE tirocerto_db;
SHOW TABLES;

# Limpar cache
docker exec -it tirocerto_mysql mysql -u root -prootsecret -e 'USE tirocerto_db; DELETE FROM cache_data;'
```

## Estrutura do Projeto

- `nodejs/`: Serviço de cache Node.js
- `php/`: Aplicação principal em PHP
- `mysql/`: Scripts de inicialização do banco de dados
- `docker-compose.yml`: Configuração dos serviços Docker

## Solução de Problemas

### Erro de Conexão com MySQL
- Verifique se o container do MySQL está rodando
- Confira as credenciais no `.env`
- Aguarde alguns segundos após iniciar os serviços para que o MySQL inicialize completamente

### Tabelas não existentes
- Execute os scripts SQL de inicialização:
```bash
docker exec -it tirocerto_mysql mysql -u root -prootsecret -e 'USE tirocerto_db; CREATE TABLE IF NOT EXISTS cache_data (cache_key VARCHAR(255) NOT NULL, data JSON NOT NULL, expires_at TIMESTAMP NOT NULL, PRIMARY KEY (cache_key), INDEX idx_expires_at (expires_at)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
```

### Problemas com o Cache
- Verifique se o serviço Node.js está rodando
- Limpe o cache se necessário
- Verifique os logs do serviço Node.js:
```bash
docker-compose logs nodejs-cache
```

## Contribuição

1. Faça um fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## Licença

Este projeto está sob a licença MIT. Veja o arquivo LICENSE para mais detalhes.