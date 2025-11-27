# Leave Management System - Code Review Report

## Executive Summary

This document reviews the PHP Leave Management System codebase against the specified requirements. The review identifies implemented features, missing functionality, and areas requiring fixes.

---

## 1. CORE REQUIREMENTS REVIEW

### 1.1 Roles System

**Status: OK** ✅

**Implementation:**

-   Database schema uses `role ENUM('employee', 'hr')` in `backend/database/schema.sql` (line 13)
-   HR role is treated as admin throughout the codebase
-   `AuthMiddleware::adminOnly()` checks for `role === 'hr'` (line 50 in `backend/middleware/AuthMiddleware.php`)

**Files:**

-   `backend/database/schema.sql` - Database schema
-   `backend/middleware/AuthMiddleware.php` - Role checking
-   `backend/controllers/LeaveController.php` - Admin-only endpoints
-   `backend/controllers/UserController.php` - Admin-only endpoints

**Note:** The spec says "admin (HR is treated as admin)" - the code correctly uses 'hr' role and treats it as admin.

---

### 1.2 Employee Registration

**Status: Partial** ⚠️

**What's Implemented:**

-   Registration form in `frontend/register.html` with fields: name, email, password, department, phone, employee_id, position
-   Backend validation in `backend/controllers/AuthController.php::register()` (lines 16-62)
-   Email uniqueness check via `User::exists()` method
-   Password hashing using `password_hash()` in `backend/config/helpers.php::hashPassword()` (line 116)
-   Duplicate email rejection with error message (line 46 in AuthController)

**What's Missing:**

-   Spec requires "username" field, but code uses "name" instead
-   No explicit username field in registration

**Files:**

-   `frontend/register.html` - Registration form
-   `backend/controllers/AuthController.php` - Registration logic
-   `backend/models/User.php` - User model with `exists()` method

**Fix Suggestion:**

-   Either add a separate `username` field or document that "name" serves as username
-   Current implementation uses "name" which is acceptable but should be documented

---

### 1.3 Authentication (Login/Logout)

**Status: Partial** ⚠️

**What's Implemented:**

-   Login functionality in `backend/controllers/AuthController.php::login()` (lines 64-104)
-   Logout function in `frontend/js/auth.js::logout()` (line 121)
-   Authentication middleware in `backend/middleware/AuthMiddleware.php`
-   Dashboard access protection via `checkAuth()` in frontend pages

**What's Wrong:**

-   **CRITICAL:** Spec requires PHP sessions, but code uses JWT tokens
-   JWT implementation in `backend/config/helpers.php::generateToken()` and `verifyToken()` (lines 70-114)
-   Token stored in localStorage, not PHP sessions

**Files:**

-   `backend/controllers/AuthController.php` - Login endpoint
-   `backend/middleware/AuthMiddleware.php` - Authentication check
-   `backend/config/helpers.php` - JWT token functions (WRONG - should be sessions)
-   `frontend/js/auth.js` - Frontend auth handling

**Fix Suggestion:**

-   Replace JWT with PHP sessions (`$_SESSION`)
-   Use `session_start()` in index.php
-   Store user data in `$_SESSION['user']` instead of JWT tokens
-   Update `AuthMiddleware` to check `$_SESSION` instead of JWT tokens
-   Remove JWT-related code from helpers.php

---

### 1.4 Employee Dashboard Features

**Status: OK** ✅

**Implementation:**

-   Dashboard page: `frontend/dashboard.html`
-   Statistics cards showing Total Leaves, Approved, Pending, Rejected (lines 115-159)
-   "Employees on Leave Today" section (lines 161-182) - matches "Team View" requirement
-   Displays: name, dates/duration, reason for employees on leave today (lines 237-282)

**Files:**

-   `frontend/dashboard.html` - Employee dashboard
-   `backend/controllers/UserController.php::getStats()` - Statistics endpoint
-   `backend/controllers/LeaveController.php::todayLeaves()` - Today's leaves endpoint

**Note:** The "Team View" requirement is satisfied by the "Employees on Leave Today" section.

---

### 1.5 Leave Application

**Status: OK** ✅

**Implementation:**

-   Leave application form in `frontend/apply-leave.html`
-   Required fields: leave_type, start_date, end_date, reason (lines 78-181)
-   Backend validation in `backend/controllers/LeaveController.php::create()` (lines 19-82)
-   Date validation: start_date must be today or later (line 47-50)
-   End date validation: end_date must be after start_date (line 47-50)
-   All fields must be filled (lines 33-38)
-   Duration calculation in `backend/models/Leave.php::calculateDuration()` (lines 145-155)

**Files:**

-   `frontend/apply-leave.html` - Application form
-   `backend/controllers/LeaveController.php::create()` - Create leave endpoint
-   `backend/models/Leave.php::validateLeaveDates()` - Date validation (lines 129-143)
-   `backend/models/Leave.php::calculateDuration()` - Duration calculation

