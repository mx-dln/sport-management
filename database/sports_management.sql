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
DROP TABLE IF EXISTS coach_sports;
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


CREATE TABLE coach_sports (
    coach_id INT NOT NULL,
    sport_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (coach_id, sport_id),
    INDEX idx_coach_sports_sport (sport_id)
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
    title VARCHAR(160) NOT NULL,
    description TEXT NULL,
    sport_id INT NULL,
    is_required TINYINT(1) NOT NULL DEFAULT 1,
    registration_initial TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_requirement_scope (title, sport_id),
    INDEX idx_requirement_sport (sport_id)
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


INSERT INTO users (name,email,phone_number,password,role,status) VALUES
('Mr. Vicente S. Saddul','vicente.s.saddul@sports.test',NULL,'$2y$12$.G5kmNx3h0l5yYCWh2jeqOst8/V9QdIRZ76o9JFoRp4VC7bai3K..','coach','active'),
('Dr. Allan Leal','allan.leal@sports.test',NULL,'$2y$12$.G5kmNx3h0l5yYCWh2jeqOst8/V9QdIRZ76o9JFoRp4VC7bai3K..','coach','active'),
('Mr. Carlo Woodrow Reyes','carlo.woodrow.reyes@sports.test',NULL,'$2y$12$.G5kmNx3h0l5yYCWh2jeqOst8/V9QdIRZ76o9JFoRp4VC7bai3K..','coach','active'),
('Mr. Manuel E. Ruma Jr.','manuel.e.ruma@sports.test',NULL,'$2y$12$.G5kmNx3h0l5yYCWh2jeqOst8/V9QdIRZ76o9JFoRp4VC7bai3K..','coach','active'),
('Mr. Christopher A. Cristobal','christopher.a.cristobal@sports.test',NULL,'$2y$12$.G5kmNx3h0l5yYCWh2jeqOst8/V9QdIRZ76o9JFoRp4VC7bai3K..','coach','active'),
('Mr. Harold Agustin','harold.agustin@sports.test',NULL,'$2y$12$.G5kmNx3h0l5yYCWh2jeqOst8/V9QdIRZ76o9JFoRp4VC7bai3K..','coach','active'),
('Mr. Amante B. Mariano Jr.','amante.b.mariano@sports.test',NULL,'$2y$12$.G5kmNx3h0l5yYCWh2jeqOst8/V9QdIRZ76o9JFoRp4VC7bai3K..','coach','active'),
('Dr. Eddie I. Peru','eddie.i.peru@sports.test',NULL,'$2y$12$.G5kmNx3h0l5yYCWh2jeqOst8/V9QdIRZ76o9JFoRp4VC7bai3K..','coach','active'),
('Mr. PrinceFord May L. Reyno','princeford.may.l.reyno@sports.test',NULL,'$2y$12$.G5kmNx3h0l5yYCWh2jeqOst8/V9QdIRZ76o9JFoRp4VC7bai3K..','coach','active'),
('Dr. Louie Ray U. Quilang','louie.ray.u.quilang@sports.test',NULL,'$2y$12$.G5kmNx3h0l5yYCWh2jeqOst8/V9QdIRZ76o9JFoRp4VC7bai3K..','coach','active'),
('Mr. Jenimar C. Dizon','jenimar.c.dizon@sports.test',NULL,'$2y$12$.G5kmNx3h0l5yYCWh2jeqOst8/V9QdIRZ76o9JFoRp4VC7bai3K..','coach','active'),
('Mr. Alfred Mateo','alfred.mateo@sports.test',NULL,'$2y$12$.G5kmNx3h0l5yYCWh2jeqOst8/V9QdIRZ76o9JFoRp4VC7bai3K..','coach','active'),
('Mr. Romeo S. Canceran','romeo.s.canceran@sports.test',NULL,'$2y$12$.G5kmNx3h0l5yYCWh2jeqOst8/V9QdIRZ76o9JFoRp4VC7bai3K..','coach','active'),
('Mr. Edcel Saclelo','edcel.saclelo@sports.test',NULL,'$2y$12$.G5kmNx3h0l5yYCWh2jeqOst8/V9QdIRZ76o9JFoRp4VC7bai3K..','coach','active'),
('Mr. Rickmar Gammad','rickmar.gammad@sports.test',NULL,'$2y$12$.G5kmNx3h0l5yYCWh2jeqOst8/V9QdIRZ76o9JFoRp4VC7bai3K..','coach','active'),
('Mr. Jan Eduard S. Mateo','jan.eduard.s.mateo@sports.test',NULL,'$2y$12$.G5kmNx3h0l5yYCWh2jeqOst8/V9QdIRZ76o9JFoRp4VC7bai3K..','coach','active'),
('Mr. Kenneth Ancheta','kenneth.ancheta@sports.test',NULL,'$2y$12$.G5kmNx3h0l5yYCWh2jeqOst8/V9QdIRZ76o9JFoRp4VC7bai3K..','coach','active'),
('Dr. Harvey T. Alejandro','harvey.t.alejandro@sports.test',NULL,'$2y$12$.G5kmNx3h0l5yYCWh2jeqOst8/V9QdIRZ76o9JFoRp4VC7bai3K..','coach','active'),
('Dr. Aquim D. Verzon','aquim.d.verzon@sports.test',NULL,'$2y$12$.G5kmNx3h0l5yYCWh2jeqOst8/V9QdIRZ76o9JFoRp4VC7bai3K..','coach','active'),
('Mr. Vicente L. Quinto','vicente.l.quinto@sports.test',NULL,'$2y$12$.G5kmNx3h0l5yYCWh2jeqOst8/V9QdIRZ76o9JFoRp4VC7bai3K..','coach','active'),
('Mr. Ryan G. Maramag','ryan.g.maramag@sports.test',NULL,'$2y$12$.G5kmNx3h0l5yYCWh2jeqOst8/V9QdIRZ76o9JFoRp4VC7bai3K..','coach','active'),
('Mr. Romel Q. Agcaoili','romel.q.agcaoili@sports.test',NULL,'$2y$12$.G5kmNx3h0l5yYCWh2jeqOst8/V9QdIRZ76o9JFoRp4VC7bai3K..','coach','active'),
('Dr. Joyce C. Pascual','joyce.c.pascual@sports.test',NULL,'$2y$12$.G5kmNx3h0l5yYCWh2jeqOst8/V9QdIRZ76o9JFoRp4VC7bai3K..','coach','active'),
('Dr. Isaias Dela Pena','isaias.dela.pena@sports.test',NULL,'$2y$12$.G5kmNx3h0l5yYCWh2jeqOst8/V9QdIRZ76o9JFoRp4VC7bai3K..','coach','active'),
('Mr. Floyd Passilan','floyd.passilan@sports.test',NULL,'$2y$12$.G5kmNx3h0l5yYCWh2jeqOst8/V9QdIRZ76o9JFoRp4VC7bai3K..','coach','active'),
('Dr. Jheoana M. Mones','jheoana.m.mones@sports.test',NULL,'$2y$12$.G5kmNx3h0l5yYCWh2jeqOst8/V9QdIRZ76o9JFoRp4VC7bai3K..','coach','active'),
('Mr. Jayson Mark Colcol','jayson.mark.colcol@sports.test',NULL,'$2y$12$.G5kmNx3h0l5yYCWh2jeqOst8/V9QdIRZ76o9JFoRp4VC7bai3K..','coach','active'),
('Mr. Deczan Piza','deczan.piza@sports.test',NULL,'$2y$12$.G5kmNx3h0l5yYCWh2jeqOst8/V9QdIRZ76o9JFoRp4VC7bai3K..','coach','active'),
('Engr. Jerome P. Juan','jerome.p.juan@sports.test',NULL,'$2y$12$.G5kmNx3h0l5yYCWh2jeqOst8/V9QdIRZ76o9JFoRp4VC7bai3K..','coach','active'),
('Mr. Benjie A. Pascua','benjie.a.pascua@sports.test',NULL,'$2y$12$.G5kmNx3h0l5yYCWh2jeqOst8/V9QdIRZ76o9JFoRp4VC7bai3K..','coach','active'),
('Mr. Kian Francis Antalan','kian.francis.antalan@sports.test',NULL,'$2y$12$.G5kmNx3h0l5yYCWh2jeqOst8/V9QdIRZ76o9JFoRp4VC7bai3K..','coach','active');

INSERT INTO sports (name,description,status) VALUES
('Archery M/W','Intramurals 2026 event from ISU tournament managers list','active'),
('Arnis M/W','Intramurals 2026 event from ISU tournament managers list','active'),
('Athletics (Jumping & Throwing)','Intramurals 2026 event from ISU tournament managers list','active'),
('Athletics (Running)','Intramurals 2026 event from ISU tournament managers list','active'),
('Badminton M/W','Intramurals 2026 event from ISU tournament managers list','active'),
('Baseball','Intramurals 2026 event from ISU tournament managers list','active'),
('Basketball 3x3 M/W','Intramurals 2026 event from ISU tournament managers list','active'),
('Basketball 5x5 Men','Intramurals 2026 event from ISU tournament managers list','active'),
('Basketball 5x5 Women','Intramurals 2026 event from ISU tournament managers list','active'),
('Beach Volleyball M/W','Intramurals 2026 event from ISU tournament managers list','active'),
('Cheer dance','Intramurals 2026 event from ISU tournament managers list','active'),
('Chess M/W','Intramurals 2026 event from ISU tournament managers list','active'),
('Dance S Third Kind','Intramurals 2026 event from ISU tournament managers list','active'),
('Dance Sports L/S','Intramurals 2026 event from ISU tournament managers list','active'),
('E-Sports CODM','Intramurals 2026 event from ISU tournament managers list','active'),
('E-Sports MLBB','Intramurals 2026 event from ISU tournament managers list','active'),
('Football','Intramurals 2026 event from ISU tournament managers list','active'),
('Futsal M/W','Intramurals 2026 event from ISU tournament managers list','active'),
('Karatedo M/W','Intramurals 2026 event from ISU tournament managers list','active'),
('Lawn Tennis M/W','Intramurals 2026 event from ISU tournament managers list','active'),
('Pencaksilat','Intramurals 2026 event from ISU tournament managers list','active'),
('Pickle Ball','Intramurals 2026 event from ISU tournament managers list','active'),
('Rowing','Intramurals 2026 event from ISU tournament managers list','active'),
('Sepaktakraw','Intramurals 2026 event from ISU tournament managers list','active'),
('Softball','Intramurals 2026 event from ISU tournament managers list','active'),
('Swimming','Intramurals 2026 event from ISU tournament managers list','active'),
('Table Tennis','Intramurals 2026 event from ISU tournament managers list','active'),
('Taekwondo','Intramurals 2026 event from ISU tournament managers list','active'),
('Volleyball Men','Intramurals 2026 event from ISU tournament managers list','active'),
('Volleyball W','Intramurals 2026 event from ISU tournament managers list','active');

INSERT INTO coach_sports (coach_id, sport_id)
SELECT u.id, s.id FROM users u JOIN sports s ON s.name='Archery M/W' WHERE u.email='vicente.s.saddul@sports.test'
UNION ALL
SELECT u.id, s.id FROM users u JOIN sports s ON s.name='Arnis M/W' WHERE u.email='allan.leal@sports.test'
UNION ALL
SELECT u.id, s.id FROM users u JOIN sports s ON s.name='Athletics (Running)' WHERE u.email='carlo.woodrow.reyes@sports.test'
UNION ALL
SELECT u.id, s.id FROM users u JOIN sports s ON s.name='Athletics (Jumping & Throwing)' WHERE u.email='manuel.e.ruma@sports.test'
UNION ALL
SELECT u.id, s.id FROM users u JOIN sports s ON s.name='Badminton M/W' WHERE u.email='christopher.a.cristobal@sports.test'
UNION ALL
SELECT u.id, s.id FROM users u JOIN sports s ON s.name='Baseball' WHERE u.email='harold.agustin@sports.test'
UNION ALL
SELECT u.id, s.id FROM users u JOIN sports s ON s.name='Basketball 5x5 Men' WHERE u.email='amante.b.mariano@sports.test'
UNION ALL
SELECT u.id, s.id FROM users u JOIN sports s ON s.name='Basketball 5x5 Women' WHERE u.email='eddie.i.peru@sports.test'
UNION ALL
SELECT u.id, s.id FROM users u JOIN sports s ON s.name='Basketball 3x3 M/W' WHERE u.email='princeford.may.l.reyno@sports.test'
UNION ALL
SELECT u.id, s.id FROM users u JOIN sports s ON s.name='Beach Volleyball M/W' WHERE u.email='louie.ray.u.quilang@sports.test'
UNION ALL
SELECT u.id, s.id FROM users u JOIN sports s ON s.name='Cheer dance' WHERE u.email='jenimar.c.dizon@sports.test'
UNION ALL
SELECT u.id, s.id FROM users u JOIN sports s ON s.name='Chess M/W' WHERE u.email='alfred.mateo@sports.test'
UNION ALL
SELECT u.id, s.id FROM users u JOIN sports s ON s.name='Dance Sports L/S' WHERE u.email='romeo.s.canceran@sports.test'
UNION ALL
SELECT u.id, s.id FROM users u JOIN sports s ON s.name='Dance S Third Kind' WHERE u.email='edcel.saclelo@sports.test'
UNION ALL
SELECT u.id, s.id FROM users u JOIN sports s ON s.name='E-Sports MLBB' WHERE u.email='rickmar.gammad@sports.test'
UNION ALL
SELECT u.id, s.id FROM users u JOIN sports s ON s.name='E-Sports CODM' WHERE u.email='jan.eduard.s.mateo@sports.test'
UNION ALL
SELECT u.id, s.id FROM users u JOIN sports s ON s.name='Football' WHERE u.email='kenneth.ancheta@sports.test'
UNION ALL
SELECT u.id, s.id FROM users u JOIN sports s ON s.name='Futsal M/W' WHERE u.email='harvey.t.alejandro@sports.test'
UNION ALL
SELECT u.id, s.id FROM users u JOIN sports s ON s.name='Karatedo M/W' WHERE u.email='aquim.d.verzon@sports.test'
UNION ALL
SELECT u.id, s.id FROM users u JOIN sports s ON s.name='Lawn Tennis M/W' WHERE u.email='vicente.l.quinto@sports.test'
UNION ALL
SELECT u.id, s.id FROM users u JOIN sports s ON s.name='Pencaksilat' WHERE u.email='ryan.g.maramag@sports.test'
UNION ALL
SELECT u.id, s.id FROM users u JOIN sports s ON s.name='Pickle Ball' WHERE u.email='romel.q.agcaoili@sports.test'
UNION ALL
SELECT u.id, s.id FROM users u JOIN sports s ON s.name='Rowing' WHERE u.email='joyce.c.pascual@sports.test'
UNION ALL
SELECT u.id, s.id FROM users u JOIN sports s ON s.name='Sepaktakraw' WHERE u.email='isaias.dela.pena@sports.test'
UNION ALL
SELECT u.id, s.id FROM users u JOIN sports s ON s.name='Softball' WHERE u.email='floyd.passilan@sports.test'
UNION ALL
SELECT u.id, s.id FROM users u JOIN sports s ON s.name='Swimming' WHERE u.email='jheoana.m.mones@sports.test'
UNION ALL
SELECT u.id, s.id FROM users u JOIN sports s ON s.name='Table Tennis' WHERE u.email='jayson.mark.colcol@sports.test'
UNION ALL
SELECT u.id, s.id FROM users u JOIN sports s ON s.name='Taekwondo' WHERE u.email='deczan.piza@sports.test'
UNION ALL
SELECT u.id, s.id FROM users u JOIN sports s ON s.name='Volleyball W' WHERE u.email='jerome.p.juan@sports.test'
UNION ALL
SELECT u.id, s.id FROM users u JOIN sports s ON s.name='Volleyball Men' WHERE u.email='benjie.a.pascua@sports.test'
UNION ALL
SELECT u.id, s.id FROM users u JOIN sports s ON s.name='Volleyball Men' WHERE u.email='kian.francis.antalan@sports.test';

INSERT INTO teams (sport_id, coach_id, name, description) VALUES
(1, 2, 'Blue Falcons Basketball', 'Varsity basketball team'),
(2, 2, 'Lady Spikers', 'Varsity volleyball team');

INSERT INTO athletes (user_id,student_id,first_name,middle_name,last_name,gender,birthdate,age,address,course,year_level,section,contact_number,guardian_name,guardian_contact,emergency_contact,height,weight,blood_type,medical_condition,sport_id,team_id,position,athlete_status)
VALUES (3,'2026-0001','Juan','Santos','Dela Cruz','Male','2004-03-15',22,'Sample Address','BSIT','3rd Year','A','09170000001','Maria Dela Cruz','09170000002','09170000003','175 cm','68 kg','O+','None',1,1,'Guard','Active');

INSERT INTO team_members (team_id, athlete_id) VALUES (1,1);

INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial) VALUES
('2x2 Picture','Recent 2x2 ID picture',NULL,1,1),
('Certificate of Grades (COG) / Grade Slip','Current Certificate of Grades (COG) or certificate of registration',NULL,1,1),
('Good Moral Certificate','Certificate of good moral character',NULL,1,0),
('Medical Certificate','Medical clearance for sports participation',NULL,1,0),
('Parent Consent','Signed parent or guardian consent',NULL,1,0),
('PSA Birth Certificate','Official PSA or local civil registry birth certificate copy',NULL,1,1),
('School ID','Valid school identification card',NULL,1,0),
('Waiver Form','Signed sports participation waiver',NULL,1,0);
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Athlete Waiver and Assumption of Risk', 'Signed waiver acknowledging sport participation risks.', id, 1, 0 FROM sports WHERE name='Archery M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Equipment Safety Checklist', 'Bow, arrow, and safety gear inspection checklist.', id, 1, 0 FROM sports WHERE name='Archery M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Insurance / Emergency Information Form', 'Emergency contact, allergies, medication, and insurance information.', id, 1, 0 FROM sports WHERE name='Archery M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Medical Certificate / Fit-to-Play Clearance', 'Physician clearance specific to training and competition participation.', id, 1, 0 FROM sports WHERE name='Archery M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Parent / Guardian Consent', 'Signed consent for participation in official practices and games.', id, 1, 0 FROM sports WHERE name='Archery M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Valid School ID', 'Current valid ISU school identification card.', id, 1, 0 FROM sports WHERE name='Archery M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Athlete Waiver and Assumption of Risk', 'Signed waiver acknowledging sport participation risks.', id, 1, 0 FROM sports WHERE name='Arnis M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Insurance / Emergency Information Form', 'Emergency contact, allergies, medication, and insurance information.', id, 1, 0 FROM sports WHERE name='Arnis M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Medical Certificate / Fit-to-Play Clearance', 'Physician clearance specific to training and competition participation.', id, 1, 0 FROM sports WHERE name='Arnis M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Parent / Guardian Consent', 'Signed consent for participation in official practices and games.', id, 1, 0 FROM sports WHERE name='Arnis M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Protective Gear Checklist', 'Required protective gear and weapon inspection checklist.', id, 1, 0 FROM sports WHERE name='Arnis M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Valid School ID', 'Current valid ISU school identification card.', id, 1, 0 FROM sports WHERE name='Arnis M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Athlete Waiver and Assumption of Risk', 'Signed waiver acknowledging sport participation risks.', id, 1, 0 FROM sports WHERE name='Athletics';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Event Entry Confirmation', 'Specific running, jumping, or throwing event assignment.', id, 1, 0 FROM sports WHERE name='Athletics';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Event Performance Record', 'Latest qualifying time, distance, height, or event result.', id, 1, 0 FROM sports WHERE name='Athletics';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Insurance / Emergency Information Form', 'Emergency contact, allergies, medication, and insurance information.', id, 1, 0 FROM sports WHERE name='Athletics';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Medical Certificate / Fit-to-Play Clearance', 'Physician clearance specific to training and competition participation.', id, 1, 0 FROM sports WHERE name='Athletics';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Parent / Guardian Consent', 'Signed consent for participation in official practices and games.', id, 1, 0 FROM sports WHERE name='Athletics';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Valid School ID', 'Current valid ISU school identification card.', id, 1, 0 FROM sports WHERE name='Athletics';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Athlete Waiver and Assumption of Risk', 'Signed waiver acknowledging sport participation risks.', id, 1, 0 FROM sports WHERE name='Athletics (Jumping & Throwing)';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Event Entry Confirmation', 'Specific running, jumping, or throwing event assignment.', id, 1, 0 FROM sports WHERE name='Athletics (Jumping & Throwing)';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Event Performance Record', 'Latest qualifying time, distance, height, or event result.', id, 1, 0 FROM sports WHERE name='Athletics (Jumping & Throwing)';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Insurance / Emergency Information Form', 'Emergency contact, allergies, medication, and insurance information.', id, 1, 0 FROM sports WHERE name='Athletics (Jumping & Throwing)';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Medical Certificate / Fit-to-Play Clearance', 'Physician clearance specific to training and competition participation.', id, 1, 0 FROM sports WHERE name='Athletics (Jumping & Throwing)';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Parent / Guardian Consent', 'Signed consent for participation in official practices and games.', id, 1, 0 FROM sports WHERE name='Athletics (Jumping & Throwing)';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Valid School ID', 'Current valid ISU school identification card.', id, 1, 0 FROM sports WHERE name='Athletics (Jumping & Throwing)';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Water Safety Clearance', 'Clearance for water-based sport participation.', id, 1, 0 FROM sports WHERE name='Athletics (Jumping & Throwing)';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Athlete Waiver and Assumption of Risk', 'Signed waiver acknowledging sport participation risks.', id, 1, 0 FROM sports WHERE name='Athletics (Running)';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Event Entry Confirmation', 'Specific running, jumping, or throwing event assignment.', id, 1, 0 FROM sports WHERE name='Athletics (Running)';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Event Performance Record', 'Latest qualifying time, distance, height, or event result.', id, 1, 0 FROM sports WHERE name='Athletics (Running)';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Insurance / Emergency Information Form', 'Emergency contact, allergies, medication, and insurance information.', id, 1, 0 FROM sports WHERE name='Athletics (Running)';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Medical Certificate / Fit-to-Play Clearance', 'Physician clearance specific to training and competition participation.', id, 1, 0 FROM sports WHERE name='Athletics (Running)';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Parent / Guardian Consent', 'Signed consent for participation in official practices and games.', id, 1, 0 FROM sports WHERE name='Athletics (Running)';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Valid School ID', 'Current valid ISU school identification card.', id, 1, 0 FROM sports WHERE name='Athletics (Running)';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Athlete Waiver and Assumption of Risk', 'Signed waiver acknowledging sport participation risks.', id, 1, 0 FROM sports WHERE name='Badminton';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Insurance / Emergency Information Form', 'Emergency contact, allergies, medication, and insurance information.', id, 1, 0 FROM sports WHERE name='Badminton';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Medical Certificate / Fit-to-Play Clearance', 'Physician clearance specific to training and competition participation.', id, 1, 0 FROM sports WHERE name='Badminton';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Parent / Guardian Consent', 'Signed consent for participation in official practices and games.', id, 1, 0 FROM sports WHERE name='Badminton';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Singles / Doubles Entry Confirmation', 'Event entry confirmation for singles, doubles, or mixed doubles.', id, 1, 0 FROM sports WHERE name='Badminton';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Valid School ID', 'Current valid ISU school identification card.', id, 1, 0 FROM sports WHERE name='Badminton';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Athlete Waiver and Assumption of Risk', 'Signed waiver acknowledging sport participation risks.', id, 1, 0 FROM sports WHERE name='Badminton M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Insurance / Emergency Information Form', 'Emergency contact, allergies, medication, and insurance information.', id, 1, 0 FROM sports WHERE name='Badminton M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Medical Certificate / Fit-to-Play Clearance', 'Physician clearance specific to training and competition participation.', id, 1, 0 FROM sports WHERE name='Badminton M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Parent / Guardian Consent', 'Signed consent for participation in official practices and games.', id, 1, 0 FROM sports WHERE name='Badminton M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Singles / Doubles Entry Confirmation', 'Event entry confirmation for singles, doubles, or mixed doubles.', id, 1, 0 FROM sports WHERE name='Badminton M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Valid School ID', 'Current valid ISU school identification card.', id, 1, 0 FROM sports WHERE name='Badminton M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Athlete Waiver and Assumption of Risk', 'Signed waiver acknowledging sport participation risks.', id, 1, 0 FROM sports WHERE name='Baseball';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Insurance / Emergency Information Form', 'Emergency contact, allergies, medication, and insurance information.', id, 1, 0 FROM sports WHERE name='Baseball';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Medical Certificate / Fit-to-Play Clearance', 'Physician clearance specific to training and competition participation.', id, 1, 0 FROM sports WHERE name='Baseball';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Parent / Guardian Consent', 'Signed consent for participation in official practices and games.', id, 1, 0 FROM sports WHERE name='Baseball';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Team Roster Confirmation', 'Official team roster and playing position confirmation.', id, 1, 0 FROM sports WHERE name='Baseball';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Valid School ID', 'Current valid ISU school identification card.', id, 1, 0 FROM sports WHERE name='Baseball';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Athlete Waiver and Assumption of Risk', 'Signed waiver acknowledging sport participation risks.', id, 1, 0 FROM sports WHERE name='Basketball';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Insurance / Emergency Information Form', 'Emergency contact, allergies, medication, and insurance information.', id, 1, 0 FROM sports WHERE name='Basketball';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Jersey Number Declaration', 'Assigned jersey number and uniform confirmation.', id, 1, 0 FROM sports WHERE name='Basketball';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Medical Certificate / Fit-to-Play Clearance', 'Physician clearance specific to training and competition participation.', id, 1, 0 FROM sports WHERE name='Basketball';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Parent / Guardian Consent', 'Signed consent for participation in official practices and games.', id, 1, 0 FROM sports WHERE name='Basketball';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Team Roster Confirmation', 'Official team roster listing player number and category.', id, 1, 0 FROM sports WHERE name='Basketball';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Valid School ID', 'Current valid ISU school identification card.', id, 1, 0 FROM sports WHERE name='Basketball';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Athlete Waiver and Assumption of Risk', 'Signed waiver acknowledging sport participation risks.', id, 1, 0 FROM sports WHERE name='Basketball 3x3 M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Insurance / Emergency Information Form', 'Emergency contact, allergies, medication, and insurance information.', id, 1, 0 FROM sports WHERE name='Basketball 3x3 M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Jersey Number Declaration', 'Assigned jersey number and uniform confirmation.', id, 1, 0 FROM sports WHERE name='Basketball 3x3 M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Medical Certificate / Fit-to-Play Clearance', 'Physician clearance specific to training and competition participation.', id, 1, 0 FROM sports WHERE name='Basketball 3x3 M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Parent / Guardian Consent', 'Signed consent for participation in official practices and games.', id, 1, 0 FROM sports WHERE name='Basketball 3x3 M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Team Roster Confirmation', 'Official team roster listing player number and category.', id, 1, 0 FROM sports WHERE name='Basketball 3x3 M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Valid School ID', 'Current valid ISU school identification card.', id, 1, 0 FROM sports WHERE name='Basketball 3x3 M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Athlete Waiver and Assumption of Risk', 'Signed waiver acknowledging sport participation risks.', id, 1, 0 FROM sports WHERE name='Basketball 5x5 Men';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Insurance / Emergency Information Form', 'Emergency contact, allergies, medication, and insurance information.', id, 1, 0 FROM sports WHERE name='Basketball 5x5 Men';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Jersey Number Declaration', 'Assigned jersey number and uniform confirmation.', id, 1, 0 FROM sports WHERE name='Basketball 5x5 Men';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Medical Certificate / Fit-to-Play Clearance', 'Physician clearance specific to training and competition participation.', id, 1, 0 FROM sports WHERE name='Basketball 5x5 Men';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Parent / Guardian Consent', 'Signed consent for participation in official practices and games.', id, 1, 0 FROM sports WHERE name='Basketball 5x5 Men';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Team Roster Confirmation', 'Official team roster listing player number and category.', id, 1, 0 FROM sports WHERE name='Basketball 5x5 Men';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Valid School ID', 'Current valid ISU school identification card.', id, 1, 0 FROM sports WHERE name='Basketball 5x5 Men';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Athlete Waiver and Assumption of Risk', 'Signed waiver acknowledging sport participation risks.', id, 1, 0 FROM sports WHERE name='Basketball 5x5 Women';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Insurance / Emergency Information Form', 'Emergency contact, allergies, medication, and insurance information.', id, 1, 0 FROM sports WHERE name='Basketball 5x5 Women';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Jersey Number Declaration', 'Assigned jersey number and uniform confirmation.', id, 1, 0 FROM sports WHERE name='Basketball 5x5 Women';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Medical Certificate / Fit-to-Play Clearance', 'Physician clearance specific to training and competition participation.', id, 1, 0 FROM sports WHERE name='Basketball 5x5 Women';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Parent / Guardian Consent', 'Signed consent for participation in official practices and games.', id, 1, 0 FROM sports WHERE name='Basketball 5x5 Women';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Team Roster Confirmation', 'Official team roster listing player number and category.', id, 1, 0 FROM sports WHERE name='Basketball 5x5 Women';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Valid School ID', 'Current valid ISU school identification card.', id, 1, 0 FROM sports WHERE name='Basketball 5x5 Women';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Athlete Waiver and Assumption of Risk', 'Signed waiver acknowledging sport participation risks.', id, 1, 0 FROM sports WHERE name='Beach Volleyball M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Insurance / Emergency Information Form', 'Emergency contact, allergies, medication, and insurance information.', id, 1, 0 FROM sports WHERE name='Beach Volleyball M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Jersey Number Declaration', 'Assigned jersey number and uniform confirmation.', id, 1, 0 FROM sports WHERE name='Beach Volleyball M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Medical Certificate / Fit-to-Play Clearance', 'Physician clearance specific to training and competition participation.', id, 1, 0 FROM sports WHERE name='Beach Volleyball M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Parent / Guardian Consent', 'Signed consent for participation in official practices and games.', id, 1, 0 FROM sports WHERE name='Beach Volleyball M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Team Roster Confirmation', 'Official team roster listing player number and category.', id, 1, 0 FROM sports WHERE name='Beach Volleyball M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Valid School ID', 'Current valid ISU school identification card.', id, 1, 0 FROM sports WHERE name='Beach Volleyball M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Athlete Waiver and Assumption of Risk', 'Signed waiver acknowledging sport participation risks.', id, 1, 0 FROM sports WHERE name='Cheer dance';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Insurance / Emergency Information Form', 'Emergency contact, allergies, medication, and insurance information.', id, 1, 0 FROM sports WHERE name='Cheer dance';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Medical Certificate / Fit-to-Play Clearance', 'Physician clearance specific to training and competition participation.', id, 1, 0 FROM sports WHERE name='Cheer dance';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Parent / Guardian Consent', 'Signed consent for participation in official practices and games.', id, 1, 0 FROM sports WHERE name='Cheer dance';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Routine / Music Clearance', 'Routine title, music clearance, and performance category confirmation.', id, 1, 0 FROM sports WHERE name='Cheer dance';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Stunt Safety Clearance', 'Coach clearance for stunt participation.', id, 1, 0 FROM sports WHERE name='Cheer dance';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Valid School ID', 'Current valid ISU school identification card.', id, 1, 0 FROM sports WHERE name='Cheer dance';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Athlete Waiver and Assumption of Risk', 'Signed waiver acknowledging sport participation risks.', id, 1, 0 FROM sports WHERE name='Chess M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Board Assignment / Rating Record', 'Board order, rating, or tournament record if available.', id, 1, 0 FROM sports WHERE name='Chess M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Insurance / Emergency Information Form', 'Emergency contact, allergies, medication, and insurance information.', id, 1, 0 FROM sports WHERE name='Chess M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Medical Certificate / Fit-to-Play Clearance', 'Physician clearance specific to training and competition participation.', id, 1, 0 FROM sports WHERE name='Chess M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Parent / Guardian Consent', 'Signed consent for participation in official practices and games.', id, 1, 0 FROM sports WHERE name='Chess M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Valid School ID', 'Current valid ISU school identification card.', id, 1, 0 FROM sports WHERE name='Chess M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Athlete Waiver and Assumption of Risk', 'Signed waiver acknowledging sport participation risks.', id, 1, 0 FROM sports WHERE name='Dance S Third Kind';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Insurance / Emergency Information Form', 'Emergency contact, allergies, medication, and insurance information.', id, 1, 0 FROM sports WHERE name='Dance S Third Kind';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Medical Certificate / Fit-to-Play Clearance', 'Physician clearance specific to training and competition participation.', id, 1, 0 FROM sports WHERE name='Dance S Third Kind';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Parent / Guardian Consent', 'Signed consent for participation in official practices and games.', id, 1, 0 FROM sports WHERE name='Dance S Third Kind';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Routine / Music Clearance', 'Routine title, music clearance, and performance category confirmation.', id, 1, 0 FROM sports WHERE name='Dance S Third Kind';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Valid School ID', 'Current valid ISU school identification card.', id, 1, 0 FROM sports WHERE name='Dance S Third Kind';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Athlete Waiver and Assumption of Risk', 'Signed waiver acknowledging sport participation risks.', id, 1, 0 FROM sports WHERE name='Dance Sports L/S';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Insurance / Emergency Information Form', 'Emergency contact, allergies, medication, and insurance information.', id, 1, 0 FROM sports WHERE name='Dance Sports L/S';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Medical Certificate / Fit-to-Play Clearance', 'Physician clearance specific to training and competition participation.', id, 1, 0 FROM sports WHERE name='Dance Sports L/S';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Parent / Guardian Consent', 'Signed consent for participation in official practices and games.', id, 1, 0 FROM sports WHERE name='Dance Sports L/S';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Routine / Music Clearance', 'Routine title, music clearance, and performance category confirmation.', id, 1, 0 FROM sports WHERE name='Dance Sports L/S';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Valid School ID', 'Current valid ISU school identification card.', id, 1, 0 FROM sports WHERE name='Dance Sports L/S';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Athlete Waiver and Assumption of Risk', 'Signed waiver acknowledging sport participation risks.', id, 1, 0 FROM sports WHERE name='E-Sports CODM';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Device / Connectivity Waiver', 'Acknowledgement of device, account, and connectivity responsibilities.', id, 1, 0 FROM sports WHERE name='E-Sports CODM';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Game Account Verification', 'Official game account ID, IGN, server, and rank screenshot.', id, 1, 0 FROM sports WHERE name='E-Sports CODM';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Insurance / Emergency Information Form', 'Emergency contact, allergies, medication, and insurance information.', id, 1, 0 FROM sports WHERE name='E-Sports CODM';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Medical Certificate / Fit-to-Play Clearance', 'Physician clearance specific to training and competition participation.', id, 1, 0 FROM sports WHERE name='E-Sports CODM';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Parent / Guardian Consent', 'Signed consent for participation in official practices and games.', id, 1, 0 FROM sports WHERE name='E-Sports CODM';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Valid School ID', 'Current valid ISU school identification card.', id, 1, 0 FROM sports WHERE name='E-Sports CODM';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Athlete Waiver and Assumption of Risk', 'Signed waiver acknowledging sport participation risks.', id, 1, 0 FROM sports WHERE name='E-Sports MLBB';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Device / Connectivity Waiver', 'Acknowledgement of device, account, and connectivity responsibilities.', id, 1, 0 FROM sports WHERE name='E-Sports MLBB';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Game Account Verification', 'Official game account ID, IGN, server, and rank screenshot.', id, 1, 0 FROM sports WHERE name='E-Sports MLBB';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Insurance / Emergency Information Form', 'Emergency contact, allergies, medication, and insurance information.', id, 1, 0 FROM sports WHERE name='E-Sports MLBB';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Medical Certificate / Fit-to-Play Clearance', 'Physician clearance specific to training and competition participation.', id, 1, 0 FROM sports WHERE name='E-Sports MLBB';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Parent / Guardian Consent', 'Signed consent for participation in official practices and games.', id, 1, 0 FROM sports WHERE name='E-Sports MLBB';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Valid School ID', 'Current valid ISU school identification card.', id, 1, 0 FROM sports WHERE name='E-Sports MLBB';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Athlete Waiver and Assumption of Risk', 'Signed waiver acknowledging sport participation risks.', id, 1, 0 FROM sports WHERE name='Football';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Insurance / Emergency Information Form', 'Emergency contact, allergies, medication, and insurance information.', id, 1, 0 FROM sports WHERE name='Football';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Medical Certificate / Fit-to-Play Clearance', 'Physician clearance specific to training and competition participation.', id, 1, 0 FROM sports WHERE name='Football';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Parent / Guardian Consent', 'Signed consent for participation in official practices and games.', id, 1, 0 FROM sports WHERE name='Football';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Team Roster Confirmation', 'Official team roster and playing position confirmation.', id, 1, 0 FROM sports WHERE name='Football';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Valid School ID', 'Current valid ISU school identification card.', id, 1, 0 FROM sports WHERE name='Football';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Athlete Waiver and Assumption of Risk', 'Signed waiver acknowledging sport participation risks.', id, 1, 0 FROM sports WHERE name='Futsal M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Insurance / Emergency Information Form', 'Emergency contact, allergies, medication, and insurance information.', id, 1, 0 FROM sports WHERE name='Futsal M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Medical Certificate / Fit-to-Play Clearance', 'Physician clearance specific to training and competition participation.', id, 1, 0 FROM sports WHERE name='Futsal M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Parent / Guardian Consent', 'Signed consent for participation in official practices and games.', id, 1, 0 FROM sports WHERE name='Futsal M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Team Roster Confirmation', 'Official futsal roster and playing position confirmation.', id, 1, 0 FROM sports WHERE name='Futsal M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Valid School ID', 'Current valid ISU school identification card.', id, 1, 0 FROM sports WHERE name='Futsal M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Athlete Waiver and Assumption of Risk', 'Signed waiver acknowledging sport participation risks.', id, 1, 0 FROM sports WHERE name='Karatedo M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Belt Rank / Weight Category Form', 'Declared belt rank, event, and category.', id, 1, 0 FROM sports WHERE name='Karatedo M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Insurance / Emergency Information Form', 'Emergency contact, allergies, medication, and insurance information.', id, 1, 0 FROM sports WHERE name='Karatedo M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Medical Certificate / Fit-to-Play Clearance', 'Physician clearance specific to training and competition participation.', id, 1, 0 FROM sports WHERE name='Karatedo M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Parent / Guardian Consent', 'Signed consent for participation in official practices and games.', id, 1, 0 FROM sports WHERE name='Karatedo M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Valid School ID', 'Current valid ISU school identification card.', id, 1, 0 FROM sports WHERE name='Karatedo M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Athlete Waiver and Assumption of Risk', 'Signed waiver acknowledging sport participation risks.', id, 1, 0 FROM sports WHERE name='Lawn Tennis M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Insurance / Emergency Information Form', 'Emergency contact, allergies, medication, and insurance information.', id, 1, 0 FROM sports WHERE name='Lawn Tennis M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Medical Certificate / Fit-to-Play Clearance', 'Physician clearance specific to training and competition participation.', id, 1, 0 FROM sports WHERE name='Lawn Tennis M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Parent / Guardian Consent', 'Signed consent for participation in official practices and games.', id, 1, 0 FROM sports WHERE name='Lawn Tennis M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Singles / Doubles Entry Confirmation', 'Event entry confirmation for singles, doubles, or mixed doubles.', id, 1, 0 FROM sports WHERE name='Lawn Tennis M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Valid School ID', 'Current valid ISU school identification card.', id, 1, 0 FROM sports WHERE name='Lawn Tennis M/W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Athlete Waiver and Assumption of Risk', 'Signed waiver acknowledging sport participation risks.', id, 1, 0 FROM sports WHERE name='Pencaksilat';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Belt Rank / Weight Category Form', 'Declared rank, event, and category.', id, 1, 0 FROM sports WHERE name='Pencaksilat';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Insurance / Emergency Information Form', 'Emergency contact, allergies, medication, and insurance information.', id, 1, 0 FROM sports WHERE name='Pencaksilat';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Medical Certificate / Fit-to-Play Clearance', 'Physician clearance specific to training and competition participation.', id, 1, 0 FROM sports WHERE name='Pencaksilat';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Parent / Guardian Consent', 'Signed consent for participation in official practices and games.', id, 1, 0 FROM sports WHERE name='Pencaksilat';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Valid School ID', 'Current valid ISU school identification card.', id, 1, 0 FROM sports WHERE name='Pencaksilat';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Athlete Waiver and Assumption of Risk', 'Signed waiver acknowledging sport participation risks.', id, 1, 0 FROM sports WHERE name='Pickle Ball';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Insurance / Emergency Information Form', 'Emergency contact, allergies, medication, and insurance information.', id, 1, 0 FROM sports WHERE name='Pickle Ball';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Medical Certificate / Fit-to-Play Clearance', 'Physician clearance specific to training and competition participation.', id, 1, 0 FROM sports WHERE name='Pickle Ball';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Parent / Guardian Consent', 'Signed consent for participation in official practices and games.', id, 1, 0 FROM sports WHERE name='Pickle Ball';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Singles / Doubles Entry Confirmation', 'Event entry confirmation for singles, doubles, or mixed doubles.', id, 1, 0 FROM sports WHERE name='Pickle Ball';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Valid School ID', 'Current valid ISU school identification card.', id, 1, 0 FROM sports WHERE name='Pickle Ball';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Athlete Waiver and Assumption of Risk', 'Signed waiver acknowledging sport participation risks.', id, 1, 0 FROM sports WHERE name='Rowing';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Insurance / Emergency Information Form', 'Emergency contact, allergies, medication, and insurance information.', id, 1, 0 FROM sports WHERE name='Rowing';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Medical Certificate / Fit-to-Play Clearance', 'Physician clearance specific to training and competition participation.', id, 1, 0 FROM sports WHERE name='Rowing';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Parent / Guardian Consent', 'Signed consent for participation in official practices and games.', id, 1, 0 FROM sports WHERE name='Rowing';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Valid School ID', 'Current valid ISU school identification card.', id, 1, 0 FROM sports WHERE name='Rowing';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Water Safety Clearance', 'Clearance for water-based sport participation.', id, 1, 0 FROM sports WHERE name='Rowing';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Athlete Waiver and Assumption of Risk', 'Signed waiver acknowledging sport participation risks.', id, 1, 0 FROM sports WHERE name='Sepaktakraw';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Insurance / Emergency Information Form', 'Emergency contact, allergies, medication, and insurance information.', id, 1, 0 FROM sports WHERE name='Sepaktakraw';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Medical Certificate / Fit-to-Play Clearance', 'Physician clearance specific to training and competition participation.', id, 1, 0 FROM sports WHERE name='Sepaktakraw';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Parent / Guardian Consent', 'Signed consent for participation in official practices and games.', id, 1, 0 FROM sports WHERE name='Sepaktakraw';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Team Roster Confirmation', 'Official team roster and playing position confirmation.', id, 1, 0 FROM sports WHERE name='Sepaktakraw';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Valid School ID', 'Current valid ISU school identification card.', id, 1, 0 FROM sports WHERE name='Sepaktakraw';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Athlete Waiver and Assumption of Risk', 'Signed waiver acknowledging sport participation risks.', id, 1, 0 FROM sports WHERE name='Softball';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Insurance / Emergency Information Form', 'Emergency contact, allergies, medication, and insurance information.', id, 1, 0 FROM sports WHERE name='Softball';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Medical Certificate / Fit-to-Play Clearance', 'Physician clearance specific to training and competition participation.', id, 1, 0 FROM sports WHERE name='Softball';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Parent / Guardian Consent', 'Signed consent for participation in official practices and games.', id, 1, 0 FROM sports WHERE name='Softball';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Team Roster Confirmation', 'Official team roster and playing position confirmation.', id, 1, 0 FROM sports WHERE name='Softball';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Valid School ID', 'Current valid ISU school identification card.', id, 1, 0 FROM sports WHERE name='Softball';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Athlete Waiver and Assumption of Risk', 'Signed waiver acknowledging sport participation risks.', id, 1, 0 FROM sports WHERE name='Swimming';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Insurance / Emergency Information Form', 'Emergency contact, allergies, medication, and insurance information.', id, 1, 0 FROM sports WHERE name='Swimming';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Medical Certificate / Fit-to-Play Clearance', 'Physician clearance specific to training and competition participation.', id, 1, 0 FROM sports WHERE name='Swimming';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Parent / Guardian Consent', 'Signed consent for participation in official practices and games.', id, 1, 0 FROM sports WHERE name='Swimming';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Swim Time Record', 'Latest qualifying time or event result.', id, 1, 0 FROM sports WHERE name='Swimming';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Valid School ID', 'Current valid ISU school identification card.', id, 1, 0 FROM sports WHERE name='Swimming';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Athlete Waiver and Assumption of Risk', 'Signed waiver acknowledging sport participation risks.', id, 1, 0 FROM sports WHERE name='Table Tennis';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Insurance / Emergency Information Form', 'Emergency contact, allergies, medication, and insurance information.', id, 1, 0 FROM sports WHERE name='Table Tennis';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Medical Certificate / Fit-to-Play Clearance', 'Physician clearance specific to training and competition participation.', id, 1, 0 FROM sports WHERE name='Table Tennis';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Parent / Guardian Consent', 'Signed consent for participation in official practices and games.', id, 1, 0 FROM sports WHERE name='Table Tennis';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Singles / Doubles Entry Confirmation', 'Event entry confirmation for singles, doubles, or mixed doubles.', id, 1, 0 FROM sports WHERE name='Table Tennis';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Valid School ID', 'Current valid ISU school identification card.', id, 1, 0 FROM sports WHERE name='Table Tennis';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Athlete Waiver and Assumption of Risk', 'Signed waiver acknowledging sport participation risks.', id, 1, 0 FROM sports WHERE name='Taekwondo';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Belt Rank / Weight Category Form', 'Declared belt rank, event, and category.', id, 1, 0 FROM sports WHERE name='Taekwondo';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Insurance / Emergency Information Form', 'Emergency contact, allergies, medication, and insurance information.', id, 1, 0 FROM sports WHERE name='Taekwondo';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Medical Certificate / Fit-to-Play Clearance', 'Physician clearance specific to training and competition participation.', id, 1, 0 FROM sports WHERE name='Taekwondo';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Parent / Guardian Consent', 'Signed consent for participation in official practices and games.', id, 1, 0 FROM sports WHERE name='Taekwondo';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Valid School ID', 'Current valid ISU school identification card.', id, 1, 0 FROM sports WHERE name='Taekwondo';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Athlete Waiver and Assumption of Risk', 'Signed waiver acknowledging sport participation risks.', id, 1, 0 FROM sports WHERE name='Volleyball';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Insurance / Emergency Information Form', 'Emergency contact, allergies, medication, and insurance information.', id, 1, 0 FROM sports WHERE name='Volleyball';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Jersey Number Declaration', 'Assigned jersey number and uniform confirmation.', id, 1, 0 FROM sports WHERE name='Volleyball';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Medical Certificate / Fit-to-Play Clearance', 'Physician clearance specific to training and competition participation.', id, 1, 0 FROM sports WHERE name='Volleyball';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Parent / Guardian Consent', 'Signed consent for participation in official practices and games.', id, 1, 0 FROM sports WHERE name='Volleyball';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Team Roster Confirmation', 'Official team roster listing player number and category.', id, 1, 0 FROM sports WHERE name='Volleyball';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Valid School ID', 'Current valid ISU school identification card.', id, 1, 0 FROM sports WHERE name='Volleyball';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Athlete Waiver and Assumption of Risk', 'Signed waiver acknowledging sport participation risks.', id, 1, 0 FROM sports WHERE name='Volleyball Men';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Insurance / Emergency Information Form', 'Emergency contact, allergies, medication, and insurance information.', id, 1, 0 FROM sports WHERE name='Volleyball Men';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Jersey Number Declaration', 'Assigned jersey number and uniform confirmation.', id, 1, 0 FROM sports WHERE name='Volleyball Men';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Medical Certificate / Fit-to-Play Clearance', 'Physician clearance specific to training and competition participation.', id, 1, 0 FROM sports WHERE name='Volleyball Men';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Parent / Guardian Consent', 'Signed consent for participation in official practices and games.', id, 1, 0 FROM sports WHERE name='Volleyball Men';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Team Roster Confirmation', 'Official team roster listing player number and category.', id, 1, 0 FROM sports WHERE name='Volleyball Men';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Valid School ID', 'Current valid ISU school identification card.', id, 1, 0 FROM sports WHERE name='Volleyball Men';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Athlete Waiver and Assumption of Risk', 'Signed waiver acknowledging sport participation risks.', id, 1, 0 FROM sports WHERE name='Volleyball W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Insurance / Emergency Information Form', 'Emergency contact, allergies, medication, and insurance information.', id, 1, 0 FROM sports WHERE name='Volleyball W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Jersey Number Declaration', 'Assigned jersey number and uniform confirmation.', id, 1, 0 FROM sports WHERE name='Volleyball W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Medical Certificate / Fit-to-Play Clearance', 'Physician clearance specific to training and competition participation.', id, 1, 0 FROM sports WHERE name='Volleyball W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Parent / Guardian Consent', 'Signed consent for participation in official practices and games.', id, 1, 0 FROM sports WHERE name='Volleyball W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Team Roster Confirmation', 'Official team roster listing player number and category.', id, 1, 0 FROM sports WHERE name='Volleyball W';
INSERT INTO requirement_types (title,description,sport_id,is_required,registration_initial)
SELECT 'Valid School ID', 'Current valid ISU school identification card.', id, 1, 0 FROM sports WHERE name='Volleyball W';

INSERT INTO announcements (title,body,sport_id,team_id,created_by)
VALUES ('Welcome Athletes','Please complete your biodata and requirement documents before the next training cycle.',NULL,NULL,1);
