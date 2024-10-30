#!/bin/bash

# chmod +x start_moodle_unix.sh

export MOODLE_DOCKER_WWWROOT=./server/moodle
export MOODLE_DOCKER_DB=mariadb

echo "Starting Moodle Docker Compose services..."
server/bin/moodle-docker-compose up
echo "Moodle services started."
