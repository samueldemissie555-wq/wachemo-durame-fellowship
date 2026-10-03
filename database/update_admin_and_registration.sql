-- Run this in phpMyAdmin after the existing Durame database is selected.
-- Admin login: fellowship / fellowship0909
INSERT INTO users (username, password_hash, full_name, role, status)
VALUES ('fellowship', '$2y$12$EFhdDP1cbvY3SsbO9OIMIendgqaLmr2/0evOznvippTzG.t4JkFoG', 'Durame Campus Fellowship', 'super_admin', 'active')
ON DUPLICATE KEY UPDATE password_hash=VALUES(password_hash), full_name=VALUES(full_name), role='super_admin', status='active';

INSERT INTO website_settings (setting_key, setting_value) VALUES
('service_registration_url','https://forms.gle/aLD5exQMyzwhhWXT8'),
('telegram_url','https://t.me/wcudc'),
('tiktok_url','https://www.tiktok.com/@wcudcfellow'),
('instagram_url','https://www.instagram.com/wcudc_ecsf'),
('facebook_url','https://www.facebook.com/Duramecampusfellowshi')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
