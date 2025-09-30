import mysql.connector
from pymongo import MongoClient

# --- Configuración ---
MONGO_URI = "mongodb://simsadmin:Putamare123.@localhost:27017/"
MONGO_DB = "simsdb"

MYSQL_CONFIG = {
    "host": "localhost",
    "user": "simsuser",
    "password": "Putamare123",
    "database": "simsdb"
}
MYSQL_CONFIG_NO_DB = {
    "host": "localhost",
    "user": "simsuser",
    "password": "Putamare123"
}

# --- MariaDB: ONLY users, payments and subscriptions ---
def comprobar_conexion_maria():
    try:
        conn = mysql.connector.connect(**MYSQL_CONFIG_NO_DB)
        conn.close()
        print("✅ Successful connection to MariaDB.")
    except Exception as e:
        print(f"❌ Connection error to MariaDB: {e}")

def crear_db_maria():
    try:
        conn = mysql.connector.connect(**MYSQL_CONFIG_NO_DB)
        cursor = conn.cursor()
        cursor.execute("CREATE DATABASE IF NOT EXISTS simsdb;")
        print("✅ MariaDB database created/ready.")
        cursor.close()
        conn.close()
    except Exception as e:
        print(f"❌ Error creating MariaDB DB: {e}")

def eliminar_db_maria():
    try:
        conn = mysql.connector.connect(**MYSQL_CONFIG_NO_DB)
        cursor = conn.cursor()
        cursor.execute("DROP DATABASE IF EXISTS simsdb;")
        print("✅ MariaDB database dropped.")
        cursor.close()
        conn.close()
    except Exception as e:
        print(f"❌ Error dropping MariaDB DB: {e}")

def crear_estructura_maria():
    try:
        conn = mysql.connector.connect(**MYSQL_CONFIG)
        cursor = conn.cursor()
        # Drop tables if they exist, in order of FK dependencies
        cursor.execute("SET FOREIGN_KEY_CHECKS = 0;")
        cursor.execute("DROP TABLE IF EXISTS payments;")
        cursor.execute("DROP TABLE IF EXISTS vehicle_usage;")
        cursor.execute("DROP TABLE IF EXISTS subscriptions;")
        cursor.execute("DROP TABLE IF EXISTS vehicles;")
        cursor.execute("DROP TABLE IF EXISTS locations;")
        cursor.execute("DROP TABLE IF EXISTS users;")
        cursor.execute("DROP TABLE IF EXISTS nationalities;")
        cursor.execute("SET FOREIGN_KEY_CHECKS = 1;")

        # Table nationalities
        cursor.execute("""
        CREATE TABLE IF NOT EXISTS nationalities (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE
        );
        """)
        # Table users
        cursor.execute("""
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(100) NOT NULL UNIQUE,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            phone VARCHAR(20),
            birth_date DATE,
            is_admin BOOLEAN NOT NULL DEFAULT FALSE,
            iban VARCHAR(34),
            driver_license_photo VARCHAR(255),
            nationality_id INT,
            minute_balance INT DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (nationality_id) REFERENCES nationalities(id)
        );
        """)
        # Table subscriptions
        cursor.execute("""
        CREATE TABLE IF NOT EXISTS subscriptions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            type ENUM('basic', 'premium') NOT NULL DEFAULT 'basic',
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            free_minutes INT DEFAULT 25,
            unlock_fee_waived BOOLEAN DEFAULT TRUE,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        );
        """)
        # Table vehicles
        cursor.execute("""
        CREATE TABLE IF NOT EXISTS vehicles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            plate VARCHAR(20) NOT NULL UNIQUE,
            brand VARCHAR(50) NOT NULL,
            model VARCHAR(50) NOT NULL,
            year INT,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        );
        """)
        # Table locations
        cursor.execute("""
        CREATE TABLE IF NOT EXISTS locations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            latitude DECIMAL(10,8) NOT NULL,
            longitude DECIMAL(11,8) NOT NULL,
            address VARCHAR(255),
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        );
        """)
        # Table vehicle_usage
        cursor.execute("""
        CREATE TABLE IF NOT EXISTS vehicle_usage (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            vehicle_id INT NOT NULL,
            start_time DATETIME NOT NULL,
            end_time DATETIME,
            start_location_id INT,
            end_location_id INT,
            total_distance_km DECIMAL(8,2),
            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (vehicle_id) REFERENCES vehicles(id),
            FOREIGN KEY (start_location_id) REFERENCES locations(id),
            FOREIGN KEY (end_location_id) REFERENCES locations(id)
        );
        """)
        # Table payments
        cursor.execute("""
        CREATE TABLE IF NOT EXISTS payments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            vehicle_usage_id INT,
            amount DECIMAL(10,2) NOT NULL,
            payment_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            type ENUM('unlock', 'time', 'subscription') NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (vehicle_usage_id) REFERENCES vehicle_usage(id)
        );
        """)
        conn.commit()
        print("✅ Tables structure (users, nationalities, subscriptions, vehicles, locations, vehicle_usage, payments) created in MariaDB.")
        cursor.close()
        conn.close()
    except Exception as e:
        print(f"❌ Error creating MariaDB structure: {e}")

