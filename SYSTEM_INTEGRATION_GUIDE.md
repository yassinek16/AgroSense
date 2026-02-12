# AgroSense System - Quick Reference Guide

## System Overview

The AgroSense platform integrates three main interfaces:

### 1. **Front Office** (Public)
- URL: `http://localhost:8000/`
- Features:
  - Event browsing & ticket purchase
  - Product shopping & checkout
  - User profile & order management
  - Event-specific dashboard
- **Color Scheme:** Green gradient navbar with white background

### 2. **Agriculteur (Farmer) Interface** (User Only)
- URL: `http://localhost:8000/agriculteur/dashboard`
- Access: Requires user login
- Features:
  - Personal dashboard
  - Manage multiple greenhouses (Serres)
  - Organize zones within greenhouses
  - Track farm operations
  - View farming statistics
- **Navigation:** Sidebar menu with green background
- **Target Users:** Farmers, agricultural operators

### 3. **Admin Interface** (Admin Only)
- URL: `http://localhost:8000/admin/`
- Access: Requires ROLE_ADMIN
- Features:
  - System dashboard & statistics
  - Greenhouse supervision
  - Zone management & monitoring
  - User management
  - Event & ticket administration
  - Report generation
  - System settings
- **Navigation:** Sidebar menu with dark green background
- **Target Users:** System administrators

---

## URL Routes Reference

### Authentication
```
GET  /login                 → Login page
POST /login                 → Process login
GET  /logout                → Logout (redirect to home)
GET  /register              → Registration page
POST /register              → Create new account
```

### Front Office - Public
```
GET  /                      → Homepage
GET  /evenements            → Events list
GET  /evenements/{id}       → Event details
GET  /panier                → Shopping cart
GET  /checkout              → Checkout process
GET  /profile               → User profile
GET  /my-orders             → Order history
```

### Agriculteur - Farmer Dashboard
```
GET  /agriculteur/dashboard               → Farmer dashboard
GET  /agriculteur/serres                  → List my greenhouses
GET  /agriculteur/serre/new               → Create greenhouse
POST /agriculteur/serre/new               → Save greenhouse
GET  /agriculteur/serre/{id}              → View greenhouse
GET  /agriculteur/serre/{id}/edit         → Edit greenhouse
POST /agriculteur/serre/{id}/edit         → Save changes
POST /agriculteur/serre/{id}/delete       → Delete greenhouse

GET  /agriculteur/zones                   → List zones
GET  /agriculteur/zone/new                → Create zone
POST /agriculteur/zone/new                → Save zone
GET  /agriculteur/zone/{id}/edit          → Edit zone
POST /agriculteur/zone/{id}/edit          → Save zone
POST /agriculteur/zone/{id}/delete        → Delete zone
```

### Admin - Administration
```
GET  /admin/                              → Admin dashboard
GET  /admin/serres                        → Greenhouse supervision
GET  /admin/serres/{id}/details           → Greenhouse details
POST /admin/serres/{id}/toggle-state      → Enable/disable greenhouse
POST /admin/serres/{id}/delete            → Delete greenhouse

GET  /admin/zones                         → Zone management
GET  /admin/zones/{id}/details            → Zone details
POST /admin/zones/{id}/toggle-state       → Enable/disable zone
POST /admin/zones/{id}/delete             → Delete zone

GET  /admin/settings                      → System settings
POST /admin/settings/init                 → Initialize settings

GET  /admin/users                         → User management
GET  /admin/tickets                       → Ticket management
GET  /admin/reports                       → Reports & analytics
```

---

## Database Model

### Key Entities

**User**
- id, email, password, roles
- firstName, lastName, phone
- createdAt, isActive
- Relationships: tickets, orders, organized_events

**Serre (Greenhouse)**
- id, name, location, status
- dateMiseEnService
- Relationships: zones, activity_logs

**Zone**
- id, name, serre_id (FK)
- status
- Relationships: serre

**Evenement (Event)**
- id, titre, description, typeEvenement
- dateDebut, dateFin, capaciteMax
- Relationships: tickets, organisateur

**Ticket**
- id, eventId, userId
- prixPaye, dateAchat, isConfirmed
- Relationships: user, event

**Commande (Order)**
- id, userId, dateCommande, total
- Relationships: user, order_items

---

## Color Scheme

### Theme Colors
```
Primary Green:      #2c6e49  (Main elements, navbar)
Secondary Green:    #52b788  (Hover effects, accents)
Light Green:        #b7e4c7  (Background highlights)
Earth Brown:        #8b5a3c  (Secondary accents)
Wheat Gold:         #f4a261  (Highlights, special elements)
Dark Background:    #1a1a1a  (Footer)
```

