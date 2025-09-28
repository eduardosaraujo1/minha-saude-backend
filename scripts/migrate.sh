#!/bin/bash

# Exit immediately if a command exits with a non-zero status
set -e

# Run migrations and seed the database
php artisan migrate:fresh --seed