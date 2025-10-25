#!/bin/bash

# Exit immediately if a command exits with a non-zero status
set -e

# Navigate to the project root directory
cd "$(dirname "$0")/.."

# Copy .env.example to .env if it doesn't already exist
if [ ! -f .env ]; then
    echo "Copying .env.example to .env"
    cp .env.example .env
else
    echo ".env already exists, skipping copy."
fi

# Install composer and npm dependencies
composer install
npm i

# Generate the application key
if php artisan key:generate; then
    echo "Application key generated successfully."
else
    echo "Failed to generate application key." >&2
    exit 1
fi

# Create database file if it doesn't exist (for SQLite)
if [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
fi

# Run migrations and seed the database
php artisan migrate:fresh --seed

php artisan boost:install