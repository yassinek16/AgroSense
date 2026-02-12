# Admin Dashboard - Users Management Module Documentation

## 📋 Overview

A complete Users Management module for Symfony 6.4 admin dashboard, featuring user listing, editing, deletion, and role management with full CSRF protection, privilege escalation prevention, and professional UI inspired by the GlassDash admin template.

---

## 🏗️ Architecture & Project Structure

### 1. **Entity Layer** - [src/Entity/User.php](src/Entity/User.php)

The User entity maps to the existing `user` table with the following fields:

**Database Mapping:**
```
- id (INT, PRIMARY KEY)
- email (VARCHAR 180, UNIQUE)
- roles (JSON array of role strings)
- password (VARCHAR hashed)
- isVerified (BOOLEAN)
- firstName (VARCHAR 100, nullable)
- lastName (VARCHAR 100, nullable)
- statutCompte (VARCHAR 50, nullable) - status field
- dateCreation (DATETIME, nullable)
```

**New Helper Methods:**
- `getInitials()`: Returns user initials (e.g., "JD" for John Doe) for avatar generation
- `getFullName()`: Returns formatted full name or email as fallback

### 2. **Form Layer** - [src/Form/UserType.php](src/Form/UserType.php)

**Form Type for User Editing:**
```php
- firstName (TextType) - Optional, max 100 chars
- lastName (TextType) - Optional, max 100 chars  
- email (EmailType) - Required, validated as unique via database constraint
- roles (ChoiceType) - Multiple select
  - ROLE_USER (User)
  - ROLE_EDITOR (Editor)
  - ROLE_ADMIN (Administrator)
- statutCompte (ChoiceType) - Account status
  - active (Active)
  - away (Away)
  - offline (Offline)
  - suspended (Suspended)
- isVerified (CheckboxType) - Email verification flag
```

**Validation Features:**
- Email uniqueness constraint
- CSRF token protection
- Field length validation
- Required field validation
- Bootstrap styling applied to all form controls

### 3. **Controller Layer** - [src/Controller/AdminUserController.php](src/Controller/AdminUserController.php)

**Routes & Actions:**

#### List Users: `/admin/users`
```
Route: /admin/users
Method: GET
Name: app_admin_user_index
Security: ROLE_ADMIN required (@IsGranted attribute)

Parameters:
- page (int, default: 1) - pagination page number
- sort (string, default: 'dateCreation') - sort field
- order (string, default: 'DESC') - ASC or DESC

Features:
- Pagination (10 items per page)
- Sorting by: firstName, lastName, email, roles, statutCompte, dateCreation
- SQL injection prevention via whitelist validation
- Response includes sortable header links
```

#### Edit User: `/admin/users/{id}/edit`
```
Route: /admin/users/{id}/edit
Methods: GET, POST
Name: app_admin_user_edit
Security: ROLE_ADMIN required

Features:
- Pre-loads user data into form
- Privilege escalation prevention:
  * Admin cannot remove their own ROLE_ADMIN
  * Detection by comparing app.user.id === user.id
- CSRF token validation
- Flash messages on success/failure (Symfony AbstractController)
```

#### Delete User: `/admin/users/{id}/delete`
```
Route: /admin/users/{id}/delete
Methods: POST, DELETE
Name: app_admin_user_delete
Security: ROLE_ADMIN required

Protection Mechanisms:
1. Self-deletion prevention - cannot delete own account
2. CSRF token validation - token format: 'delete{id}'
3. Confirmation modal on frontend with JS validation
4. Hard delete (no soft delete)
5. Flash message feedback
```

**Security Features:**
```php
// Privilege escalation check (edit)
if ($currentUser->getId() === $user->getId() && 
    !in_array('ROLE_ADMIN', $form->getData()->getRoles())) {
    $this->addFlash('error', 'You cannot remove your own admin role.');
}

// Self-deletion prevention (delete)
if ($currentUser->getId() === $user->getId()) {
    $this->addFlash('error', 'You cannot delete your own account.');
}

// CSRF protection (delete)
if (!$this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
    $this->addFlash('error', 'Invalid CSRF token.');
}
```

### 4. **View Layer - Twig Templates**

#### [templates/admin/base.html.twig](templates/admin/base.html.twig) - Master Layout
**Features:**
- Responsive two-column layout (sidebar + main content)
- Fixed sidebar navigation (250px width)
- Top bar with page title and current user profile
- Flash message rendering
- Bootstrap 5.3 + Bootstrap Icons
- Mobile-responsive sidebar (toggleable via CSS)
- Unified color scheme:
  - Primary: #10b981 (emerald green)
  - Secondary: #f59e0b (amber)
  - Danger: #ef4444 (red)
  - Dark BG: #f3f4f6 (light gray)

**Components:**
- Navigation sidebar with Dashboard/Users/Settings links
- User profile badge with initials avatar
- Alert/flash message container
- Block extension points for page title and content
- Bootstrap JS included for interactive components

