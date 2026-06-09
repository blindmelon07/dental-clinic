# DentCare System — Process Flow Documentation

**System:** DentCare Dental Clinic Management System  
**Stack:** Laravel 11 · Filament Admin Panel · Livewire · Spatie Permission  
**Last Updated:** 2026-06-08

---

## Table of Contents

1. [System Overview](#1-system-overview)
2. [User Roles & Access Levels](#2-user-roles--access-levels)
3. [Process Flow 1 — Patient Registration](#3-process-flow-1--patient-registration)
4. [Process Flow 2 — Patient Login & Dashboard](#4-process-flow-2--patient-login--dashboard)
5. [Process Flow 3 — Appointment Booking](#5-process-flow-3--appointment-booking)
6. [Process Flow 4 — Admin / Staff Login](#6-process-flow-4--admin--staff-login)
7. [Process Flow 5 — Admin Manages Appointments](#7-process-flow-5--admin-manages-appointments)
8. [Process Flow 6 — Dentist Completes a Visit](#8-process-flow-6--dentist-completes-a-visit)
9. [Process Flow 7 — Billing & Payment](#9-process-flow-7--billing--payment)
10. [Appointment Status Lifecycle](#10-appointment-status-lifecycle)
11. [Key Files Reference](#11-key-files-reference)

---

## 1. System Overview

DentCare is a web-based dental clinic management system with two distinct interfaces:

| Interface | URL Prefix | Technology | Who Uses It |
|-----------|------------|------------|-------------|
| **Patient Portal** | `/patient/*` | Livewire + Blade | Patients (self-service) |
| **Admin Panel** | `/admin/*` | Filament | Super Admin, Admin, Receptionist, Dentist |

The system separates patient-facing self-service from staff-facing management, connected through a shared database with role-based access control.

---

## 2. User Roles & Access Levels

| Role | Panel | Key Capabilities |
|------|-------|-----------------|
| `super_admin` | Admin (`/admin`) | Full access to everything |
| `admin` | Admin (`/admin`) | All features except deleting users |
| `receptionist` | Admin (`/admin`) | Patient management, appointments, invoices |
| `dentist` | Admin (`/admin`) | View patients/appointments, manage dental records, create prescriptions |
| `patient` | Patient Portal (`/patient`) | Book appointments, view own records and invoices |

After login, the system automatically routes each role to the correct panel:
- Staff roles → `/admin`
- Patients → `/patient/dashboard`

---

## 3. Process Flow 1 — Patient Registration

**Entry point:** `/register`  
**Controller:** `app/Http/Controllers/Auth/RegisteredUserController.php`  
**View:** `resources/views/auth/register.blade.php`

```
[Visitor]
    │
    ▼
[Opens /register page]
    │
    ▼
[Fills Registration Form]
    ├── Account Info:    name, email, password, phone
    └── Personal Info:   first name, last name, date of birth, gender, address, city
    │
    ▼
[Form Submitted → RegisteredUserController::store()]
    │
    ├── Validate inputs
    │       ├── email: required, unique in users table
    │       ├── password: min 8 characters
    │       └── date_of_birth: must be before today
    │
    ├── Create User record
    │       └── Assign role: "patient"
    │
    ├── Create Patient record (auto-linked to User)
    │       ├── patient_number generated: "PT-YYYYMMDD-0001"
    │       └── Linked to active Clinic
    │
    ├── Auto-login the new user (Auth::login)
    │
    └── Redirect → /patient/dashboard
```

**Data Created:**
- `users` table: account credentials + role
- `patients` table: profile, medical info, patient number
- Spatie role assignment: `model_has_roles`

---

## 4. Process Flow 2 — Patient Login & Dashboard

**Entry point:** `/login`  
**Controller:** `app/Http/Controllers/Auth/AuthenticatedSessionController.php`  
**Dashboard Component:** `app/Livewire/Patient/Dashboard.php`

```
[Patient]
    │
    ▼
[Opens /login]
    │
    ▼
[Enters email + password]
    │
    ▼
[AuthenticatedSessionController::store()]
    │
    ├── Validate credentials
    ├── Auth::attempt()
    │       └── Fail → back to login with error
    │
    ├── Regenerate session (security)
    │
    └── Check role
            ├── Staff role → /admin
            └── Patient role → /patient/dashboard
    │
    ▼
[Patient Dashboard shows:]
    ├── Upcoming confirmed appointments
    ├── Recent dental records
    └── Unpaid invoices
```

**Patient Portal Navigation:**

| Page | URL | Purpose |
|------|-----|---------|
| Dashboard | `/patient/dashboard` | Overview & quick links |
| Book Appointment | `/patient/book` | 4-step booking wizard |
| My Appointments | `/patient/appointments` | View all appointments, cancel |
| My Records | `/patient/records` | Dental records & X-rays |
| My Invoices | `/patient/invoices` | Invoices & payment status |
| Profile | `/patient/profile` | Update profile photo |

---

## 5. Process Flow 3 — Appointment Booking

**Component:** `app/Livewire/Patient/BookAppointment.php`  
**View:** `resources/views/livewire/patient/book-appointment.blade.php`  
**URL:** `/patient/book`

The booking flow is a **4-step wizard** built with Livewire (real-time, no full page reloads).

### Step 1 — Select a Service

```
[Patient clicks "Book Appointment"]
    │
    ▼
[Step 1: Choose Service]
    ├── Services displayed grouped by category
    ├── Each card shows: name, description, price, duration
    └── Patient clicks a service → advances to Step 2
```

### Step 2 — Select a Dentist

```
[Step 2: Choose Dentist]
    ├── All active dentists listed
    ├── Each card shows: name, specialization, bio, consultation fee
    └── Patient clicks a dentist → advances to Step 3
```

### Step 3 — Pick Date & Time

```
[Step 3: Select Date & Time]
    │
    ├── Date picker (minimum: tomorrow onwards)
    │
    ├── On date selected → system calculates available slots:
    │       ├── Reads dentist's DentistSchedule for that day of week
    │       ├── Filters out slots that are already booked
    │       ├── Accounts for service duration (no overlapping appointments)
    │       └── Excludes cancelled/no-show appointments from block list
    │
    ├── Time slots shown in a grid (e.g., 9:00 AM, 9:30 AM, 10:00 AM…)
    │
    └── Patient selects a time → advances to Step 4
```

### Step 4 — Confirm Booking

```
[Step 4: Review & Confirm]
    ├── Summary shown:
    │       ├── Selected service (name, price, duration)
    │       ├── Selected dentist
    │       ├── Appointment date & time
    │       └── Optional: Chief Complaint textarea
    │
    ├── Patient clicks "Confirm Appointment"
    │
    ├── Validation runs (all steps must be complete)
    │
    ├── Appointment::create() called with:
    │       ├── status: "pending"
    │       ├── appointment_number: "APT-YYYYMMDD-0001"
    │       ├── patient_id, dentist_id, service_id, clinic_id
    │       ├── appointment_date, start_time, end_time (auto-calculated)
    │       ├── chief_complaint (optional)
    │       └── booked_by: current user ID
    │
    └── Success screen → shows appointment number for reference
```

**Appointment is created with status `pending` — awaiting admin/receptionist confirmation.**

---

## 6. Process Flow 4 — Admin / Staff Login

**Entry point:** `/admin/login` (Filament)

```
[Staff Member]
    │
    ▼
[Opens /admin/login]
    │
    ▼
[Enters email + password]
    │
    ▼
[Filament authenticates → checks panel access]
    │
    ├── panelAccessCheck: user must NOT have "patient" role
    │
    └── Redirect → /admin (Dashboard)
```

**Admin Dashboard Widgets:**

| Widget | Shows |
|--------|-------|
| Appointment Stats | Counts by status (pending, confirmed, completed, cancelled) |
| Billing Stats | Revenue totals |
| Inventory Stats | Medicine/supply levels |
| Recent Appointments | Latest bookings |
| Cleaning Reminders | Patients due for cleaning |
| Low Stock Medicines | Inventory alerts |

---

## 7. Process Flow 5 — Admin Manages Appointments

**Resource:** `app/Filament/Resources/AppointmentResource.php`  
**URL:** `/admin/appointments`

### 7.1 Viewing Appointment Requests

```
[Admin / Receptionist opens /admin/appointments]
    │
    ▼
[List View shows all appointments]
    ├── Columns: Appointment #, Date, Time, Patient, Dentist, Service, Type, Status
    ├── Status badge colors:
    │       ├── pending    → amber/warning
    │       ├── confirmed  → blue/info
    │       ├── completed  → green/success
    │       ├── cancelled  → red/danger
    │       └── no_show    → gray
    │
    ├── Filters: by status, by type, by dentist
    ├── Search: by appointment number
    └── Sorting: by appointment date (newest first)
```

### 7.2 Confirming an Appointment

```
[Admin clicks an appointment → selects "Confirm"]
    │
    ▼
[Confirmation dialog appears: "Are you sure?"]
    │
    ▼
[Admin confirms]
    │
    ▼
[Appointment status: pending → confirmed]
    │
    └── Patient's appointment now shows "Confirmed" in their portal
```

### 7.3 Cancelling an Appointment

```
[Admin clicks an appointment → selects "Cancel"]
    │
    ▼
[Cancellation dialog]
    │
    ▼
[Admin confirms]
    │
    ▼
[Appointment status: pending/confirmed → cancelled]
    │
    └── Cancellation reason can be recorded in the notes field
```

### 7.4 Creating an Appointment (Walk-in / Phone Booking)

```
[Admin clicks "New Appointment"]
    │
    ▼
[Create Appointment Form]
    ├── Patient (searchable by name or patient number)
    ├── Dentist (live relationship selector)
    ├── Service (auto-updates end_time based on duration)
    ├── Appointment Type (consultation, follow-up, emergency, cleaning, procedure, x-ray)
    ├── Date (minimum: today)
    ├── Start Time / End Time (end auto-calculated from service duration)
    ├── Status (can set directly: pending, confirmed, etc.)
    ├── Chief Complaint & Notes
    └── Cancellation Reason (visible only when status = cancelled)
    │
    ▼
[Save → Appointment created]
```

### 7.5 Editing an Appointment

```
[Admin clicks an appointment → "Edit"]
    │
    ▼
[Edit form with all fields pre-filled]
    │
    ▼
[Admin modifies fields → Save]
    │
    └── Changes saved, audit trail updated
```

---

## 8. Process Flow 6 — Dentist Completes a Visit

**Actors:** Dentist (admin panel access, limited permissions)

```
[Patient arrives for confirmed appointment]
    │
    ▼
[Receptionist updates status: confirmed → in_progress]
    │
    ▼
[Dentist opens the appointment in /admin/appointments]
    │
    ▼
[Dentist creates a Dental Record for this visit]
    ├── Chief complaint & diagnosis
    ├── Treatment provided
    ├── Dental X-rays (uploaded images)
    └── Linked to the appointment
    │
    ▼
[Dentist creates a Prescription (if needed)]
    ├── Medication list with dosage & instructions
    └── Linked to the patient
    │
    ▼
[Dentist or Receptionist updates status: in_progress → completed]
    │
    ▼
[Receptionist creates Invoice]
    ├── Auto-linked to appointment & patient
    ├── Line items: services rendered (from InvoiceItem)
    ├── Subtotal → Discount → Tax → Total calculated
    └── Balance due tracked
    │
    └── Patient can view invoice in /patient/invoices
```

---

## 9. Process Flow 7 — Billing & Payment

**Resources:** `app/Filament/Resources/InvoiceResource.php`  
**Patient view:** `app/Livewire/Patient/MyInvoices.php`

```
[Invoice created after visit completion]
    │
    ▼
[Invoice status: unpaid]
    │
    ▼
[Patient views invoice at /patient/invoices]
    │
    ▼
[Patient pays at clinic counter]
    │
    ▼
[Receptionist records Payment in admin panel]
    ├── Amount, payment method, reference number
    └── Linked to Invoice
    │
    ▼
[Invoice balance due recalculated]
    │
    └── When fully paid → Invoice status: paid
```

---

## 10. Appointment Status Lifecycle

The appointment moves through the following states:

```
                  ┌─────────┐
     Booking ───► │ PENDING │
                  └────┬────┘
                       │  Admin confirms
                       ▼
                  ┌───────────┐
                  │ CONFIRMED │
                  └─────┬─────┘
                        │  Patient arrives
                        ▼
                  ┌─────────────┐
                  │ IN_PROGRESS │
                  └──────┬──────┘
                         │  Visit done
                         ▼
                  ┌───────────┐
                  │ COMPLETED │
                  └───────────┘

  Any stage ──► CANCELLED  (by admin, receptionist, or patient)
  Confirmed  ──► NO_SHOW   (patient did not appear)
```

| Status | Set By | Meaning |
|--------|--------|---------|
| `pending` | System (on booking) | Awaiting staff review |
| `confirmed` | Admin / Receptionist | Slot reserved, patient notified |
| `in_progress` | Receptionist / Dentist | Patient is currently being seen |
| `completed` | Receptionist / Dentist | Visit finished |
| `cancelled` | Admin / Receptionist / Patient | Appointment cancelled |
| `no_show` | Admin / Receptionist | Patient did not arrive |

---

## 11. Key Files Reference

### Authentication & Registration

| File | Purpose |
|------|---------|
| `app/Http/Controllers/Auth/RegisteredUserController.php` | Patient registration logic |
| `app/Http/Controllers/Auth/AuthenticatedSessionController.php` | Login / logout |
| `resources/views/auth/register.blade.php` | Registration form UI |
| `resources/views/auth/login.blade.php` | Login form UI |
| `routes/auth.php` | Auth routes |

### Patient Portal

| File | Purpose |
|------|---------|
| `app/Livewire/Patient/BookAppointment.php` | 4-step booking wizard logic |
| `app/Livewire/Patient/MyAppointments.php` | Patient appointment list |
| `app/Livewire/Patient/MyRecords.php` | Patient dental records |
| `app/Livewire/Patient/MyInvoices.php` | Patient invoices |
| `resources/views/livewire/patient/` | All patient portal views |
| `routes/web.php` | Patient portal routes (`/patient/*`) |

### Admin Panel (Filament)

| File | Purpose |
|------|---------|
| `app/Filament/Resources/AppointmentResource.php` | Appointment CRUD & actions |
| `app/Filament/Resources/PatientResource.php` | Patient management |
| `app/Filament/Resources/DentalRecordResource.php` | Dental records management |
| `app/Filament/Resources/InvoiceResource.php` | Billing management |
| `app/Filament/Resources/PrescriptionResource.php` | Prescription management |
| `app/Filament/Pages/Dashboard.php` | Admin dashboard |
| `app/Providers/Filament/AdminPanelProvider.php` | Admin panel configuration |

### Models

| Model | File | Key Role |
|-------|------|----------|
| `User` | `app/Models/User.php` | Auth + roles via Spatie |
| `Patient` | `app/Models/Patient.php` | Patient profile & medical history |
| `Dentist` | `app/Models/Dentist.php` | Dentist profile & schedules |
| `Appointment` | `app/Models/Appointment.php` | Booking record with status enum |
| `DentistSchedule` | `app/Models/DentistSchedule.php` | Working hours per day |
| `Service` | `app/Models/Service.php` | Services with price & duration |
| `DentalRecord` | `app/Models/DentalRecord.php` | Visit summaries |
| `Prescription` | `app/Models/Prescription.php` | Medication prescriptions |
| `Invoice` | `app/Models/Invoice.php` | Billing with recalculate() |
| `Payment` | `app/Models/Payment.php` | Payment transactions |

### Permissions & Roles

| File | Purpose |
|------|---------|
| `database/seeders/PermissionSeeder.php` | Defines all roles & permissions |
| `config/permission.php` | Spatie Permission configuration |

---

*End of Process Flow Documentation*
