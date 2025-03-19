<?php
// Script to copy PostgreSQL configuration to config.php
if (file_exists('config.pgsql.php')) {
    // Backup the original config if it doesn't exist
    if (!file_exists('config.original.php') && file_exists('config.php')) {
        copy('config.php', 'config.original.php');
        echo "Original configuration backed up to config.original.php\n";
    }
    
    // Copy the PostgreSQL config to config.php
    if (copy('config.pgsql.php', 'config.php')) {
        echo "PostgreSQL configuration activated successfully!\n";
    } else {
        echo "Failed to activate PostgreSQL configuration.\n";
    }
} else {
    echo "Error: config.pgsql.php not found.\n";
}
?> 