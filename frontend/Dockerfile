# Frontend runtime: Node 26 (Nuxt 4.5 engines: ^22.19 || ^24.11 || >=26)
FROM node:26-alpine AS dev

WORKDIR /app
ENV NUXT_TELEMETRY_DISABLED=1

# Pre-create node_modules owned by `node`: docker seeds an empty named volume from
# the image path, ownership included, so the volume is writable by the runtime user.
RUN mkdir -p /app/node_modules && chown -R node:node /app

# node:* images ship a uid 1000 "node" user; run as it so bind-mounted
# node_modules stay writable from the host.
USER node

EXPOSE 3000
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]
