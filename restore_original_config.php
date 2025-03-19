<?php
// Script to restore original configuration from backup
if (file_exists('config.original.php')) {
    // Copy the original config back to config.php
    if (copy('config.original.php', 'config.php')) {
        echo "Original configuration restored successfully!\n";
    } else {
        echo "Failed to restore original configuration.\n";
    }
} else {
    echo "Error: config.original.php not found. No backup exists.\n";
}
?> 