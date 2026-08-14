CREATE DATABASE IF NOT EXISTS sport_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sport_management;

DROP TABLE IF EXISTS attendance;
DROP TABLE IF EXISTS sms_logs;
DROP TABLE IF EXISTS announcements;
DROP TABLE IF EXISTS training_schedules;
DROP TABLE IF EXISTS form_templates;
DROP TABLE IF EXISTS athlete_documents;
DROP TABLE IF EXISTS requirement_types;
DROP TABLE IF EXISTS team_members;
DROP TABLE IF EXISTS athletes;
DROP TABLE IF EXISTS teams;
DROP TABLE IF EXISTS sports;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    phone_number VARCHAR(30) NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','sports_coordinator','coach','athlete') NOT NULL DEFAULT 'athlete',
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE sports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL UNIQUE,
    description TEXT NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sport_id INT NOT NULL,
    coach_id INT NULL,
    name VARCHAR(120) NOT NULL,
    description TEXT NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sport_id) REFERENCES sports(id) ON DELETE CASCADE,
    FOREIGN KEY (coach_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE athletes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    student_id VARCHAR(60) NOT NULL UNIQUE,
    first_name VARCHAR(80) NOT NULL,
    middle_name VARCHAR(80) NULL,
    last_name VARCHAR(80) NOT NULL,
    gender VARCHAR(20) NULL,
    birthdate DATE NULL,
    age INT DEFAULT 0,
    address TEXT NULL,
    course VARCHAR(120) NULL,
    year_level VARCHAR(40) NULL,
    section VARCHAR(40) NULL,
    contact_number VARCHAR(30) NULL,
    guardian_name VARCHAR(120) NULL,
    guardian_contact VARCHAR(30) NULL,
    emergency_contact VARCHAR(30) NULL,
    height VARCHAR(20) NULL,
    weight VARCHAR(20) NULL,
    blood_type VARCHAR(10) NULL,
    medical_condition TEXT NULL,
    sport_id INT NULL,
    team_id INT NULL,
    position VARCHAR(80) NULL,
    athlete_status ENUM('Active','Inactive','Graduated','Injured') NOT NULL DEFAULT 'Active',
    profile_photo VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (sport_id) REFERENCES sports(id) ON DELETE SET NULL,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE SET NULL
);

CREATE TABLE team_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT NOT NULL,
    athlete_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_member (team_id, athlete_id),
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (athlete_id) REFERENCES athletes(id) ON DELETE CASCADE
);

CREATE TABLE requirement_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(160) NOT NULL UNIQUE,
    description TEXT NULL,
    is_required TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE athlete_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    athlete_id INT NOT NULL,
    requirement_type_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    status ENUM('Pending','Submitted','Approved','Rejected') NOT NULL DEFAULT 'Submitted',
    remarks TEXT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_document (athlete_id, requirement_type_id),
    FOREIGN KEY (athlete_id) REFERENCES athletes(id) ON DELETE CASCADE,
    FOREIGN KEY (requirement_type_id) REFERENCES requirement_types(id) ON DELETE CASCADE
);

