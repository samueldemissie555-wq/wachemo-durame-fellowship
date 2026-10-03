USE if0_42696741_Durame;
ALTER TABLE users MODIFY role ENUM('super_admin','admin','editor') NOT NULL DEFAULT 'editor';
INSERT INTO users(username,password_hash,full_name,role,status) VALUES('fellowship','$2y$12$WqUUmolSrcR9eZjZD37JTucMINXWQ8Kw1ksmKRgeWSvFLpvI4CDC','Durame Campus Fellowship','super_admin','active') ON DUPLICATE KEY UPDATE password_hash=VALUES(password_hash),role='super_admin',status='active';
INSERT INTO website_settings(setting_key,setting_value) VALUES('service_form_url','https://forms.gle/aLD5exQMyzwhhWXT8'),('map_url','https://www.google.com/maps/search/?api=1&query=Wachemo+University+Durame+Campus') ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

CREATE TABLE IF NOT EXISTS service_registrations(
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 full_name VARCHAR(150) NOT NULL,
 department VARCHAR(150) NOT NULL,
 phone VARCHAR(30) NOT NULL UNIQUE,
 year_level ENUM('1st Year','2nd Year','3rd Year','4th Year','5th Year') NOT NULL,
 services JSON NOT NULL,
 mobilizations JSON NOT NULL,
 status ENUM('active','archived') NOT NULL DEFAULT 'active',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_service_status(status),
 INDEX idx_created_at(created_at)
) ENGINE=InnoDB;