#### [templates/admin/user/index.html.twig](templates/admin/user/index.html.twig) - User List
**Features:**
- Responsive data table rendering
- Sortable column headers (click to toggle ASC/DESC)
- Pagination controls with smart ellipsis
- User avatar with initials
- Role-based badges with color coding:
  - Administrator → Blue (#dbeafe)
  - Editor → Yellow (#fef08a)
  - User → Indigo (#e0e7ff)
- Status indicator badges:
  - Active → Green (#d1fae5)
  - Away → Yellow (#fef3c7)
  - Offline → Gray (#e5e7eb)
  - Suspended → Red (#fee2e2)
- Action buttons: Edit (indigo) | Delete (red)
- Delete confirmation modal via `confirmDelete(name)` JavaScript function
- Table hover effects for better UX
- Empty state message when no users found

#### [templates/admin/user/edit.html.twig](templates/admin/user/edit.html.twig) - User Editor
**Features:**
- Two-column layout: form + sidebar
- Form fields with validation error messages
- User profile card showing:
  - Avatar with initials
  - Full name
  - Email address
- Account details panel:
  - Member since (formatted date)
  - Email verified status (badge)
  - Current highest role
- Save/Cancel buttons with icons
- Back link to user list with navigation arrow
- Form helper rendering with bootstrap styling

---

## 🔐 Security Implementation

### 1. **Authorization**
```php
#[IsGranted('ROLE_ADMIN')]  // On all user management routes
```
- Routes are protected with Symfony's `#[IsGranted]` attribute
- Only users with `ROLE_ADMIN` can access user management
- Authentication already exists (assumed)

### 2. **CSRF Protection**
- Edit form: Built-in Symfony form CSRF token
- Delete form: Manual token validation with signature `delete{id}`
```php
$this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))
```

### 3. **Privilege Escalation Prevention**

**Edit Scenario:**
```
Admin (ID: 1) edits User (ID: 2)
- Can change any field ✓

Admin (ID: 1) edits themselves (ID: 1)  
- CANNOT remove ROLE_ADMIN from own roles ✗
- Attempting to do so triggers error flash message
```

**Delete Scenario:**
```
Cannot delete currently authenticated user ✗
Can delete any other user ✓
```

### 4. **Input Validation**

**Sorting Parameters (SQL Injection Prevention):**
```php
$validSortFields = ['firstName', 'lastName', 'email', 'roles', 'statutCompte', 'dateCreation'];
if (!in_array($sort, $validSortFields)) {
    $sort = 'dateCreation'; // Reset to default
}
```

**Email Validation:**
```php
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
```

---

## 📊 Database Considerations

### Existing Table Structure (No Changes)

The implementation uses the existing `user` table as-is:

**Column Mapping:**
```
Database Column  | Entity Property | Type
==============================================
id              | id              | int
email           | email           | string
roles           | roles           | json (array)
password        | password        | string
is_verified     | isVerified      | bool
first_name      | firstName       | string
last_name       | lastName        | string
statut_compte   | statutCompte    | string
date_creation   | dateCreation    | DateTime
```

**Notes:**
- Database column names follow snake_case convention
- Doctrine ORM handles automatic camelCase-to-snake_case conversion via `#[ORM\Column(...)]`
- No migrations needed - existing schema is used

---

## 🎨 UI/UX Implementation

### Status Badge Styling

**HTML Implementation:**
```twig
<span class="badge bg-success">● Active</span>
<span class="badge bg-warning">● Away</span>
<span class="badge bg-secondary">● Offline</span>
<span class="badge bg-danger">● Suspended</span>
```

**CSS (Bootstrap 5 + Custom):**
```css
.bg-success  { background-color: #d1fae5; color: #065f46; }
.bg-warning  { background-color: #fef3c7; color: #92400e; }
.bg-secondary{ background-color: #e5e7eb; color: #4b5563; }
.bg-danger   { background-color: #fee2e2; color: #7f1d1d; }
```

### Avatar System

**User Initials:**
```php
public function getInitials(): string
{
    $first = $this->firstName ? substr($this->firstName, 0, 1) : '';
    $last = $this->lastName ? substr($this->lastName, 0, 1) : '';
    return strtoupper($first . $last) ?: substr($this->email, 0, 2);
}
```

**Rendering in Twig:**
```twig
<div class="avatar">{{ user.initials }}</div>
```

**CSS Styling:**
```css
.avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    color: white;
    background: linear-gradient(135deg, #10b981, #f59e0b);
}
```

---

## 🔄 Routing Configuration

Routes are defined using **PHP Attributes** (Symfony 6 standard):

```php
#[Route('/admin/users')]
#[IsGranted('ROLE_ADMIN')]
final class AdminUserController extends AbstractController
{
    #[Route('', name: 'app_admin_user_index', methods: ['GET'])]
    public function index() { ... }

    #[Route('/{id}/edit', name: 'app_admin_user_edit', methods: ['GET', 'POST'])]
    public function edit(User $user) { ... }

    #[Route('/{id}/delete', name: 'app_admin_user_delete', methods: ['POST', 'DELETE'])]
    public function delete(User $user) { ... }
}
```

**Generated Routes:**
```
GET    /admin/users              → app_admin_user_index
GET    /admin/users/{id}/edit    → app_admin_user_edit
POST   /admin/users/{id}/edit    → app_admin_user_edit (form submission)
POST   /admin/users/{id}/delete  → app_admin_user_delete
DELETE /admin/users/{id}/delete  → app_admin_user_delete
```

No additional route configuration needed - routes are auto-loaded from Controller directory.

---

## 🧪 Testing Scenarios

### Happy Path

**1. View Users List**
```
GET /admin/users
→ Page 1 displayed with 10 items
→ Sortable headers visible
→ Edit/Delete buttons functional
```

**2. Edit User**
```
GET /admin/users/2/edit
→ Form populated with user data
→ Modify firstName/email
POST /admin/users/2/edit (valid form)
→ Redirect to list
→ Flash: "User "John Doe" has been updated successfully."
```

**3. Delete User**
```
POST /admin/users/3/delete (with valid CSRF token)
→ User removed from database
→ Redirect to list
→ Flash: "User "Jane Smith" has been deleted successfully."
```

### Security Tests

**1. Privilege Escalation (Edit)**
```
Admin user (ID: 1, roles: [ROLE_ADMIN]) goes to /admin/users/1/edit
Changes roles to [ROLE_USER] (removing ROLE_ADMIN)
POST form → ERROR
Flash: "You cannot remove your own admin role."
Redirect: /admin/users/1/edit
Data NOT changed ✓
```

**2. Self-Deletion Prevention**
```
Admin user (ID: 1) POST /admin/users/1/delete
ERROR
Flash: "You cannot delete your own account."
Redirect: /admin/users
Account NOT deleted ✓
```

**3. CSRF Token Validation**
```
POST /admin/users/3/delete (with invalid/missing token)
ERROR
Flash: "Invalid CSRF token."
User NOT deleted ✓
```

**4. Authorization**
```
Non-admin user tries GET /admin/users
→ 403 Forbidden (Symfony security exception)
Only ROLE_ADMIN can access ✓
```

---

## 📦 Dependencies

**Already included in composer.json:**
- `symfony/framework-bundle` ^6.4 - Core framework
- `symfony/form` ^6.4 - Form handling
- `symfony/security-bundle` ^6.4 - Authorization
- `doctrine/orm` ^3.6 - ORM
- `symfony/validator` ^6.4 - Validation
- `twig/twig` ^3.0 - Template engine

**Frontend:**
- Bootstrap 5.3 (CDN)
- Bootstrap Icons 1.11 (CDN)
- No JavaScript framework (vanilla JS for confirmations)

---

## 🛠️ Implementation Files Summary

| File | Purpose | Lines |
|------|---------|-------|
| `src/Entity/User.php` | Entity with helper methods | +20 |
| `src/Form/UserType.php` | Form class for CRUD | 120 |
| `src/Controller/AdminUserController.php` | CRUD operations & authorization | 160 |
| `src/Controller/AdminController.php` | Dashboard index (modified) | 18 |
| `templates/admin/base.html.twig` | Master layout | 250 |
| `templates/admin/index.html.twig` | Dashboard (modified) | 120 |
| `templates/admin/user/index.html.twig` | User list | 180 |
| `templates/admin/user/edit.html.twig` | User editor | 160 |

---

## ✅ Checklist for Deployment

- [x] User entity extends with initials & fullName methods
- [x] UserType form created with proper validation
- [x] AdminUserController with all CRUD actions
- [x] Routes configured with attributes
- [x] CSRF protection on edit & delete
- [x] Privilege escalation prevention implemented
- [x] Status badge system with color coding
- [x] Pagination with smart controls
- [x] Sortable column headers
- [x] User avatar generation from initials
- [x] Bootstrap 5.3 responsive design
- [x] Admin authorization (@IsGranted)
- [x] Flash message feedback
- [x] Error handling & validation messages
- [x] Professional UI matching GlassDash template

---

## 🚀 Usage

### Access the Admin Panel

1. **Navigate to admin Users page:**
   ```
   https://your-domain/admin/users
   ```

2. **View user list with sorting:**
   ```
   Click column headers to sort by name, email, role, status, or date
   Use pagination controls to browse pages
   ```

3. **Edit a user:**
   ```
   Click "Edit" button → Modify fields → Save Changes
   ```

4. **Delete a user:**
   ```
   Click "Delete" button → Confirm in modal → User removed
   ```

### Role Management

- **ROLE_USER**: Default role, limited permissions
- **ROLE_EDITOR**: Can edit content
- **ROLE_ADMIN**: Full admin access (this module)

---

## 📝 Notes

- No soft delete implemented (as specified)
- Authentication is assumed to exist
- Database was not recreated
- API Platform not used
- No JavaScript frameworks (vanilla JS only)
- All code follows Symfony 6.4 best practices
- Production-ready code with no placeholders