CREATE TABLE form_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(160) NOT NULL,
    description TEXT NULL,
    file_path VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    uploaded_by INT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE training_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sport_id INT NOT NULL,
    team_id INT NOT NULL,
    coach_id INT NOT NULL,
    training_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    venue VARCHAR(160) NOT NULL,
    description TEXT NULL,
    status ENUM('Scheduled','Updated','Cancelled','Completed') NOT NULL DEFAULT 'Scheduled',
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sport_id) REFERENCES sports(id) ON DELETE CASCADE,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (coach_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    schedule_id INT NOT NULL,
    athlete_id INT NOT NULL,
    status ENUM('Present','Absent','Late','Excused') NOT NULL DEFAULT 'Present',
    remarks TEXT NULL,
    marked_by INT NULL,
    marked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_attendance (schedule_id, athlete_id),
    FOREIGN KEY (schedule_id) REFERENCES training_schedules(id) ON DELETE CASCADE,
    FOREIGN KEY (athlete_id) REFERENCES athletes(id) ON DELETE CASCADE,
    FOREIGN KEY (marked_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    body TEXT NOT NULL,
    sport_id INT NULL,
    team_id INT NULL,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sport_id) REFERENCES sports(id) ON DELETE SET NULL,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE sms_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recipient_name VARCHAR(160) NOT NULL,
    phone_number VARCHAR(40) NOT NULL,
    message TEXT NOT NULL,
    status VARCHAR(80) NOT NULL,
    sent_by INT NULL,
    source VARCHAR(40) NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sent_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE medical_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    athlete_id INT NOT NULL,
    exam_date DATE NULL,
    height VARCHAR(20) NULL,
    weight VARCHAR(20) NULL,
    blood_type VARCHAR(10) NULL,
    blood_pressure VARCHAR(20) NULL,
    heart_rate VARCHAR(20) NULL,
    allergies TEXT NULL,
    medical_conditions TEXT NULL,
    medications TEXT NULL,
    injury_history TEXT NULL,
    recent_injury TEXT NULL,
    fitness_status VARCHAR(120) NULL,
    clearance_status ENUM('Fit to Play','Not Fit to Play') NULL,
    certificate_path VARCHAR(255) NULL,
    certificate_name VARCHAR(255) NULL,
    physician_name VARCHAR(160) NULL,
    physician_remarks TEXT NULL,
    next_checkup_date DATE NULL,
    recorded_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (athlete_id) REFERENCES athletes(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE competitions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(180) NOT NULL,
    sport_id INT NULL,
    category ENUM('Individual','Team') NOT NULL DEFAULT 'Individual',
    event_type VARCHAR(120) NULL,
    venue VARCHAR(160) NULL,
    organizer VARCHAR(160) NULL,
    level ENUM('School','Division','Regional','National') NOT NULL DEFAULT 'School',
    start_date DATE NULL,
    end_date DATE NULL,
    registration_deadline DATE NULL,
    status ENUM('Upcoming','Ongoing','Completed') NOT NULL DEFAULT 'Upcoming',
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sport_id) REFERENCES sports(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE competition_participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    competition_id INT NOT NULL,
    athlete_id INT NOT NULL,
    event_name VARCHAR(180) NULL,
    jersey_bib VARCHAR(40) NULL,
    coach_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_participant (competition_id, athlete_id),
    FOREIGN KEY (competition_id) REFERENCES competitions(id) ON DELETE CASCADE,
    FOREIGN KEY (athlete_id) REFERENCES athletes(id) ON DELETE CASCADE,
    FOREIGN KEY (coach_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE competition_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    competition_id INT NOT NULL,
    athlete_id INT NOT NULL,
    rank_place VARCHAR(40) NULL,
    medal ENUM('Gold','Silver','Bronze','None') NOT NULL DEFAULT 'None',
    score_time VARCHAR(80) NULL,
    result_status ENUM('Winner','Qualified','Eliminated') NOT NULL DEFAULT 'Winner',
    remarks TEXT NULL,
    updated_by INT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_result (competition_id, athlete_id),
    FOREIGN KEY (competition_id) REFERENCES competitions(id) ON DELETE CASCADE,
    FOREIGN KEY (athlete_id) REFERENCES athletes(id) ON DELETE CASCADE,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE athlete_histories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    athlete_id INT NOT NULL,
    competition_name VARCHAR(180) NOT NULL,
    competition_level ENUM('School','Municipal','Provincial','Regional','National','International','Other') NOT NULL DEFAULT 'School',
    sport_id INT NULL,
    event_name VARCHAR(120) NULL,
    competition_year SMALLINT NULL,
    organization VARCHAR(160) NULL,
    location VARCHAR(160) NULL,
    result VARCHAR(120) NULL,
    medal ENUM('None','Gold','Silver','Bronze','Other') NOT NULL DEFAULT 'None',
    description TEXT NULL,
    proof_file VARCHAR(255) NULL,
    proof_name VARCHAR(255) NULL,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (athlete_id) REFERENCES athletes(id) ON DELETE CASCADE,
    FOREIGN KEY (sport_id) REFERENCES sports(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

INSERT INTO users (name,email,phone_number,password,role,status) VALUES
('System Administrator','admin@sports.test',NULL,'$2y$12$af04aKvs15Zp2EwOE0O92uDX9LUiu799GNWxmFwsM1NC4M8jt/AZi','admin','active'),
('Coach Demo','coach@sports.test','09171234567','$2y$12$af04aKvs15Zp2EwOE0O92uDX9LUiu799GNWxmFwsM1NC4M8jt/AZi','coach','active'),
('Athlete Demo','athlete@sports.test',NULL,'$2y$12$af04aKvs15Zp2EwOE0O92uDX9LUiu799GNWxmFwsM1NC4M8jt/AZi','athlete','active');

INSERT INTO sports (name,description) VALUES
('Basketball','Men and women basketball teams'),
('Volleyball','Indoor volleyball program'),
('Badminton','Singles and doubles badminton'),
('Athletics','Track and field events'),
('Chess','Board sports and competitions');

INSERT INTO teams (sport_id, coach_id, name, description) VALUES
(1, 2, 'Blue Falcons Basketball', 'Varsity basketball team'),
(2, 2, 'Lady Spikers', 'Varsity volleyball team');

INSERT INTO athletes (user_id,student_id,first_name,middle_name,last_name,gender,birthdate,age,address,course,year_level,section,contact_number,guardian_name,guardian_contact,emergency_contact,height,weight,blood_type,medical_condition,sport_id,team_id,position,athlete_status)
VALUES (3,'2026-0001','Juan','Santos','Dela Cruz','Male','2004-03-15',22,'Sample Address','BSIT','3rd Year','A','09170000001','Maria Dela Cruz','09170000002','09170000003','175 cm','68 kg','O+','None',1,1,'Guard','Active');

INSERT INTO team_members (team_id, athlete_id) VALUES (1,1);

INSERT INTO requirement_types (title,description,is_required) VALUES
('School ID','Valid school identification card',1),
('Birth Certificate','PSA or local civil registry copy',1),
('Medical Certificate','Medical clearance for sports participation',1),
('Parent Consent','Signed parent or guardian consent',1),
('Waiver Form','Signed sports participation waiver',1),
('Grade Slip / COR','Current grade slip or certificate of registration',1),
('Good Moral Certificate','Certificate of good moral character',1),
('2x2 Picture','Recent 2x2 ID picture',1);

INSERT INTO announcements (title,body,sport_id,team_id,created_by)
VALUES ('Welcome Athletes','Please complete your biodata and requirement documents before the next training cycle.',NULL,NULL,1);
