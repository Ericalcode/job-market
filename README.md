CareerConnect - Job Market Platform

CareerConnect is a full-stack PHP and MySQL/TiDB web application designed to connect Jobseekers and Employers seamlessly. Features include secure account authentication, role-based dashboards, user profile management (skills, profile photos, courses), job posting/application capabilities, an MPesa subscription model, and administrator management tools.

🛠️ System Architecture & Tech Stack

Backend: PHP (Native / Object-Oriented)

Database: TiDB Cloud / MySQL (utilizing mysqli extension)

Frontend: HTML5, CSS3, JavaScript (Modal & UI interactions)

Authentication: PHP Session Management with Password Hashing (password_hash & password_verify)

Hosting Environment: Docker container deployed on Render

🚀 Key Features

1. Account Management & Security

Role-Based Access Control (RBAC): Three distinct roles: jobseeker, employer, and admin.

Profile Customization: Users can upload profile photos, list professional skills, add educational qualifications/courses, and write bios.

Security Control: Support for password updates and admin-approved email change requests.

2. Job Postings & Applications

Employers: Post new job openings, view submitted candidate applications, and inspect jobseeker profiles.

Jobseekers: Browse available job listings, apply with one click, and track application statuses.

3. CareerConnect Pro & Subscription Management

MPesa Integration Prompt: "Go CareerConnect Pro" modal providing payment steps to upgrade via MPesa (0796168453).

Admin Clearing System: Administrators can set/edit subscription fees and toggle payment status (PAID / NOT PAID) via a dropdown.

4. Admin Management Panel

Approve or reject user email change requests.

Create new user accounts and delete inactive or violating accounts.

View real-time database reports and subscription records.

🗄️ Database Schema Setup

Run the following SQL queries in your MySQL / TiDB Cloud SQL Editor:

CREATE DATABASE IF NOT EXISTS test;
USE test;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    pending_email VARCHAR(100) DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('jobseeker', 'employer', 'admin') NOT NULL DEFAULT 'jobseeker',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Profiles Table
CREATE TABLE IF NOT EXISTS profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    title VARCHAR(150) DEFAULT NULL,
    skills TEXT DEFAULT NULL,
    courses TEXT DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    profile_pic VARCHAR(255) DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Jobs Table
CREATE TABLE IF NOT EXISTS jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employer_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(100) NOT NULL,
    status ENUM('active', 'closed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Applications Table
CREATE TABLE IF NOT EXISTS applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,
    jobseeker_id INT NOT NULL,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (jobseeker_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Subscriptions Table
CREATE TABLE IF NOT EXISTS subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL DEFAULT 500.00,
    payment_status ENUM('PAID', 'NOT PAID') DEFAULT 'NOT PAID',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);


⚙️ Installation & Local Setup

Clone the Repository:

git clone https://github.com/Ericalcode/job-market.git
cd job-market


Configure Database Connection (db.php):
Ensure your database credentials match your local MySQL or TiDB Cloud instance:

$host = 'YOUR_TIDB_HOST';
$port = 4000;
$user = 'YOUR_TIDB_USER';
$password = 'YOUR_TIDB_PASSWORD';
$dbname = 'test';


Run Locally:
If using built-in PHP server:

php -S localhost:8000


Navigate to http://localhost:8000 in your web browser.

📄 License

This project is open-source and available under the MIT License.
