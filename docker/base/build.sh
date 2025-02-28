#!/usr/bin/env bash
set -x
set -e

PHP_VERSION=8.2

TAG=$PHP_VERSION-$(git rev-parse HEAD | head -c 7)

#docker login -u $DOCKER_USER -p $DOCKER_PASS

export WWWUSER=${WWWUSER:-$UID}
export WWWGROUP=${WWWGROUP:-$(id -g)}
export PHP_VERSION=${PHP_VERSION:-8.2}

docker build \
    --build-arg WWWUSER=$WWWUSER \
    --build-arg WWWGROUP=$WWWGROUP \
    --build-arg PHP_VERSION=$PHP_VERSION\
    -t ghcr.io/austinkregel/base:${TAG} .

docker tag ghcr.io/austinkregel/base:${TAG} ghcr.io/austinkregel/base:php${PHP_VERSION}

docker push ghcr.io/austinkregel/base:${TAG}
docker push ghcr.io/austinkregel/base:php${PHP_VERSION}
