<?php
// Script to copy Docker configuration to config.php
if (file_exists('config.docker.php')) {
    // Backup the original config if it doesn't exist
    if (!file_exists('config.original.php') && file_exists('config.php')) {
        copy('config.php', 'config.original.php');
        echo "Original configuration backed up to config.original.php\n";
    }
    
    // Copy the Docker config to config.php
    if (copy('config.docker.php', 'config.php')) {
        echo "Docker configuration activated successfully!\n";
    } else {
        echo "Failed to activate Docker configuration.\n";
    }
} else {
    echo "Error: config.docker.php not found.\n";
}
?> 