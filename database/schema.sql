CREATE DATABASE IF NOT EXISTS if0_42696741_Durame CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE if0_42696741_Durame;
CREATE TABLE IF NOT EXISTS users(id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,username VARCHAR(80) NOT NULL UNIQUE,password_hash VARCHAR(255) NOT NULL,full_name VARCHAR(150) NULL,role ENUM('super_admin','admin','editor') NOT NULL DEFAULT 'editor',status ENUM('active','disabled') NOT NULL DEFAULT 'active',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB;
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
CREATE TABLE IF NOT EXISTS announcements(id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,title_en VARCHAR(255) NOT NULL,title_am VARCHAR(255) NULL,content_en TEXT NOT NULL,content_am TEXT NULL,status ENUM('draft','published') NOT NULL DEFAULT 'draft',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS events(id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,title_en VARCHAR(255) NOT NULL,title_am VARCHAR(255) NULL,description_en TEXT NULL,description_am TEXT NULL,event_date DATE NOT NULL,start_time TIME NULL,end_time TIME NULL,location VARCHAR(255) NULL,image VARCHAR(255) NULL,registration_enabled TINYINT(1) NOT NULL DEFAULT 0,status ENUM('draft','published') NOT NULL DEFAULT 'draft',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS sermons(id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,title_en VARCHAR(255) NOT NULL,title_am VARCHAR(255) NULL,description_en TEXT NULL,description_am TEXT NULL,speaker VARCHAR(150) NULL,bible_reference VARCHAR(255) NULL,video_url VARCHAR(500) NULL,audio_url VARCHAR(500) NULL,status ENUM('draft','published') NOT NULL DEFAULT 'draft',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS ministries(id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,title_en VARCHAR(255) NOT NULL,title_am VARCHAR(255) NULL,description_en TEXT NULL,description_am TEXT NULL,image VARCHAR(255) NULL,status ENUM('draft','published') NOT NULL DEFAULT 'draft',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS resources(id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,title_en VARCHAR(255) NOT NULL,title_am VARCHAR(255) NULL,description_en TEXT NULL,description_am TEXT NULL,file_path VARCHAR(500) NULL,external_url VARCHAR(500) NULL,status ENUM('draft','published') NOT NULL DEFAULT 'draft',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS gallery_albums(id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,title_en VARCHAR(255) NOT NULL,title_am VARCHAR(255) NULL,description_en TEXT NULL,description_am TEXT NULL,cover_image VARCHAR(255) NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS gallery_images(id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,album_id INT UNSIGNED NULL,title VARCHAR(255) NULL,image_path VARCHAR(500) NOT NULL,caption TEXT NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(album_id) REFERENCES gallery_albums(id) ON DELETE SET NULL) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS prayer_requests(id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,name VARCHAR(100) NULL,email VARCHAR(150) NULL,request TEXT NOT NULL,visibility ENUM('private','public') NOT NULL DEFAULT 'private',status ENUM('new','praying','answered','archived') NOT NULL DEFAULT 'new',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS social_links(id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,platform VARCHAR(50) NOT NULL,url VARCHAR(500) NOT NULL,is_active TINYINT(1) NOT NULL DEFAULT 1,sort_order INT NOT NULL DEFAULT 0) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS contact_messages(id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,name VARCHAR(120) NULL,email VARCHAR(150) NULL,subject VARCHAR(255) NULL,message TEXT NOT NULL,status ENUM('new','read','archived') NOT NULL DEFAULT 'new',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS website_settings(setting_key VARCHAR(100) PRIMARY KEY,setting_value TEXT NULL) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS activity_logs(id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,user_id INT UNSIGNED NULL,action VARCHAR(255) NOT NULL,ip_address VARCHAR(45) NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL) ENGINE=InnoDB;

INSERT INTO users(username,password_hash,full_name,role,status) VALUES('fellowship','$2y$12$WqUUmolSrcR9eZjZD37JTucMINXWQ8Kw1ksmKRgeWSvFLpvI4CDC','Durame Campus Fellowship','super_admin','active') ON DUPLICATE KEY UPDATE password_hash=VALUES(password_hash),role='super_admin',status='active';
INSERT IGNORE INTO ministries(title_en,title_am,description_en,description_am,image,status) VALUES
('Worship Ministry','የአምልኮ አገልግሎት','Leading students into worship and the presence of God.','ተማሪዎችን ወደ አምልኮና ወደ እግዚአብሔር መገኘት መምራት።','assets/images/ministries/worship.jpg','published'),
('Prayer Ministry','የጸሎት አገልግሎት','Standing in the gap through prayer and intercession for our campus.','ለካምፓሳችን በጸሎትና በምልጃ በክፍተቱ መቆም።','assets/images/ministries/prayer.jpg','published'),
('Evangelism Ministry','የወንጌል አገልግሎት','Reaching our campus and community with the Gospel of Jesus Christ.','የኢየሱስ ክርስቶስን ወንጌል ለካምፓስና ለማህበረሰቡ ማድረስ።','assets/images/ministries/evangelism.jpg','published'),
('Discipleship Ministry','የደቀመዝሙርነት አገልግሎት','Building believers through Bible study, mentoring and discipleship.','በመጽሐፍ ቅዱስ ጥናት፣ አማካሪነትና ደቀመዝሙርነት አማኞችን መገንባት።','assets/images/ministries/discipleship.jpg','published'),
('Media Ministry','የሚዲያ አገልግሎት','Using media and technology to share the message of Christ.','የክርስቶስን መልእክት ለማስተላለፍ ሚዲያንና ቴክኖሎጂን መጠቀም።','assets/images/ministries/media.jpg','published'),
('Service Ministry','የአገልግሎት ሚኒስትሪ','Serving students with love and compassion in practical ways.','ተማሪዎችን በፍቅርና በርኅራኄ በተግባር ማገልገል።','assets/images/ministries/service.jpg','published'),
('Student Fellowship','የተማሪዎች ፌሎሽፕ','Creating healthy friendships, discussion groups and campus community.','ጤናማ ወዳጅነት፣ የውይይት ቡድኖችና የካምፓስ ማህበረሰብ መፍጠር።','assets/images/ministries/student.jpg','published'),
('Outreach','የውጭ አገልግሎት','Serving and encouraging people beyond our regular gatherings.','ከመደበኛ ስብሰባዎቻችን ውጭ ሰዎችን ማገልገልና ማበረታታት።','assets/images/ministries/outreach.jpg','published');
INSERT IGNORE INTO social_links(platform,url,is_active,sort_order) VALUES('Telegram','https://t.me/wcudc',1,1),('TikTok','https://www.tiktok.com/@wcudcfellow',1,2),('Instagram','https://www.instagram.com/wcudc_ecsf',1,3),('Facebook','https://www.facebook.com/Duramecampusfellowshi',1,4);
INSERT INTO website_settings(setting_key,setting_value) VALUES('site_name','Durame Campus Fellowship'),('university','Wachemo University'),('campus','Durame Campus'),('church','Buicho Kale Hiwot Church'),('phone',''),('email',''),('telegram_url','https://t.me/wcudc'),('tiktok_url','https://www.tiktok.com/@wcudcfellow'),('instagram_url','https://www.instagram.com/wcudc_ecsf'),('facebook_url','https://www.facebook.com/Duramecampusfellowshi'),('map_url','https://www.google.com/maps/search/?api=1&query=Wachemo+University+Durame+Campus'),('service_form_url','https://forms.gle/aLD5exQMyzwhhWXT8') ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
-- WCUDC Form Builder (MySQL 5.7 compatible)
CREATE TABLE IF NOT EXISTS custom_forms (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 title VARCHAR(255) NOT NULL,
 title_am VARCHAR(255) NULL,
 description TEXT NULL,
 description_am TEXT NULL,
 slug VARCHAR(120) NOT NULL UNIQUE,
 fields TEXT NOT NULL,
 status ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
 created_by INT UNSIGNED NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_custom_forms_status(status),
 FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS custom_form_submissions (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 form_id INT UNSIGNED NOT NULL,
 values_json LONGTEXT NOT NULL,
 uploaded_files TEXT NULL,
 ip_address VARCHAR(45) NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_cfs_form(form_id),
 CONSTRAINT fk_cfs_form FOREIGN KEY(form_id) REFERENCES custom_forms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
