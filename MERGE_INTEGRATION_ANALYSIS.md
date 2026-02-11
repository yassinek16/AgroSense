# Merge Integration Analysis - Connection Issues & Required Changes

## 🔴 CRITICAL ISSUES (MUST FIX)

### 1. **MISSING: User-Commande Relationship**

**Location**: [src/Entity/Commande.php](src/Entity/Commande.php)

**Problem**: The `Commande` entity has NO relationship to `User`. When customers checkout, the order is created but is NOT linked to who placed it.

**Impact**:

- Cannot track which customer made which order
- Admin cannot see customer order history
- Users cannot view their own orders
- Checkout in FrontController creates orders without user context

**Solution Required**:

```php
// Add to Commande.php
#[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'commandes')]
#[ORM\JoinColumn(nullable: false)]
private ?User $user = null;

// Add getter/setter for $user
public function getUser(): ?User { return $this->user; }
public function setUser(?User $user): static { $this->user = $user; return $this; }
```

**Additional Action in User.php**:

```php
// Add Collection in User entity
#[ORM\OneToMany(targetEntity: Commande::class, mappedBy: 'user', orphanRemoval: true)]
private Collection $commandes;

// In __construct()
$this->commandes = new ArrayCollection();

// Add getter/remover
public function getCommandes(): Collection { return $this->commandes; }
public function addCommande(Commande $commande): static { ... }
public function removeCommande(Commande $commande): static { ... }
```

**Update FrontController checkout()**: Must set the user on the Commande:

```php
$commande->setUser($this->getUser()); // Add this line
```

---

### 2. **Inconsistent Dashboard Structure**

**Locations**:

- [src/Controller/AdminController.php](src/Controller/AdminController.php) (Events/Tickets focused)
- [src/Controller/DashboardController.php](src/Controller/DashboardController.php) (Products/Orders focused)

**Problem**: TWO SEPARATE dashboard systems:

- `AdminController@dashboard` → Events, Tickets, Users, Reports
- `DashboardController@dashboard` → Products, Orders, Sales

Both route to `/admin/dashboard` but serve different purposes. Creates confusion and duplicate routes.

**Solution Required**:

1. **Merge into unified AdminController** with tabs/sections:
   - Overview (key metrics from both)
   - Products & Inventory Management
   - Orders/Commandes Management
   - Events Management
   - Tickets Management
   - User Management
   - Reports

2. **Remove DashboardController** entirely or keep it as a redirect

3. **Update routes** to disambiguate:
   - `/admin` → main dashboard (overview)
   - `/admin/produits` → products section
   - `/admin/commandes` → orders section
   - `/admin/evenements` → events section

---

### 3. **Checkout Flow Missing User Assignment**

**Location**: [src/Controller/FrontController.php](src/Controller/FrontController.php) - `checkout()` method (line ~127)

**Problem**: When cart is checked out, the `Commande` is created WITHOUT setting the user who placed it.

**Current Code**:

```php
$commande = new \App\Entity\Commande();
$commande->setReference('CMD-' . uniqid());
$commande->setDateCommande(new \DateTime());
$commande->setTotal($cartService->getTotal());
$commande->setCreatedAt(new \DateTimeImmutable());
$commande->setStatut('Confirmée');
// ❌ MISSING: $commande->setUser($this->getUser());
```

**Fix**: Add `$commande->setUser($this->getUser());` before persist

---

## 🟡 IMPORTANT ISSUES (SHOULD FIX)

### 4. **Role-Based Access Control Not Enforced**

**Location**: [config/packages/security.yaml](config/packages/security.yaml) - Line 40

**Problem**: Access control rules are commented out:

```yaml
# access_control:
#     # - { path: ^/admin, roles: ROLE_ADMIN }
#     # - { path: ^/profile, roles: ROLE_USER }
```

**Impact**:

- Any authenticated user can access `/admin/*` routes (should be ROLE_ADMIN only)
- No protection on user-specific routes

**Solution**: Uncomment and configure:

