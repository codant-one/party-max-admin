# Usa una imagen base ligera de Node
FROM node:20-alpine

# Establece el directorio de trabajo dentro del contenedor
WORKDIR /app

# Copia los archivos de dependencias
COPY package*.json ./

# Instala las dependencias
RUN npm install

# Copia el resto del proyecto
COPY . .

# Expone el puerto por defecto de Vite
EXPOSE 5173

# Ejecuta el servidor de desarrollo
CMD ["npm", "run", "dev", "--", "--host"]
