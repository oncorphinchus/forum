# Deployment Guide

This guide will help you deploy your Forum application with PostgreSQL support.

## Prerequisites

- A PostgreSQL database (set up on render.com or another hosting provider)
- PHP with PDO and PDO_PGSQL extensions enabled
- Web server (Apache, Nginx, etc.)

## Configuration Steps

### 1. Environment Variables

Make sure the following environment variables are set on your hosting provider:

```
IS_PRODUCTION=true
DATABASE_URL=your_postgresql_host
DATABASE_USER=your_postgresql_user
DATABASE_PASSWORD=your_postgresql_password
DATABASE_NAME=your_postgresql_database
DATABASE_PORT=5432  # Default PostgreSQL port
```

### 2. PHP Extensions

Ensure your hosting provider has the following PHP extensions installed and enabled:

- pdo
- pdo_pgsql
- pgsql

### 3. Deployment Process

1. Upload all your files to the hosting provider
2. Make sure the config.php file is using the PostgreSQL configuration
3. Run db_init.php to initialize your database schema if needed

## Troubleshooting

### Connection Issues

If you see "Connection refused" errors:

1. Verify the database credentials are correct
2. Check if your hosting provider allows external connections to PostgreSQL
3. Ensure your IP is allowlisted in the database firewall settings
4. Check if the PDO and PDO_PGSQL extensions are installed

### SQL Syntax Errors

PostgreSQL has some differences from MySQL:

1. Use SERIAL instead of AUTO_INCREMENT
2. Use double quotes for table/column names when they conflict with reserved words
3. Use || for string concatenation instead of CONCAT()
4. Column names are case-sensitive in PostgreSQL

## Database Schema Changes

When migrating from MySQL to PostgreSQL, you'll need to:

1. Change INT to INTEGER for data types
2. Replace ENUM with CHECK constraints
3. Remove any MySQL-specific syntax like ON UPDATE CURRENT_TIMESTAMP
4. Replace INDEX declarations with CREATE INDEX statements

## Post-Deployment Steps

After successful deployment:

1. Create a database backup schedule
2. Set up proper logging
3. Configure error reporting for production 