```yaml
access_control:
  - { path: ^/admin, roles: ROLE_ADMIN }
  - { path: ^/cart, roles: ROLE_USER }
  - { path: ^/checkout, roles: ROLE_USER }
  - { path: ^/ticket, roles: ROLE_USER }
  - { path: ^/evenement/new, roles: ROLE_ADMIN }
  - { path: ^/evenement/edit, roles: ROLE_ADMIN }
```

---

### 5. **Missing Route Prefixes & Security Checks**

**Locations**: Multiple controllers

**Problems**:

- `CommandeController` → `/admin/commande` (good)
- `ProduitController` → `/admin/produit` (good)
- `EvenementController` → `/evenement` (NO admin prefix - public can create events?)
- `TicketController` → `/ticket` (good, has @IsGranted)
- `FrontController` → `/` and `/cart` (good, public/user routes)

**Issue with EvenementController**:

- Has `#[IsGranted('ROLE_ADMIN')]` on individual methods ✓
- But route `/evenement/new` is accessible to all users initially
- Should have route-level security

**Recommendation**: Add security checks:

```php
#[Route('/evenement')]
#[IsGranted('ROLE_USER')] // Public can view, but check individual methods
class EvenementController ...
```

---

### 6. **Missing: User Profile/Account Views**

**Problem**: Users cannot:

- View their own order history
- Manage their profile
- View their past tickets
- Cancel/modify orders

**Missing Routes & Controllers**:

- `/user/profile` - View/edit profile
- `/user/orders` - View user's orders (commandes)
- `/user/orders/{id}` - View specific order details
- Already exists: `/ticket/my-tickets`

**Solution Required**: Create UserProfileController or extend FrontController with:

```php
#[Route('/profile', name: 'user_profile')]
#[IsGranted('ROLE_USER')]
public function profile(User $user): Response { ... }

#[Route('/my-orders', name: 'user_orders')]
#[IsGranted('ROLE_USER')]
public function myOrders(): Response { ... }

#[Route('/my-orders/{id}', name: 'user_order_details')]
#[IsGranted('ROLE_USER')]
public function orderDetails(Commande $commande): Response { ... }
```

---

### 7. **Navigation Structure Inconsistent**

**Locations**:

- [templates/back/back_base.html.twig](templates/back/back_base.html.twig)
- [templates/front/front_base.html.twig](templates/front/front_base.html.twig)

**Problem**: Admin sidebar has:

- Produits → `/admin/produit`
- Commandes → `/admin/commande`
- Events section? (might be missing in back_base)
- Dashboard links to BOTH AdminController and DashboardController

**Solution**:

- Consolidate sidebar navigation
- Add proper event management section if using AdminController's event dashboard
- Link user dashboard to `/user/my-orders` and `/user/profile`
- Add "Mes Événements" for event organizers (if they can create events)

---

### 8. **Missing Redirect on Successful Registration**

**Location**: [src/Controller/RegistrationController.php](src/Controller/RegistrationController.php) - Line 31

**Current Code**:

```php
return $this->redirectToRoute('evenement_index');
```

**Problem**: After registration, user is redirected to events (evenement_index). Should probably redirect to:

- Front page (`app_front_index`) - for customers
- OR ask which type of user they are
- OR auto-login and redirect to `/`

**Recommendation**:

```php
return $this->redirectToRoute('app_front_index');
// OR implement auto-login and redirect to dashboard
```

---

### 9. **Missing CommandeForm Relationship Configuration**

**Location**: [src/Form/CommandeType.php](src/Form/CommandeType.php)

**Problem**: The form likely doesn't include the `User` field since it wasn't in the entity. Once User relationship is added, the form needs updating or explicit user assignment in the controller.

**Likely Current Code**:

```php
// Missing field - won't show user
class CommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reference', TextType::class)
            ->add('dateCommande', DateTimeType::class)
            // ... etc
            // ❌ NO USER FIELD
    }
}
```

**Fix**: Either:

1. **For admin creation**: Add UserChoiceType field if admin can create orders
2. **For checkout**: Don't include in form, set in controller with `$commande->setUser($this->getUser())`

