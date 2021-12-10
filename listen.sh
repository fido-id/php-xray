#!/bin/bash

echo "Build image..."
image_name="php-xray-d"
dockerfile="Dockerfile.daemon"

docker build \
  --tag "${image_name}" \
  --file "${dockerfile}" \
  --build-arg UID="$(id -u)" \
  "."

echo " done!\n"

docker run \
      --rm \
      -v ~/php-xray/.aws:/root/.aws/:ro \
      --attach STDOUT \
      --net=host \
      --name xray-daemon \
      -p 2000:2000/udp \
      php-xray-d
