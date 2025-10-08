#!/bin/bash
# Carsharing Platform - MariaDB Initialization Script
# Date: October 2025
# Purpose: Initialize MariaDB database with schema and seed data

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

# Database connection parameters
DB_HOST="${DB_HOST:-mariadb}"
DB_PORT="${DB_PORT:-3306}"
DB_NAME="${DB_NAME:-carsharing}"
DB_USER="${DB_USER:-carsharing_user}"
DB_PASSWORD="${DB_PASSWORD:-carsharing_pass}"
MYSQL_ROOT_PASSWORD="${MYSQL_ROOT_PASSWORD:-root_password_change_me}"

# Paths to SQL files
SCHEMA_FILE="/docker-entrypoint-initdb.d/schema.sql"
SEED_FILE="/docker-entrypoint-initdb.d/seed.sql"

# Function to wait for MariaDB to be ready
wait_for_mariadb() {
    log_info "Waiting for MariaDB to be ready..."
    
    local max_attempts=30
    local attempt=0
    
    while [ $attempt -lt $max_attempts ]; do
        if mysql -h"$DB_HOST" -P"$DB_PORT" -uroot -p"$MYSQL_ROOT_PASSWORD" -e "SELECT 1" >/dev/null 2>&1; then
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

# Function to check if database exists
database_exists() {
    local db_name=$1
    local result=$(mysql -h"$DB_HOST" -P"$DB_PORT" -uroot -p"$MYSQL_ROOT_PASSWORD" \
        -e "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME='$db_name'" \
        -s -N 2>/dev/null)
    
    if [ -n "$result" ]; then
        return 0
    else
        return 1
    fi
}

# Function to check if tables exist
tables_exist() {
    local db_name=$1
    local result=$(mysql -h"$DB_HOST" -P"$DB_PORT" -uroot -p"$MYSQL_ROOT_PASSWORD" \
        -e "SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='$db_name'" \
        -s -N 2>/dev/null)
    
    if [ "$result" -gt 0 ]; then
        return 0
    else
        return 1
    fi
}

# Function to check if data exists
data_exists() {
    local db_name=$1
    local table_name=$2
    local result=$(mysql -h"$DB_HOST" -P"$DB_PORT" -uroot -p"$MYSQL_ROOT_PASSWORD" \
        -D"$db_name" -e "SELECT COUNT(*) FROM $table_name" -s -N 2>/dev/null)
    
    if [ "$result" -gt 0 ]; then
        return 0
    else
        return 1
    fi
}

# Function to run SQL file
run_sql_file() {
    local sql_file=$1
    local description=$2
    
    if [ ! -f "$sql_file" ]; then
        log_error "SQL file not found: $sql_file"
        return 1
    fi
    
    log_info "Running $description..."
    
    if mysql -h"$DB_HOST" -P"$DB_PORT" -uroot -p"$MYSQL_ROOT_PASSWORD" "$DB_NAME" < "$sql_file" 2>/dev/null; then
        log_info "$description completed successfully"
        return 0
    else
        log_error "$description failed"
        return 1
    fi
}

# Main initialization function
initialize_database() {
    log_info "=========================================="
    log_info "MariaDB Database Initialization"
    log_info "=========================================="
    log_info "Database: $DB_NAME"
    log_info "Host: $DB_HOST:$DB_PORT"
    log_info "=========================================="
    
    # Wait for MariaDB to be ready
    if ! wait_for_mariadb; then
        log_error "Cannot connect to MariaDB. Exiting..."
        exit 1
    fi
    
    # Check if database exists
    if database_exists "$DB_NAME"; then
        log_info "Database '$DB_NAME' already exists"
        
        # Check if tables exist
        if tables_exist "$DB_NAME"; then
            log_info "Tables already exist in database '$DB_NAME'"
            
            # Check if data exists (check users table as example)
            if data_exists "$DB_NAME" "users" 2>/dev/null; then
                log_info "Database already contains data. Skipping initialization."
                log_info "If you want to reinitialize, drop the database first."
                return 0
            else
                log_info "Tables exist but no data found. Running seed script..."
                if [ -f "$SEED_FILE" ]; then
                    run_sql_file "$SEED_FILE" "Seed data import"
                fi
            fi
        else
            log_info "No tables found. Running schema creation..."
            if [ -f "$SCHEMA_FILE" ]; then
                run_sql_file "$SCHEMA_FILE" "Schema creation"
            fi
            
            log_info "Running seed data import..."
            if [ -f "$SEED_FILE" ]; then
                run_sql_file "$SEED_FILE" "Seed data import"
            fi
        fi
    else
        log_info "Database '$DB_NAME' does not exist. Creating..."
        mysql -h"$DB_HOST" -P"$DB_PORT" -uroot -p"$MYSQL_ROOT_PASSWORD" \
            -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null
        
        log_info "Running schema creation..."
        if [ -f "$SCHEMA_FILE" ]; then
            run_sql_file "$SCHEMA_FILE" "Schema creation"
        fi
        
        log_info "Running seed data import..."
        if [ -f "$SEED_FILE" ]; then
            run_sql_file "$SEED_FILE" "Seed data import"
        fi
    fi
    
    log_info "=========================================="
    log_info "MariaDB initialization completed!"
    log_info "=========================================="
}

# Run initialization
initialize_database
