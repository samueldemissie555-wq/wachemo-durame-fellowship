-- Admin account and content-management privileges
-- Database: if0_42696741_Durame
-- Admin: fellowship / fellowship0909
INSERT INTO users (username,password_hash,full_name,role,status)
VALUES ('fellowship','$2y$12$Q8l0emt6OzPHrjZ07zvcYef91MmAliRZjdUAth1IfXslbc/RLajhK','Durame Campus Fellowship','super_admin','active')
ON DUPLICATE KEY UPDATE password_hash=VALUES(password_hash), role='super_admin', status='active';
