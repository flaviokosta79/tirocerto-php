# Tiro Certo - Fantasy Game

Aplicação completa para palpites esportivos com sistema de pontuação.

## Pré-requisitos
- Docker e Docker Compose instalados
- Arquivo `.env` configurado (veja `.env.example`)

## Como iniciar os serviços

1. **Configuração inicial**:
```bash
# Copie o arquivo de exemplo de variáveis de ambiente
cp .env.example .env
```

2. **Edite o arquivo .env**:
   - Configure as credenciais do MySQL
   - Adicione sua chave da API de futebol

3. **Inicie todos os containers**:
```bash
docker-compose up -d --build
```

4. **Verifique os logs**:
```bash
# Verifique o MySQL
docker logs tirocerto_mysql

# Verifique o Node.js (cache)
docker logs tirocerto_nodejs_cache

# Verifique o PHP/Apache
docker logs tirocerto_php_apache
```

5. **Acesse a aplicação**:
- Frontend: http://localhost:8080
- API Node.js (cache): http://localhost:3000

## Comandos úteis

**Parar todos os serviços**:
```bash
docker-compose down
```

**Reiniciar um serviço específico**:
```bash
docker-compose restart [nome_do_servico]
```

**Visualizar logs em tempo real**:
```bash
docker-compose logs -f
```

## Estrutura de serviços
- **MySQL**: Banco de dados na porta 3306
- **Node.js**: Serviço de cache na porta 3000
- **PHP/Apache**: Aplicação principal na porta 8080

## Solução de problemas

**Erro de conexão com MySQL**:
- Verifique se o container do MySQL está rodando
- Confira as credenciais no `.env`

**Tabelas não existentes**:
Execute os scripts SQL de inicialização:
```bash
docker exec -i tirocerto_mysql mysql -u root -p < mysql/init/01-schema.sql
```

**Limpar e reconstruir tudo**:
```bash
docker-compose down -v
docker-compose up -d --build