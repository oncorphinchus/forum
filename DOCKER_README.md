# Docker Setup for Login System

This document provides instructions on how to run the Login System application using Docker.

## Prerequisites

- [Docker](https://docs.docker.com/get-docker/)
- [Docker Compose](https://docs.docker.com/compose/install/)

## Getting Started

Follow these steps to get your application running in Docker containers:

### 1. Activate Docker Configuration

Before starting the containers, you need to switch to the Docker database configuration:

```bash
# Run this PHP script to activate Docker configuration
php use_docker_config.php
```

This will create a backup of your original configuration and replace it with Docker-specific settings.

### 2. Start the Docker Containers

```bash
# Build and start the containers
docker-compose up -d

# To see the logs
docker-compose logs -f
```

### 3. Access the Application

The application will be available at: http://localhost:8080

### 4. Database Access

You can connect to the MySQL database using these credentials:

- Host: localhost
- Port: 3306
- Database: login_system
- Username: user
- Password: password
- Root Password: rootpassword

### 5. Stopping the Containers

```bash
# Stop and remove the containers
docker-compose down

# To also remove volumes (this will delete all data in the database)
docker-compose down -v
```

### 6. Restore Original Configuration

If you want to switch back to your original configuration for local development:

```bash
# Run this PHP script to restore original configuration
php restore_original_config.php
```

## Troubleshooting

### Database Connection Issues

If you experience database connection issues:

1. Ensure the database container is running: `docker-compose ps`
2. Check database logs: `docker-compose logs db`
3. Verify the configuration in `config.docker.php`

### File Permission Issues

If you encounter file permission issues with uploads:

```bash
# Access the web container
docker-compose exec web bash

# Fix permissions inside the container
chmod -R 777 /var/www/html/uploads
```

## Additional Commands

### Execute Commands in Containers

```bash
# Execute command in web container
docker-compose exec web <command>

# Execute command in database container
docker-compose exec db <command>

# Get a shell in the web container
docker-compose exec web bash

# Get a shell in the database container
docker-compose exec db bash
```

### View Container Logs

```bash
# View logs for all containers
docker-compose logs

# View logs for a specific container
docker-compose logs web
docker-compose logs db

# Follow logs (continuous output)
docker-compose logs -f
``` 