FROM node:24-alpine
WORKDIR /app

# Copy workspace manifests (context = monorepo root)
COPY package.json ./
COPY shared/design-system/package.json ./shared/design-system/
COPY frontends/school-web/package.json ./frontends/school-web/

# Install all workspace deps (hoisted to /app/node_modules)
RUN npm install

WORKDIR /app/frontends/school-web
EXPOSE 3003
CMD ["npm", "run", "dev"]
