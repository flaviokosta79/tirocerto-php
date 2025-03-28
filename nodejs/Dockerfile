# nodejs/Dockerfile
# Use uma imagem oficial do Node.js. Escolha a versão LTS mais recente ou a que preferir.
FROM node:18-alpine

# Cria o diretório da aplicação dentro do container
WORKDIR /usr/src/app

# Copia package.json e package-lock.json (se existir)
# Copiar esses arquivos separadamente aproveita o cache do Docker.
# Se eles não mudarem, o 'npm install' não será executado novamente em builds subsequentes.
COPY package*.json ./

# Instala as dependências da aplicação
# Use 'npm ci' para uma instalação mais rápida e confiável em ambientes de CI/CD
# ou 'npm install' para desenvolvimento.
RUN npm install
# Se você estiver em produção, considere 'npm ci --only=production'

# Copia o restante do código da aplicação para o diretório de trabalho
COPY . .

# Expõe a porta que a aplicação Node.js vai usar (definida no docker-compose.yml também)
EXPOSE 3000

# Comando padrão para iniciar a aplicação (pode ser sobrescrito no docker-compose.yml)
CMD [ "node", "server.js" ]