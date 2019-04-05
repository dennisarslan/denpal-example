ARG CLI_IMAGE
FROM ${CLI_IMAGE} as cli

#FROM amazeeio/php:7.2-fpm
FROM amazeeiodevelopment/denpal_php

COPY --from=cli /app /app
