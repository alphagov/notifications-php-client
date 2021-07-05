#!/bin/bash
DOCKER_IMAGE_NAME=notifications-php-client

docker run \
  --rm \
  -v "`pwd`:/var/project" \
  --env-file docker.env \
  -it \
  ${DOCKER_IMAGE_NAME} \
  ${@}
