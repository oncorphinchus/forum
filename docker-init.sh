#!/bin/bash

# Create uploads directory if it doesn't exist
if [ ! -d "./uploads" ]; then
    echo "Creating uploads directory..."
    mkdir -p ./uploads
    chmod 777 ./uploads
    echo "Uploads directory created."
else
    echo "Uploads directory already exists."
    chmod 777 ./uploads
fi

# Activate Docker configuration
echo "Activating Docker configuration..."
php use_docker_config.php

echo "Ready to start Docker containers. Run 'docker-compose up -d' to begin." 