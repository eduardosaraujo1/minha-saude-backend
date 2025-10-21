#!/bin/bash

# Exit immediately if a command exits with a non-zero status
set -e

# Navigate to the project root directory
cd "$(dirname "$0")/.."

# Create database file if it doesn't exist (for SQLite)
if [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
fi

# Run migrations and seed the database
php artisan migrate:fresh --seed