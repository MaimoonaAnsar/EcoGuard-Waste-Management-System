-- EcoGuard 2026 migration
-- Safe to run once on an existing EcoGuard database.

SET @db = DATABASE();

-- Add complaint coordinates only when they do not already exist.
SET @sql = IF(
    EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='complaint' AND COLUMN_NAME='Latitude'),
    'SELECT 1',
    'ALTER TABLE complaint ADD COLUMN Latitude DECIMAL(10,7) NULL'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
    EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='complaint' AND COLUMN_NAME='Longitude'),
    'SELECT 1',
    'ALTER TABLE complaint ADD COLUMN Longitude DECIMAL(10,7) NULL'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Local Authority garbage-truck collection schedules.
CREATE TABLE IF NOT EXISTS truck_schedule (
    Schedule_Id INT AUTO_INCREMENT PRIMARY KEY,
    District VARCHAR(100) NOT NULL,
    Area VARCHAR(150) NOT NULL,
    Day_Of_Week ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
    Pickup_Time TIME NOT NULL,
    Waste_Type VARCHAR(100) DEFAULT 'General',
    Notes VARCHAR(255) NULL,
    Created_By INT NULL,
    Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_truck_schedule_district (District),
    INDEX idx_truck_schedule_created_by (Created_By),
    CONSTRAINT fk_truck_schedule_user FOREIGN KEY (Created_By) REFERENCES users(U_Id) ON DELETE SET NULL
);

-- Citizen special pickup requests.
CREATE TABLE IF NOT EXISTS pickup_request (
    Request_Id INT AUTO_INCREMENT PRIMARY KEY,
    U_Id INT NOT NULL,
    District VARCHAR(100) NOT NULL,
    Address VARCHAR(255) NOT NULL,
    Latitude DECIMAL(10,7) NULL,
    Longitude DECIMAL(10,7) NULL,
    Waste_Type VARCHAR(100) DEFAULT 'General',
    Preferred_Date DATE NOT NULL,
    Notes VARCHAR(255) NULL,
    Status ENUM('Pending','Scheduled','Completed','Rejected') DEFAULT 'Pending',
    Handled_By INT NULL,
    Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pickup_user (U_Id),
    INDEX idx_pickup_status (Status),
    CONSTRAINT fk_pickup_user FOREIGN KEY (U_Id) REFERENCES users(U_Id) ON DELETE CASCADE,
    CONSTRAINT fk_pickup_handler FOREIGN KEY (Handled_By) REFERENCES users(U_Id) ON DELETE SET NULL
);