# --- MongoDB: ONLY cars, history, sensors, logs ---
def comprobar_conexion_mongo():
    try:
        client = MongoClient(MONGO_URI)
        client.admin.command("ping")
        print("✅ Successful connection to MongoDB.")
    except Exception as e:
        print(f"❌ Connection error to MongoDB: {e}")

def crear_db_mongo():
    try:
        client = MongoClient(MONGO_URI)
        db = client[MONGO_DB]
        db.create_collection("dummy", capped=False)
        db["dummy"].drop()
        print("✅ MongoDB database ready (created on data insertion).")
    except Exception as e:
        print(f"❌ Error creating MongoDB DB: {e}")

def eliminar_db_mongo():
    try:
        client = MongoClient(MONGO_URI)
        client.drop_database(MONGO_DB)
        print("✅ MongoDB database dropped.")
    except Exception as e:
        print(f"❌ Error dropping MongoDB DB: {e}")

def crear_estructura_mongo():
    try:
        client = MongoClient(MONGO_URI)
        db = client[MONGO_DB]
        # Collection cars
        if "cars" not in db.list_collection_names():
            db.create_collection("cars")
            # Removed unique=True from _id index
            db["cars"].create_index("_id")
            db["cars"].create_index("license_plate", unique=True)
        # Collection history (actions, trips, claims, etc)
        if "history" not in db.list_collection_names():
            db.create_collection("history")
            db["history"].create_index("user_id")
            db["history"].create_index("car_id")
            db["history"].create_index("date")
        # Collection sensors
        if "sensors" not in db.list_collection_names():
            db.create_collection("sensors")
            db["sensors"].create_index("car_id")
            db["sensors"].create_index("sensor_id")
            db["sensors"].create_index("timestamp")
        # Collection logs
        if "logs" not in db.list_collection_names():
            db.create_collection("logs")
            db["logs"].create_index("car_id")
            db["logs"].create_index("timestamp")
        print("✅ Collections (cars, history, sensors, logs) created in MongoDB.")
    except Exception as e:
        if "already exists" in str(e):
            print("ℹ️  Collections already exist in MongoDB.")
        else:
            print(f"❌ Error creating MongoDB collections: {e}")

# --- Combined functions ---
def crear_todas_bases():
    crear_db_maria()
    crear_estructura_maria()
    crear_db_mongo()
    crear_estructura_mongo()
    print("✅ All databases and structures created.")

def eliminar_todas_bases():
    eliminar_db_maria()
    eliminar_db_mongo()
    print("✅ All databases dropped.")

# --- Improved interactive menu ---
def menu():
    opciones = {
        "1": ("[MariaDB] Check connection", comprobar_conexion_maria),
        "2": ("[MariaDB] Create database", crear_db_maria),
        "3": ("[MariaDB] Drop database", eliminar_db_maria),
        "4": ("[MariaDB] Create structure (users, payments, subscriptions)", crear_estructura_maria),
        "5": ("[MongoDB] Check connection", comprobar_conexion_mongo),
        "6": ("[MongoDB] Create database", crear_db_mongo),
        "7": ("[MongoDB] Drop database", eliminar_db_mongo),
        "8": ("[MongoDB] Create structure (cars, history, sensors, logs)", crear_estructura_mongo),
        "9": ("[GENERAL] Create ALL databases and structures", crear_todas_bases),
        "10": ("[GENERAL] Drop ALL databases", eliminar_todas_bases),
        "0": ("Exit", None)
    }
    while True:
        print("\n" + "="*60)
        print("SIMS DATABASE MANAGER")
        print("="*60)
        print("MariaDB options:")
        for key in ["1", "2", "3", "4"]:
            print(f"  {key}. {opciones[key][0]}")
        print("-"*60)
        print("MongoDB options:")
        for key in ["5", "6", "7", "8"]:
            print(f"  {key}. {opciones[key][0]}")
        print("-"*60)
        print("General options:")
        for key in ["9", "10"]:
            print(f"  {key}. {opciones[key][0]}")
        print("-"*60)
        print("  0. Exit")
        print("="*60)
        opcion = input("Select an option: ").strip()
        if opcion == "0":
            print("Goodbye!")
            break
        elif opcion in opciones and opciones[opcion][1]:
            opciones[opcion][1]()
        else:
            print("Invalid option. Please try again.")

if __name__ == "__main__":
    menu()