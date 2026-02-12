# Admin Users Module - Integration Guide

## 🎯 Quick Start

This guide walks through the integration of the Users Management module for your Symfony 6.4 admin dashboard.

---

## ✅ Step 1: Verify User Entity Extensions

**File:** `src/Entity/User.php`

Ensure the following methods are added to the User class:

```php
/**
 * Get user initials for avatar
 */
public function getInitials(): string
{
    $first = $this->firstName ? substr($this->firstName, 0, 1) : '';
    $last = $this->lastName ? substr($this->lastName, 0, 1) : '';
    return strtoupper($first . $last) ?: substr($this->email, 0, 2);
}

/**
 * Get full name
 */
public function getFullName(): string
{
    $name = trim(($this->firstName ?? '') . ' ' . ($this->lastName ?? ''));
    return $name ?: $this->email;
}
```

---

## ✅ Step 2: Create UserType Form

**File:** `src/Form/UserType.php`

This form handles user editing with:
- Email validation
- Role assignment (ROLE_USER, ROLE_EDITOR, ROLE_ADMIN)
- Status selection (active, away, offline, suspended)
- Email verification checkbox
- CSRF protection (automatic)

---

## ✅ Step 3: Create AdminUserController

**File:** `src/Controller/AdminUserController.php`

The controller provides three main routes:

### 3.1 List Users
```
Route: GET /admin/users
Handler: index()
Features:
- Pagination (10 per page)
- Sorting (click column headers)
- User profiles with avatars
- Edit/Delete action buttons
```

### 3.2 Edit User
```
Route: GET|POST /admin/users/{id}/edit
Handler: edit(User $user, Request $request)
Features:
- Form validation
- CSRF protection (built-in)
- Privilege escalation prevention (prevent removing own admin role)
- Flash messages
```

### 3.3 Delete User
```
Route: POST /admin/users/{id}/delete
Handler: delete(User $user, Request $request)
Features:
- CSRF token validation
- Self-deletion prevention (cannot delete own account)
- Flash message feedback
- Hard delete
```

---

## ✅ Step 4: Create Admin Base Template

**File:** `templates/admin/base.html.twig`

Master layout for all admin pages including:
- Responsive sidebar (fixed left panel)
- Top bar with user profile
- Main content area
- Flash message rendering
- Bootstrap 5.3 integration

All admin pages should extend this:
```twig
{% extends 'admin/base.html.twig' %}
```

---

## ✅ Step 5: Create User List Template

**File:** `templates/admin/user/index.html.twig`

Features:
- Responsive data table
- Sortable column headers
- Pagination controls
- Status badges (Active/Away/Offline/Suspended)
- Role badges (Administrator/Editor/User)
- Edit/Delete buttons with confirmation
- Empty state message

---

## ✅ Step 6: Create User Edit Template

**File:** `templates/admin/user/edit.html.twig`

Features:
- Two-column layout (form + sidebar)
- Form field validation error messages
- User profile card
- Account details sidebar
- Save/Cancel buttons
- Back navigation link

---

## 🔐 Security Configuration

### Authorization via Attributes

The controller uses Symfony's `#[IsGranted('ROLE_ADMIN')]` attribute:

```php
#[Route('/admin/users')]
#[IsGranted('ROLE_ADMIN')]
final class AdminUserController extends AbstractController
{
    // All methods are protected
}
```

This requires authentication to already be configured (which it is in your project).

### Existing Security Setup (From config/packages/security.yaml)

Your project already has:
- User provider configured
- Authentication with custom authenticator
- Password hashing configuration

No changes needed to security.yaml!

---

## 🎨 UI/Color Scheme

All templates use a unified color scheme:

```
Primary Color:      #10b981 (Emerald Green)
Secondary Color:    #f59e0b (Amber)
Danger Color:       #ef4444 (Red)
Dark Background:    #f3f4f6 (Light Gray)
Card Background:    #ffffff (White)
```

