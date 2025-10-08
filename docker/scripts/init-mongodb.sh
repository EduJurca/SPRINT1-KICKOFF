#!/bin/bash
# Carsharing Platform - MongoDB Initialization Script
# Date: October 2025
# Purpose: Initialize MongoDB database with collections and indexes

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

# MongoDB connection parameters
MONGO_HOST="${MONGO_HOST:-mongodb}"
MONGO_PORT="${MONGO_PORT:-27017}"
MONGO_DB="${MONGO_DB:-carsharing}"
MONGO_USER="${MONGO_USER:-carsharing_user}"
MONGO_PASSWORD="${MONGO_PASSWORD:-carsharing_pass}"
MONGO_ROOT_USER="${MONGO_INITDB_ROOT_USERNAME:-admin}"
MONGO_ROOT_PASSWORD="${MONGO_INITDB_ROOT_PASSWORD:-admin_password_change_me}"

# Path to initialization script
INIT_SCRIPT="/docker-entrypoint-initdb.d/init.js"

# Function to wait for MongoDB to be ready
wait_for_mongodb() {
    log_info "Waiting for MongoDB to be ready..."
    
    local max_attempts=30
    local attempt=0
    
    while [ $attempt -lt $max_attempts ]; do
        if mongosh --host "$MONGO_HOST" --port "$MONGO_PORT" \
            --username "$MONGO_ROOT_USER" --password "$MONGO_ROOT_PASSWORD" \
            --authenticationDatabase admin \
            --eval "db.adminCommand('ping')" >/dev/null 2>&1; then
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

# Function to check if database exists
database_exists() {
    local db_name=$1
    local result=$(mongosh --host "$MONGO_HOST" --port "$MONGO_PORT" \
        --username "$MONGO_ROOT_USER" --password "$MONGO_ROOT_PASSWORD" \
        --authenticationDatabase admin \
        --quiet --eval "db.adminCommand('listDatabases').databases.map(d => d.name).includes('$db_name')" 2>/dev/null)
    
    if [ "$result" = "true" ]; then
        return 0
    else
        return 1
    fi
}

# Function to check if collections exist
collections_exist() {
    local db_name=$1
    local result=$(mongosh --host "$MONGO_HOST" --port "$MONGO_PORT" \
        --username "$MONGO_ROOT_USER" --password "$MONGO_ROOT_PASSWORD" \
        --authenticationDatabase admin \
        --quiet --eval "use $db_name; db.getCollectionNames().length" 2>/dev/null)
    
    if [ "$result" -gt 0 ]; then
        return 0
    else
        return 1
    fi
}

# Function to check if user exists
user_exists() {
    local db_name=$1
    local username=$2
    local result=$(mongosh --host "$MONGO_HOST" --port "$MONGO_PORT" \
        --username "$MONGO_ROOT_USER" --password "$MONGO_ROOT_PASSWORD" \
        --authenticationDatabase admin \
        --quiet --eval "use $db_name; db.getUser('$username') !== null" 2>/dev/null)
    
    if [ "$result" = "true" ]; then
        return 0
    else
        return 1
    fi
}

# Function to create application user
create_app_user() {
    local db_name=$1
    local username=$2
    local password=$3
    
    log_info "Creating application user '$username' for database '$db_name'..."
    
    mongosh --host "$MONGO_HOST" --port "$MONGO_PORT" \
        --username "$MONGO_ROOT_USER" --password "$MONGO_ROOT_PASSWORD" \
        --authenticationDatabase admin \
        --eval "
        use $db_name;
        db.createUser({
            user: '$username',
            pwd: '$password',
            roles: [
                { role: 'readWrite', db: '$db_name' },
                { role: 'dbAdmin', db: '$db_name' }
            ]
        });
        " >/dev/null 2>&1
    
    if [ $? -eq 0 ]; then
        log_info "Application user created successfully"
        return 0
    else
        log_warn "Failed to create application user (may already exist)"
        return 1
    fi
}

# Function to run initialization script
run_init_script() {
    local script_file=$1
    
    if [ ! -f "$script_file" ]; then
        log_error "Initialization script not found: $script_file"
        return 1
    fi
    
    log_info "Running MongoDB initialization script..."
    
    if mongosh --host "$MONGO_HOST" --port "$MONGO_PORT" \
        --username "$MONGO_ROOT_USER" --password "$MONGO_ROOT_PASSWORD" \
        --authenticationDatabase admin \
        "$MONGO_DB" < "$script_file" >/dev/null 2>&1; then
        log_info "Initialization script completed successfully"
        return 0
    else
        log_error "Initialization script failed"
        return 1
    fi
}

# Function to create indexes
create_indexes() {
    local db_name=$1
    
    log_info "Creating indexes for optimal performance..."
    
    mongosh --host "$MONGO_HOST" --port "$MONGO_PORT" \
        --username "$MONGO_ROOT_USER" --password "$MONGO_ROOT_PASSWORD" \
        --authenticationDatabase admin \
        --eval "
        use $db_name;
        
        // Sensor data indexes
        if (db.getCollectionNames().includes('sensor_data')) {
            db.sensor_data.createIndex({ vehicle_id: 1, timestamp: -1 });
            db.sensor_data.createIndex({ timestamp: -1 });
            db.sensor_data.createIndex({ vehicle_id: 1 });
        }
        
        // Activity logs indexes
        if (db.getCollectionNames().includes('activity_logs')) {
            db.activity_logs.createIndex({ user_id: 1, timestamp: -1 });
            db.activity_logs.createIndex({ timestamp: -1 });
            db.activity_logs.createIndex({ action: 1 });
        }
        
        // System logs indexes
        if (db.getCollectionNames().includes('system_logs')) {
            db.system_logs.createIndex({ timestamp: -1 });
            db.system_logs.createIndex({ level: 1, timestamp: -1 });
        }
        
        // Notifications indexes
        if (db.getCollectionNames().includes('notifications')) {
            db.notifications.createIndex({ user_id: 1, created_at: -1 });
            db.notifications.createIndex({ read: 1, created_at: -1 });
        }
        " >/dev/null 2>&1
    
    if [ $? -eq 0 ]; then
        log_info "Indexes created successfully"
        return 0
    else
        log_warn "Failed to create some indexes"
        return 1
    fi
}

# Main initialization function
initialize_mongodb() {
    log_info "=========================================="
    log_info "MongoDB Database Initialization"
    log_info "=========================================="
    log_info "Database: $MONGO_DB"
    log_info "Host: $MONGO_HOST:$MONGO_PORT"
    log_info "=========================================="
    
    # Wait for MongoDB to be ready
    if ! wait_for_mongodb; then
        log_error "Cannot connect to MongoDB. Exiting..."
        exit 1
    fi
    
    # Check if database exists
    if database_exists "$MONGO_DB"; then
        log_info "Database '$MONGO_DB' already exists"
        
        # Check if collections exist
        if collections_exist "$MONGO_DB"; then
            log_info "Collections already exist in database '$MONGO_DB'"
            log_info "Database already initialized. Skipping initialization."
            log_info "If you want to reinitialize, drop the database first."
            return 0
        else
            log_info "No collections found. Running initialization script..."
            if [ -f "$INIT_SCRIPT" ]; then
                run_init_script "$INIT_SCRIPT"
            fi
            
            # Create indexes
            create_indexes "$MONGO_DB"
        fi
    else
        log_info "Database '$MONGO_DB' does not exist. Creating..."
        
        # Create application user
        if ! user_exists "$MONGO_DB" "$MONGO_USER"; then
            create_app_user "$MONGO_DB" "$MONGO_USER" "$MONGO_PASSWORD"
        else
            log_info "Application user already exists"
        fi
        
        # Run initialization script
        if [ -f "$INIT_SCRIPT" ]; then
            run_init_script "$INIT_SCRIPT"
        fi
        
        # Create indexes
        create_indexes "$MONGO_DB"
    fi
    
    log_info "=========================================="
    log_info "MongoDB initialization completed!"
    log_info "=========================================="
}

# Run initialization
initialize_mongodb