---

### 1.6 My Leaves Page

**Status: Partial** ⚠️

**What's Implemented:**

-   "My Leaves" page in `frontend/my-leaves.html`
-   Displays: type, dates, duration, reason, status, admin comments (lines 120-211)
-   Lists all user's leave requests
-   Shows admin comments and rejection reasons

**What's Missing:**

-   **CRITICAL:** Edit functionality for pending leaves - NOT IMPLEMENTED
-   **CRITICAL:** Cancel functionality for pending leaves - NOT IMPLEMENTED
-   No edit/cancel buttons in the UI
-   No backend endpoints for update/delete

**Files:**

-   `frontend/my-leaves.html` - My leaves page
-   `backend/controllers/LeaveController.php::myLeaves()` - Get user's leaves endpoint

**Fix Suggestion:**

-   Add "Edit" and "Cancel" buttons for pending leaves in `my-leaves.html`
-   Create `PUT /api/leaves/:id` endpoint for editing
-   Create `DELETE /api/leaves/:id` endpoint for canceling
-   Add `update()` and `delete()` methods in `Leave` model
-   Add `update()` and `cancel()` methods in `LeaveController`
-   Validate that only pending leaves can be edited/canceled
-   Validate that user can only edit/cancel their own leaves

---

### 1.7 Admin Dashboard Features

**Status: OK** ✅

**Implementation:**

-   Admin dashboard in `frontend/admin-dashboard.html`
-   Statistics cards: Total Employees, Total Leaves, Pending, Approved, Rejected, On Leave Today (lines 86-148)
-   Pending leave requests table with Approve/Reject actions (lines 150-171)
-   Backend stats endpoint: `backend/controllers/UserController.php::getAdminStats()` (lines 34-53)

**Files:**

-   `frontend/admin-dashboard.html` - Admin dashboard
-   `backend/controllers/UserController.php::getAdminStats()` - Admin statistics

---

### 1.8 Admin Manage Leave Requests

**Status: OK** ✅

**Implementation:**

-   "Manage Leave Requests" page in `frontend/admin-leaves.html`
-   Tabs for Pending, Approved, Rejected (lines 79-128)
-   Approve/Reject functionality with optional comments (lines 311-367)
-   Only admins can change status via `adminOnly()` middleware

**Files:**

-   `frontend/admin-leaves.html` - Manage leaves page
-   `backend/controllers/LeaveController.php::approve()` - Approve endpoint (lines 141-171)
-   `backend/controllers/LeaveController.php::reject()` - Reject endpoint (lines 173-208)
-   `backend/models/Leave.php::approve()` - Approve method (lines 89-106)
-   `backend/models/Leave.php::reject()` - Reject method (lines 108-127)

---

### 1.9 Employee Management Page

**Status: OK** ✅

**Implementation:**

-   Employee Management page in `frontend/admin-employees.html`
-   Lists employees with: name, email, department, role (lines 125-152)
-   Backend endpoint: `backend/controllers/UserController.php::getEmployees()` (lines 55-72)
-   Only accessible to admins

**Files:**

-   `frontend/admin-employees.html` - Employee management page
-   `backend/controllers/UserController.php::getEmployees()` - Get employees endpoint

---

### 1.10 Database Schema

**Status: OK** ✅

**Implementation:**

-   Users table in `backend/database/schema.sql` (lines 8-24)
-   Leaves table in `backend/database/schema.sql` (lines 27-50)
-   Foreign key relationship: `leaves.employee_id` references `users.id` (line 44)
-   Leave fields include: user reference, leave_type, start_date, end_date, status, reason, comments, applied_at, updated_at

**Files:**

-   `backend/database/schema.sql` - Database schema

---

### 1.11 Validation and Bug Fixes

**Status: Partial** ⚠️

**What's Implemented:**

-   Duplicate email rejection: `backend/controllers/AuthController.php::register()` (line 45-47)
-   Date validation: `backend/models/Leave.php::validateLeaveDates()` (lines 129-143)
-   Past date rejection: checks if start_date < today (line 138)
-   Invalid date range rejection: checks if end_date <= start_date (line 134)
-   Duration calculation happens after validation (lines 58-62 in LeaveController)

**What's Missing:**

-   The "known incorrect leave duration issue" - duration is calculated correctly, but should verify this is working as expected

**Files:**

-   `backend/controllers/AuthController.php` - Registration validation
-   `backend/models/Leave.php::validateLeaveDates()` - Date validation
-   `backend/models/Leave.php::calculateDuration()` - Duration calculation

---

## 2. TECH-SPECIFIC REQUIREMENTS

### 2.1 PHP Sessions (Not JWT)

**Status: Missing** ❌

**Current Implementation:**

