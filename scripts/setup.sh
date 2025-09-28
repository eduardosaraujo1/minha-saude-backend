#!/bin/bash

# Exit immediately if a command exits with a non-zero status
set -e

# Copy .env.example to .env if it doesn't already exist
if [ ! -f .env ]; then
    echo "Copying .env.example to .env"
    cp .env.example .env
else
    echo ".env already exists, skipping copy."
fi

# Generate the application key
if php artisan key:generate; then
    echo "Application key generated successfully."
else
    echo "Failed to generate application key." >&2
    exit 1
fi