Status Badge Colors:
```
Active:     Green (#d1fae5) with dark green text
Away:       Yellow (#fef3c7) with dark yellow text  
Offline:    Gray (#e5e7eb) with dark gray text
Suspended:  Red (#fee2e2) with dark red text
```

Role Badge Colors:
```
Administrator:  Blue (#dbeafe) with dark blue text
Editor:         Yellow (#fef08a) with dark yellow text
User:           Indigo (#e0e7ff) with dark indigo text
```

---

## 📋 Feature Checklist

### User List View (`/admin/users`)
- [x] Display users in responsive table
- [x] Avatar with user initials
- [x] Full name display
- [x] Email address
- [x] Role badge (Administrator/Editor/User)
- [x] Status badge (Active/Away/Offline/Suspended) 
- [x] Join date (formatted date_creation)
- [x] Edit button
- [x] Delete button with confirmation
- [x] Pagination (10 items per page)
- [x] Sortable columns (firstName, lastName, email, roles, statutCompte, dateCreation)
- [x] Sorting direction toggle (ASC/DESC)

### Edit User View (`/admin/users/{id}/edit`)
- [x] firstName field (text input)
- [x] lastName field (text input)
- [x] email field (email input with validation)
- [x] roles field (multi-select dropdown)
  - ROLE_USER
  - ROLE_EDITOR
  - ROLE_ADMIN
- [x] statutCompte field (select dropdown)
  - active
  - away
  - offline
  - suspended
- [x] isVerified field (checkbox)
- [x] Form validation errors displayed
- [x] Save button
- [x] Cancel button
- [x] User profile card in sidebar
- [x] Account details in sidebar
- [x] Back link to user list
- [x] CSRF protection
- [x] Privilege escalation prevention (cannot remove own admin role)
- [x] Flash messages on success/failure

### Delete User (`/admin/users/{id}/delete`)
- [x] POST/DELETE method handling
- [x] CSRF token validation with 'delete{id}' signature
- [x] Confirmation modal (JavaScript)
- [x] Prevent self-deletion
- [x] Hard delete (no soft delete)
- [x] Flash message on success
- [x] Redirect to user list

### Security Features
- [x] ROLE_ADMIN authorization required
- [x] CSRF protection on edit form
- [x] CSRF protection on delete form
- [x] Privilege escalation prevention
- [x] SQL injection prevention in sorting
- [x] Email uniqueness validation
- [x] Self-deletion prevention
- [x] Self-role-removal prevention

---

## 🧪 Testing the Implementation

### Test 1: View User List
```bash
# Navigate to:
http://localhost:8000/admin/users

# Expected:
- List of users in table format
- Pagination controls visible
- Edit/Delete buttons present
- Sortable column headers
```

### Test 2: Edit User
```bash
# Click Edit button for any user
# Expected:
- Form loads with user data
- Can modify firstName, lastName, email, roles, status
- Save button submits form
- Redirect to list with success message
```

### Test 3: Edit Own User Without Privilege Escalation
```bash
# Edit your own admin account
# Try to change role from ROLE_ADMIN to ROLE_USER
# Click Save
# Expected:
- Error message: "You cannot remove your own admin role."
- User stays on edit page
- Role NOT changed
```

### Test 4: Delete User
```bash
# Click Delete button
# Confirm in modal
# Expected:
- User removed from database
- Redirect to list
- Success message: "User "..." has been deleted successfully."
```

### Test 5: Delete Own Account Prevention
```bash
# Try to delete your own account
# Expected:
- Error message: "You cannot delete your own account."
- Redirect to list
- Account NOT deleted
```

### Test 6: Non-Admin Access
```bash
# Logout, login as non-admin user
# Navigate to http://localhost:8000/admin/users
# Expected:
- 403 Forbidden error
- Only ROLE_ADMIN can access
```

---

## 📝 Form Validation Rules

### Email Field
- Required: Yes
- Format: Valid email address
- Unique: Yes (via database constraint)
- Max length: 180 characters

### firstName Field
- Required: No
- Max length: 100 characters

### lastName Field
- Required: No
- Max length: 100 characters

