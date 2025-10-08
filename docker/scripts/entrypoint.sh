#!/bin/bash
# Carsharing Platform - Docker Entrypoint Script
# Date: October 2025
# Purpose: Initialize container, wait for databases, set permissions, start Apache

set -e

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Logging functions
log_info() {
    echo -e "${GREEN}[INFO]${NC} $(date '+%Y-%m-%d %H:%M:%S') - $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $(date '+%Y-%m-%d %H:%M:%S') - $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $(date '+%Y-%m-%d %H:%M:%S') - $1"
}

# Function to wait for MariaDB to be ready
wait_for_mariadb() {
    log_info "Waiting for MariaDB to be ready..."
    
    local max_attempts=30
    local attempt=0
    
    while [ $attempt -lt $max_attempts ]; do
        if nc -z ${DB_HOST:-mariadb} ${DB_PORT:-3306} 2>/dev/null; then
            log_info "MariaDB is accepting connections"
            
            # Additional check: try to connect with mysql client
            sleep 2
            log_info "MariaDB is ready!"
            return 0
        fi
        
        attempt=$((attempt + 1))
        log_warn "MariaDB not ready yet (attempt $attempt/$max_attempts)..."
        sleep 2
    done
    
    log_error "MariaDB failed to become ready after $max_attempts attempts"
    return 1
}

# Function to wait for MongoDB to be ready
wait_for_mongodb() {
    log_info "Waiting for MongoDB to be ready..."
    
    local max_attempts=30
    local attempt=0
    
    while [ $attempt -lt $max_attempts ]; do
        if nc -z ${MONGO_HOST:-mongodb} ${MONGO_PORT:-27017} 2>/dev/null; then
            log_info "MongoDB is accepting connections"
            sleep 2
            log_info "MongoDB is ready!"
            return 0
        fi
        
        attempt=$((attempt + 1))
        log_warn "MongoDB not ready yet (attempt $attempt/$max_attempts)..."
        sleep 2
    done
    
    log_error "MongoDB failed to become ready after $max_attempts attempts"
    return 1
}

# Function to set proper file permissions
set_permissions() {
    log_info "Setting proper file permissions..."
    
    # Ensure www-data owns the web directory
    chown -R www-data:www-data /var/www/html
    
    # Set directory permissions (755)
    find /var/www/html -type d -exec chmod 755 {} \;
    
    # Set file permissions (644)
    find /var/www/html -type f -exec chmod 644 {} \;
    
    # Make sure public directory is accessible
    chmod 755 /var/www/html/public
    
    # Ensure log directory is writable
    mkdir -p /var/log/apache2
    chown -R www-data:www-data /var/log/apache2
    chmod -R 755 /var/log/apache2
    
    log_info "File permissions set successfully"
}

# Function to create necessary directories
create_directories() {
    log_info "Creating necessary directories..."
    
    mkdir -p /var/www/html/public
    mkdir -p /var/www/html/api
    mkdir -p /var/www/html/js
    mkdir -p /var/www/html/css
    mkdir -p /var/www/html/lang
    mkdir -p /var/www/html/uploads
    mkdir -p /var/log/apache2
    
    log_info "Directories created successfully"
}

# Function to check environment variables
check_environment() {
    log_info "Checking environment variables..."
    
    local required_vars=(
        "DB_HOST"
        "DB_PORT"
        "DB_NAME"
        "DB_USER"
        "DB_PASSWORD"
        "MONGO_HOST"
        "MONGO_PORT"
        "MONGO_DB"
    )
    
    local missing_vars=()
    
    for var in "${required_vars[@]}"; do
        if [ -z "${!var}" ]; then
            missing_vars+=("$var")
        fi
    done
    
    if [ ${#missing_vars[@]} -gt 0 ]; then
        log_warn "Missing environment variables: ${missing_vars[*]}"
        log_warn "Using default values where applicable"
    else
        log_info "All required environment variables are set"
    fi
}

# Function to display startup information
display_info() {
    log_info "=========================================="
    log_info "Carsharing Platform - Web Server"
    log_info "=========================================="
    log_info "Environment: ${APP_ENV:-production}"
    log_info "PHP Version: $(php -v | head -n 1)"
    log_info "Apache Version: $(apache2 -v | head -n 1)"
    log_info "MariaDB Host: ${DB_HOST:-mariadb}:${DB_PORT:-3306}"
    log_info "MongoDB Host: ${MONGO_HOST:-mongodb}:${MONGO_PORT:-27017}"
    log_info "=========================================="
}

# Function to test database connections
test_database_connections() {
    log_info "Testing database connections..."
    
    # Test MariaDB connection
    if nc -z ${DB_HOST:-mariadb} ${DB_PORT:-3306} 2>/dev/null; then
        log_info "✓ MariaDB connection successful"
    else
        log_error "✗ MariaDB connection failed"
    fi
    
    # Test MongoDB connection
    if nc -z ${MONGO_HOST:-mongodb} ${MONGO_PORT:-27017} 2>/dev/null; then
        log_info "✓ MongoDB connection successful"
    else
        log_error "✗ MongoDB connection failed"
    fi
}

# Main execution
main() {
    log_info "Starting Carsharing Platform Web Server..."
    
    # Display startup information
    display_info
    
    # Check environment variables
    check_environment
    
    # Create necessary directories
    create_directories
    
    # Wait for databases to be ready
    if ! wait_for_mariadb; then
        log_error "Failed to connect to MariaDB. Exiting..."
        exit 1
    fi
    
    if ! wait_for_mongodb; then
        log_error "Failed to connect to MongoDB. Exiting..."
        exit 1
    fi
    
    # Test database connections
    test_database_connections

    # Ensure Composer dependencies (MongoDB PHP Library) are available when using bind mounts
    if [ ! -f "/var/www/html/vendor/autoload.php" ]; then
        log_info "Installing PHP dependencies (mongodb/mongodb) with Composer..."
        export COMPOSER_ALLOW_SUPERUSER=1
        if command -v composer >/dev/null 2>&1; then
(cd /var/www/html && composer require mongodb/mongodb --no-interaction --prefer-dist || true)
        else
            log_warn "Composer not found, skipping PHP dependencies installation"
        fi
    else
        log_info "Composer dependencies already present"
    fi
    
    # Set proper file permissions
    set_permissions
    
    log_info "Initialization complete!"
    log_info "Starting Apache HTTP Server..."
    
    # Execute the main command (Apache)
    exec "$@"
}

# Run main function
main "$@"
