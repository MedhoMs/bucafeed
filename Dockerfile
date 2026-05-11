FROM mirror.gcr.io/library/node:20-alpine
WORKDIR /app

# Instalar dependencias del sistema necesarias para compilar paquetes nativos
RUN apk add --no-cache python3 make g++

# Copiar solo los manifiestos primero → Docker cachea la capa de deps
# si el código cambia pero package.json no, no reinstala nada
COPY package*.json ./
RUN npm install

# Copiar el resto del código
COPY . .

# Exponer puerto de Vite
EXPOSE 5173

# Comando de inicio para desarrollo con HMR habilitado
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]
