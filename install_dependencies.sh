#!/bin/bash

# Check if composer is installed
if ! command -v composer &> /dev/null; then
    echo "Composer is not installed. Please install Composer first."
    echo "Visit https://getcomposer.org/download/ for installation instructions."
    exit 1
fi

# Install dependencies
echo "Installing dependencies..."
composer install

echo "Dependencies installed successfully!"
echo "You can now use Google OAuth and other OAuth providers." 