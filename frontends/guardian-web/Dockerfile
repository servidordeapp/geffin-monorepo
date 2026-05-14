FROM node:24-alpine
WORKDIR /app

COPY package.json ./
COPY shared/design-system/package.json ./shared/design-system/
COPY frontends/guardian-web/package.json ./frontends/guardian-web/

RUN npm install

WORKDIR /app/frontends/guardian-web
EXPOSE 3000
CMD ["npm", "run", "dev"]