### Bootstrap Color Classes
- `.btn-primary` → Primary green
- `.btn-success` → Secondary green
- `.badge.bg-primary` → Green badge
- `.text-success` → Green text

---

## Common Navigation Elements

### Front Office Navbar
```
[Logo] AgriCulture
  Accueil | Événements | Panier
  
  [Admin Dropdown - if admin]
    Gestion Boutique
    - Produits
    - Commandes
    
    Gestion Événements
    - Événements
    - Tickets
    
    Système
    - Utilisateurs
    - Rapports
    - Tableau de Bord
  
  [User Dropdown - if logged in]
    Mon Profil
    Mes Commandes
    Mes Tickets
    --------
    Déconnexion
  
  [Public links - if not logged in]
    Connexion | Inscription
```

### Admin Sidebar
- Dashboard
- Greenhouses (Supervision)
- Zones (Supervision)
- Settings
- User Management
- Reports
- Logout

### Agriculteur Sidebar
- Dashboard
- My Greenhouses (List, Add, Edit, Delete)
- My Zones (List, Add, Edit, Delete)
- Farm Statistics
- Profile Settings
- Logout

---

## Key Features by Interface

### For Farmers (Agriculteur)
✓ Create and manage multiple greenhouses
✓ Organize zones within each greenhouse
✓ Track farm operations
✓ View farming metrics & statistics
✓ Manage farm settings & preferences
✓ Access to public events and marketplace

### For Admins
✓ System-wide dashboard with KPIs
✓ Greenhouse and zone supervision
✓ Monitor all farm operations
✓ User management and roles
✓ Event & ticket administration
✓ Order processing & fulfillment
✓ System configuration & settings
✓ Generate reports & analytics

### For Regular Users
✓ Browse and purchase event tickets
✓ Shop for agricultural products
✓ Manage orders & delivery
✓ Access order history & invoices
✓ Update user profiles
✓ Track purchases & tickets

---

## Responsive Design

All interfaces are responsive and work on:
- ✓ Desktop (1200px+)
- ✓ Tablet (768px - 1199px)
- ✓ Mobile (< 768px)

Bootstrap 5 grid system is used throughout for responsive layouts.

---

## API Integration

### Weather Service (Configured)
- Configuration: `app.Service.WeatherService`
- API Key: `WEATHER_API_KEY` environment variable
- Purpose: Weather data for agricultural insights
- Status: Configured, ready for implementation

---

## Troubleshooting

### Routes Not Found
1. Clear cache: `php bin/console cache:clear`
2. Check routes: `php bin/console debug:router`
3. Verify user roles for admin access

### CSS/Styling Issues
1. Clear browser cache (Ctrl+Shift+Delete)
2. Verify Bootstrap CDN is loading
3. Check template extends correctly

### Database Issues
1. Run migrations: `php bin/console doctrine:migrations:migrate`
2. Check `.env` DATABASE_URL
3. Verify MySQL/MariaDB connection

### Authentication Issues
1. Check user role: `ROLE_USER` or `ROLE_ADMIN`
2. Verify session is active
3. Check security.yaml configuration

---

## Development Commands

```bash
# Clear application cache
php bin/console cache:clear

# View all routes
php bin/console debug:router

# Create database
php bin/console doctrine:database:create

# Run migrations
php bin/console doctrine:migrations:migrate

# Start development server
php bin/console serve

# Create new migration
php bin/console make:migration

# Dump autoloader
composer dump-autoload
```

---

## File Structure Summary

```
/templates
├── base.html.twig              (Front office base template)
├── admin/
│   ├── base_admin.html.twig    (Admin base template)
│   ├── dashboard.html.twig
│   ├── serres.html.twig
│   └── zones.html.twig
├── agriculteur/
│   ├── base.html.twig          (Farmer base template)
│   ├── dashboard.html.twig
│   ├── serres.html.twig
│   └── zones.html.twig
└── ... other templates

/src/Controller
├── FrontController.php
├── SecurityController.php
├── UserProfileController.php
├── Front/
│   └── AgriculteurController.php
└── Admin/
    ├── AdminDashboardController.php
    ├── AdminSerreController.php
    ├── AdminZoneController.php
    └── AdminSettingsController.php

/src/Entity
├── User.php
├── Serre.php
├── Zone.php
├── Evenement.php
├── Ticket.php
├── Commande.php
└── ... other entities
```

---

## Support & Documentation

For detailed information:
- See `MERGE_RESOLUTION_SUMMARY.md` for merge details
- Check `config/routes*.yaml` for route configuration
- Review controller files for specific endpoint logic
- Check `TESTING_GUIDE.md` for testing procedures

---

**Last Updated:** February 12, 2026  
**System Status:** ✅ Merge Complete, Ready for Testing