-   Uses JWT tokens in `backend/config/helpers.php` (lines 70-114)
-   Tokens stored in localStorage on frontend
-   Token-based authentication in `AuthMiddleware`

**Required:**

-   PHP sessions using `$_SESSION`
-   `session_start()` in entry point
-   Session-based authentication

**Files to Modify:**

-   `backend/config/helpers.php` - Remove JWT functions, add session helpers
-   `backend/middleware/AuthMiddleware.php` - Use `$_SESSION` instead of JWT
-   `backend/index.php` - Add `session_start()`
-   `frontend/js/auth.js` - Remove token management, use cookies or session

**Fix Suggestion:**

1. Remove `generateToken()` and `verifyToken()` functions
2. Add `startSession()` and `getSessionUser()` functions
3. Update `AuthController::login()` to set `$_SESSION['user_id']` and `$_SESSION['user']`
4. Update `AuthMiddleware::authenticate()` to check `$_SESSION['user_id']`
5. Update frontend to not store tokens in localStorage

---

### 2.2 MySQL Database

**Status: OK** ✅

**Implementation:**

-   Uses MySQL with PDO in `backend/config/Database.php`
-   MySQL schema in `backend/database/schema.sql`
-   No MongoDB code found

**Files:**

-   `backend/config/Database.php` - Database connection
-   `backend/database/schema.sql` - MySQL schema

---

## 3. EXTRA FEATURES (Not in Spec)

The following features are implemented but not mentioned in the specification:

1. **Employee ID Field** - Registration includes `employee_id` field (not in spec)
2. **Position Field** - Registration includes `position` field (not in spec)
3. **Joining Date** - Users table has `joining_date` field (not in spec)
4. **Duration Unit** - Leaves can be in "hours" or "days" (spec only mentions days)
5. **Rejected Reason** - Separate field for rejection reason (not just admin comment)
6. **Reviewed By** - Tracks which admin reviewed the leave (not in spec)
7. **Admin Sidebar** - Modern sidebar navigation for admin panel (UI enhancement)
8. **Auto-calculated Duration** - Duration is auto-calculated from dates (convenience feature)
9. **Today's Leaves Details** - Admin dashboard shows detailed list of today's leaves (enhancement)
10. **Employee Statistics** - Admin can see leave stats per employee (enhancement)

---

## 4. SUMMARY OF ISSUES

### Critical Issues (Must Fix):

1. **JWT Instead of Sessions** ❌

    - Location: `backend/config/helpers.php`, `backend/middleware/AuthMiddleware.php`
    - Fix: Replace JWT with PHP sessions

2. **Missing Edit/Cancel Functionality** ❌
    - Location: `frontend/my-leaves.html`, `backend/controllers/LeaveController.php`
    - Fix: Add edit and cancel endpoints and UI buttons

### Minor Issues:

1. **Username vs Name** ⚠️

    - Spec says "username" but code uses "name"
    - Fix: Document or add separate username field

2. **Registration Fields** ⚠️
    - Spec says "username, email, password, department, phone"
    - Code has: name, email, password, department, phone, employee_id, position
    - Fix: Document extra fields or remove if not needed

---

## 5. FILES SUMMARY

### Backend Files:

-   `backend/controllers/AuthController.php` - Authentication
-   `backend/controllers/LeaveController.php` - Leave management
-   `backend/controllers/UserController.php` - User management
-   `backend/models/User.php` - User model
-   `backend/models/Leave.php` - Leave model
-   `backend/middleware/AuthMiddleware.php` - Authentication middleware
-   `backend/routes/api.php` - API routing
-   `backend/config/Database.php` - Database connection
-   `backend/config/helpers.php` - Helper functions (JWT - needs replacement)
-   `backend/database/schema.sql` - Database schema

### Frontend Files:

-   `frontend/dashboard.html` - Employee dashboard
-   `frontend/my-leaves.html` - My leaves page (needs edit/cancel)
-   `frontend/apply-leave.html` - Apply leave form
-   `frontend/admin-dashboard.html` - Admin dashboard
-   `frontend/admin-leaves.html` - Manage leaves page
-   `frontend/admin-employees.html` - Employee management
-   `frontend/register.html` - Registration form
-   `frontend/login.html` - Login page
-   `frontend/js/auth.js` - Authentication JavaScript
-   `frontend/js/api.js` - API calls

---

## 6. RECOMMENDATIONS

1. **Immediate Priority:**

    - Replace JWT with PHP sessions
    - Add edit/cancel functionality for pending leaves

2. **Documentation:**

    - Document that "name" serves as username
    - Document extra fields (employee_id, position) if keeping them

3. **Testing:**
    - Test date validation edge cases
    - Test duration calculation accuracy
    - Test edit/cancel permissions

---

**Review Date:** Generated automatically
**Reviewed By:** Code Review System
