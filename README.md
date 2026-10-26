# Laravel Request System

## Project Description

The Laravel Request System is a web-based application developed using Laravel and MySQL. It is created as part of the DevOps Laboratory 1 activity.

## Student Information

**Name:** Jan Emmanuel Ical  
**Course:** BSIT  
**Year and Section:** 4th Year - Block 3

## Software Requirements

- PHP
- Composer
- Laravel
- MySQL
- XAMPP
- Git
- GitHub
- Node.js and NPM

## Installation

1. Clone the repository.
2. Open the project folder in the terminal.
3. Install the Laravel dependencies:

```bash
composer install
```

## Laboratory 2 - Request Data Model

### Request Table

The Laravel request system uses a `requests` table to store submitted requests.

| Field | Type | Constraint | Purpose |
|---|---|---|---|
| id | BIGINT UNSIGNED | Primary Key, Auto Increment | Unique request number |
| requester_name | VARCHAR(100) | Required | Person submitting the request |
| requester_email | VARCHAR(255) | Required | Contact email of requester |
| item_name | VARCHAR(150) | Required | Requested item or service |
| quantity | INT UNSIGNED | Required, greater than 0 | Requested quantity |
| purpose | TEXT | Required | Reason for the request |
| status | VARCHAR(20) | Default: pending | Current request state |
| created_at | TIMESTAMP | Required | Creation time |
| updated_at | TIMESTAMP | Required | Last update time |

### Migration

Create the migration using:

```bash
php artisan make:migration create_requests_table
```

### Lab 3
Driver: [Jan Emmanuel M. Ical]
Reviewer: [Kris Angela Bon]

Driver Responsibilities:
- Implement the security changes
- Run tests
- Push the feature branch
- Create the pull request

Reviewer Responsibilities:
- Inspect the implementation
- Test the system
- Leave a substantive review comment
- Approve the pull request after fixes

## Laboratory 3 - Secure Request Access

### Ownership Rules

- Guests cannot open any request page; they are redirected to the login page.
- Students see and open only the requests whose `user_id` matches their own account.
- Students can create requests. `user_id`, requester name, requester email and the initial `pending` status are assigned on the server, never taken from the form.
- Students cannot change a request's status.
- Administrators (`is_admin = 1`) can list and open every request and change its status to `pending`, `approved` or `rejected`.

### Denial Response

A student who opens another student's request receives **403 Forbidden**. Every request action calls `Gate::authorize()` with `ServiceRequestPolicy` before any data is returned or written. A request ID that does not exist returns 404.

### Route Summary

All request routes are inside the `auth` middleware group in `routes/web.php`.

| Method | URL | Action | Who may use it |
|---|---|---|---|
| GET | `/requests` | `index` - list and creation form | Students (own requests only), administrators (all) |
| POST | `/requests` | `store` - create a request | Students |
| GET | `/requests/{serviceRequest}` | `show` - request details | Owner or administrator |
| PATCH | `/requests/{serviceRequest}/status` | `updateStatus` - change status | Administrators |

### File Responsibilities

| File or area | Maintainer | Purpose |
|---|---|---|
| `app/Policies/ServiceRequestPolicy.php` | Jan Emmanuel Ical | Authorization rules for viewing, creating and updating requests |
| `app/Http/Controllers/ServiceRequestController.php` | Jan Emmanuel Ical | List scoping, validation, trusted field assignment |
| `routes/web.php` | Jan Emmanuel Ical | Route definitions and auth middleware group |
| `resources/views/requests/` | Jan Emmanuel Ical | Escaped output, CSRF fields, status form |
| `README.md` | Jan Emmanuel Ical, Kris Angela Bon | Ownership policy and setup documentation |

### Testing Steps

1. Run `php artisan migrate`, then create two student accounts and one administrator account (`is_admin = 1`).
2. As a guest, open `/requests` and `/requests/1`: both redirect to `/login`.
3. Log in as each student: the list shows only that student's requests, and their own request opens.
4. As a student, open the other student's request ID directly: 403 Forbidden.
5. As a student, send a status PATCH: 403 Forbidden, and the stored status is unchanged.
6. As the administrator, list all requests and change a status: the new status is saved.
7. Submit quantity `0`, `-1` or `1.5`, or a blank item name: validation errors are shown and no row is saved.
8. Submit extra `user_id`, `status` or `is_admin` fields: they are ignored.
9. Submit text containing `<script>` and an apostrophe: the markup is shown as plain text and the apostrophe is stored correctly.
10. Submit a form with a missing or invalid CSRF token: 419 Page Expired.

## Laboratory 3 Verification
Verification instruction: Test student ownership and deny access to another student's requests.

Verification instruction: Test administrator access and administrator-only status updates.