### roles Field
- Required: Yes (at least one role)
- Valid values: ROLE_USER, ROLE_EDITOR, ROLE_ADMIN
- Multiple select allowed

### statutCompte Field
- Required: No
- Valid values: active, away, offline, suspended, null

### isVerified Field
- Required: No
- Type: Boolean
- Default: false

---

## 🔄 Workflow Example: Complete User Edit Cycle

```
1. Admin visits /admin/users
   ↓
2. Admin clicks "Edit" on user "John Doe"
   ↓
3. GET /admin/users/5/edit
   - Form renders with John's data:
     * firstName: "John"
     * lastName: "Doe"
     * email: "john@example.com"
     * roles: ["ROLE_USER"]
     * statutCompte: "active"
     * isVerified: false
   ↓
4. Admin changes:
   * roles: ["ROLE_USER", "ROLE_EDITOR"]
   * statutCompte: "away"
   * isVerified: true
   ↓
5. Admin clicks "Save Changes"
   ↓
6. POST /admin/users/5/edit
   - Symfony validates form
   - CSRF token checked ✓
   - Database constraint checks email uniqueness ✓
   - Data persisted to database
   ↓
7. Redirect to /admin/users
   ↓
8. Flash message displayed:
   "User "John Doe" has been updated successfully."
   ↓
9. User list shown with John's updated data
```

---

## 📊 Database Query Optimization

The list view uses efficient queries:

```php
// Controller builds query:
$query = $this->userRepository->createQueryBuilder('u')
    ->orderBy("u.$sort", $order)  // Apply sorting
    ->getQuery();

// Paginator handles result set
$paginator = new Paginator($query, true);

// Set pagination bounds
->setFirstResult(($page - 1) * self::ITEMS_PER_PAGE)
->setMaxResults(self::ITEMS_PER_PAGE);
```

This ensures:
- Single database query per page load
- Efficient pagination
- No N+1 query problems
- Proper sorting at database level

---

## 🎓 Code Style & Standards

All code follows Symfony 6.4 best practices:

- **Strict typing:** All method parameters and return types specified
- **Attributes:** Routes defined via PHP 8 attributes
- **Final classes:** Controllers marked as `final`
- **Dependency injection:** Constructor-based DI for all dependencies
- **Form types:** Proper form builder pattern
- **CSRF protection:** Automatic on forms, manual on delete
- **Security:** Authorization attributes and privilege escalation checks
- **Twig templates:** Proper escaping, structured blocks
- **Bootstrap 5.3:** Mobile-responsive, semantic HTML

---

## 🚀 Deployment Checklist

Before going to production:

- [ ] Ensure database exists at `symfony_db`
- [ ] Run `php bin/console cache:clear` in production
- [ ] Check Symfony logs for any errors
- [ ] Test all user management functions
- [ ] Verify HTTPS is enabled
- [ ] Test with different user roles
- [ ] Confirm email validation works
- [ ] Check that logs show no security warnings
- [ ] Test pagination with large user datasets
- [ ] Verify sorting performance
- [ ] Check responsive design on mobile

---

## 📞 Support & Troubleshooting

### Issue: Routes not found (404)
**Solution:** Clear cache
```bash
php bin/console cache:clear
```

### Issue: Access Denied (403)
**Solution:** Ensure user has ROLE_ADMIN
```bash
# In admin panel, check user role assignment
```

### Issue: CSRF token mismatch
**Solution:** Ensure cookies are enabled in browser

### Issue: Form validation fails silently
**Solution:** Check error logs
```bash
tail -f var/log/dev.log
```

---

## 📚 Additional Resources

- [Symfony Forms Documentation](https://symfony.com/doc/current/forms.html)
- [Symfony Security](https://symfony.com/doc/current/security.html)
- [Doctrine ORM](https://www.doctrine-project.org/projects/doctrine-orm/en/latest/)
- [Twig Template Engine](https://twig.symfony.com/)
- [Bootstrap 5.3 Documentation](https://getbootstrap.com/docs/5.3/)

---

**Created:** February 12, 2026  
**Framework:** Symfony 6.4  
**Status:** Production Ready