---

## 🟢 NICE-TO-HAVE IMPROVEMENTS

### 10. **Missing User Role Logic**

While roles exist (ROLE_ADMIN, ROLE_USER), there's no logic for:

- Event organizers having special permissions
- Vendors/sellers managing products
- Different user tiers

**Current**: Only ROLE_ADMIN and ROLE_USER. Consider adding:

```php
// If multiple user types needed:
ROLE_ORGANIZER - Can create/manage events and sell tickets
ROLE_VENDOR - Can manage products
ROLE_CUSTOMER - Regular users
```

---

### 11. **Validation & Constraints Inconsistency**

- `User.php` has good validation ✓
- `Produit.php` has good validation ✓
- `Commande.php` has validation ✓
- `Ticket.php` has some validation
- `Evenement.php` needs more validation (currently missing many constraints)

---

### 12. **Error Handling & User Feedback**

- Check stock availability in checkout ✓
- But no feedback if event is full
- No email confirmations for orders/tickets
- No order status notifications

---

## 📋 IMPLEMENTATION CHECKLIST

### Phase 1: Critical Fixes (DO FIRST)

- [ ] Add `User` relationship to `Commande` entity
- [ ] Add `$commandes` collection to `User` entity
- [ ] Create database migration for new relationship
- [ ] Update `FrontController::checkout()` to set user
- [ ] Update `CommandeType` form if needed
- [ ] Enable `access_control` in security.yaml

### Phase 2: Dashboard Consolidation

- [ ] Decide: Keep AdminController or DashboardController as main?
- [ ] Merge statistics/views into unified dashboard
- [ ] Update route structure for clarity
- [ ] Update back_base.html.twig navigation
- [ ] Redirect old routes to new ones

### Phase 3: User Features

- [ ] Create user profile controller/views
- [ ] Add my-orders route and view
- [ ] Add order detail view
- [ ] Update front_base.html.twig with user menu links

### Phase 4: Security Hardening

- [ ] Review all @IsGranted decorators
- [ ] Ensure route prefixes match security config
- [ ] Add Voter for order ownership (only user can see own orders)
- [ ] Test role-based access

### Phase 5: Polish

- [ ] Fix registration redirect
- [ ] Add email notifications
- [ ] Add validation to Evenement
- [ ] Test complete checkout flow cart → order → confirmation

---

## 🔗 Entity Relationship Summary (After Fixes)

```
User (1) ─────── (N) Commande
  ├─ (1) ─────── (N) Ticket
  ├─ (1) ─────── (N) Evenement (as organizer)

Evenement (1) ─────── (N) Ticket

Commande (1) ─────── (N) LigneCommande

Produit (1) ─────── (N) LigneCommande
```

---

## 🗺️ Route Map After Integration

```
PUBLIC ROUTES:
/                           → Front page (products)
/cart                       → Shopping cart
/checkout                   → Checkout (requires ROLE_USER)
/evenement                  → Event listings
/evenement/{id}             → Event details
/login                      → Login
/register                   → Registration

USER ROUTES (ROLE_USER):
/profile                    → User profile
/my-orders                  → My orders/commandes
/my-orders/{id}             → Order details
/ticket/my-tickets          → My tickets
/ticket/purchase/{id}       → Buy event ticket
/ticket/{id}                → Ticket details

ADMIN ROUTES (ROLE_ADMIN):
/admin                      → Dashboard (overview)
/admin/produit              → Products management
/admin/produit/new          → Create product
/admin/produit/{id}/edit    → Edit product
/admin/commande             → Orders management
/admin/commande/{id}        → Order details
/admin/evenement            → Events management
/admin/evenement/new        → Create event
/admin/users                → Users management
/admin/tickets              → Tickets management
/admin/reports              → Reports
```

---

## 📝 Notes

- Migration files already exist: Version20260205141242.php, Version20260207212948.php, Version20260209092954.php
- Need new migration for User-Commande relationship
- Test both branches' existing functionality after changes
