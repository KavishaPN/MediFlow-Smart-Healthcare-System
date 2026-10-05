# MediFlow – Smart Healthcare System

MediFlow is a web-based Smart Healthcare Management System developed to simplify and organize healthcare-related activities such as patient management, doctor management, department management, appointment scheduling, and doctor-to-doctor referrals.

The system provides separate access for Admin, Doctor, and Patient users with role-based functionalities.

## Project Overview

MediFlow is designed to provide a centralized platform for managing essential healthcare operations.

The system allows:

- Admins to manage departments and doctors
- Patients to register, manage their profiles, and book appointments
- Doctors to view assigned appointments and update appointment status
- Doctors to create and manage referrals to other doctors
- Patients to view appointment and referral information
- Secure role-based access to different parts of the system

## Modules

### 1. Admin Module

The Admin module provides administrative control over the healthcare system.

Features include:

- Admin login
- Department management
- Add, edit, and delete departments
- Doctor management
- Add, edit, and delete doctors
- View registered patients
- View and manage appointment information
- Manage doctor-department assignments

### 2. Doctor Module

The Doctor module allows doctors to manage their assigned healthcare activities.

Features include:

- Doctor login
- View assigned appointments
- View patient information related to appointments
- Update appointment status
- Create doctor-to-doctor referrals
- Manage referral requests
- Accept or reject referrals
- Update referral status

### 3. Patient Module

The Patient module allows patients to access and manage their healthcare-related information.

Features include:

- Patient registration
- Patient login
- Patient profile management
- Book appointments
- View appointment details
- View appointment status
- View doctor and department information
- View referral information

### 4. Department Management

The system allows the administrator to manage healthcare departments.

Features include:

- Add departments
- Edit departments
- Delete departments
- Assign doctors to departments
- Display department information along with doctors

### 5. Appointment Management

MediFlow provides an appointment management system connecting patients and doctors.

Appointment information includes:

- Patient
- Doctor
- Appointment date
- Appointment time
- Reason for appointment
- Appointment status
- Appointment creation date

Appointment statuses include:

- Pending
- Approved
- Completed
- Cancelled

### 6. Doctor-to-Doctor Referral

MediFlow includes a doctor referral system that allows doctors to refer patients to other doctors when further consultation is required.

Referral features include:

- Create referrals
- Select another doctor for referral
- Specify referral priority
- Referral status management
- Accept or reject referral requests
- Track completed referrals

Referral priorities include:

- Normal
- Urgent
- Emergency

## User Roles

| User Role | Main Responsibilities |
|-----------|------------------------|
| Admin | Manage departments, doctors, patients, and appointments |
| Doctor | Manage appointments and doctor referrals |
| Patient | Register, manage profile, book appointments, and view healthcare information |

## Technologies Used

### Frontend

- HTML5
- CSS3
- JavaScript
- Bootstrap

### Backend

- PHP

### Database

- MySQL / MariaDB

### Development Environment

- XAMPP
- Apache
- phpMyAdmin

## Database

The project uses a relational database named:

`db_mediflow`

The database contains tables for managing the main system entities, including:

- Admin
- Departments
- Doctors
- Patients
- Appointments

The system uses relationships between entities such as doctors and departments, and patients, doctors, and appointments.

## Security Features

MediFlow implements basic security practices for a web-based healthcare management system.

These include:

- Role-based session authentication
- Separate login access for Admin, Doctor, and Patient
- Session-based access control
- PDO prepared statements for database operations
- Unique constraints for important fields
- Protected access to role-specific pages
- Password-based patient registration and authentication

## Project Structure

A simplified project structure is:

MediFlow/
│
├── admin/
├── doctor/
├── patient/
├── config/
├── auth/
│
├── index.php
│
└── other PHP, CSS and JavaScript files
