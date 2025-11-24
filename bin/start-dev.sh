#! /usr/bin/env bash

docker-compose -f docker-compose.dev.yml --env-file www/.env up --build
