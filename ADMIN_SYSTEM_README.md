# Structure du Système d'Administration GreenTech

## 📁 Arborescence des Fichiers

```
src/
├── Controller/
│   ├── Admin/
│   │   ├── AdminDashboardController.php      # Tableau de bord principal
│   │   ├── AdminSerreController.php          # Supervision des serres
│   │   ├── AdminZoneController.php           # Supervision des zones  
│   │   ├── AdminSettingsController.php       # Paramètres métier
│   │   ├── AdminController.php               # Contrôleur général (legacy)
│   │   ├── SecurityController.php            # Authentification
│   │   ├── UserController.php                # Gestion utilisateurs
│   │   └── MaintenanceController.php         # Maintenance système
│   └── Front/
│       └── DashboardController.php           # Dashboard agriculteur
├── Entity/
│   ├── User.php                              # Entité utilisateur
│   ├── Setting.php                           # Paramètres système
│   ├── ActivityLog.php                       # Journal d'activité
│   ├── Serre.php                             # Entité serre
│   └── Zone.php                              # Entité zone
├── Repository/
│   ├── UserRepository.php
│   ├── SettingRepository.php
│   ├── ActivityLogRepository.php
│   ├── SerreRepository.php
│   └── ZoneRepository.php
└── Service/
    └── WeatherService.php

templates/
├── admin/
│   ├── base_admin.html.twig                  # Layout principal admin
│   ├── dashboard.html.twig                  # Tableau de bord
│   ├── serres_supervision.html.twig          # Supervision serres
│   ├── zones_supervision.html.twig           # Supervision zones
│   ├── settings_metier.html.twig            # Paramètres métier
│   ├── login.html.twig                       # Connexion admin
│   ├── forgot_password.html.twig             # Mot de passe oublié
│   ├── users.html.twig                       # Gestion utilisateurs
│   ├── new_user.html.twig                    # Créer utilisateur
│   ├── edit_user.html.twig                   # Modifier utilisateur
│   ├── reset_password.html.twig              # Réinitialiser mot de passe
│   ├── serre_details.html.twig               # Détails serre
│   ├── zone_details.html.twig                # Détails zone
│   └── confirm_delete_*.html.twig            # Confirmations suppression
└── front/
    ├── base_front.html.twig                  # Layout front
    └── dashboard/
        └── index.html.twig                   # Dashboard agriculteur
```

## 🎨 Design et Style

### Palette de Couleurs (Vert Moderne)
- **Primary Green**: #2E7D32
- **Light Green**: #4CAF50  
- **Dark Green**: #1B5E20
- **Accent Green**: #81C784
- **Background Light**: #F1F8E9
- **Text Dark**: #263238
- **Text Light**: #616161

### Caractéristiques Design
- Design moderne et épuré avec thème vert
- Sidebar fixe avec navigation intuitive
- Cards avec ombres douces et bordures arrondies
- Badges colorés pour les états
- Tableaux responsives avec hover effects
- Animations subtiles et transitions fluides
- Mode sombre/clair (prévu)

## 🔧 Fonctionnalités Principales

### 1️⃣ Supervision des Serres
- **Consultation**: Voir toutes les serres avec détails complets
- **Filtrage**: Par état (actif/maintenance/inactif) et par agriculteur
- **Contrôle**: Changer l'état des serres
- **Suppression**: Supprimer serres en cas d'erreur critique
- **Statistiques**: Nombre total, actives, surface totale

### 2️⃣ Supervision des Zones  
- **Consultation**: Voir toutes les zones avec détails
- **Détection**: Incohérences automatiques (zone sans serre, surface > serre)
- **Filtrage**: Par état et par serre parente
- **Contrôle**: Désactiver/activer zones
- **Validation**: Vérification automatique de la cohérence

### 3️⃣ Paramètres Métier
- **États possibles**: Serre (actif, maintenance, inactive), Zone (active, inactive, maintenance)
- **Cultures autorisées**: Liste configurable des cultures
- **Règles de surface**: Min/max pour serres et zones
- **Limites**: Nombre max de zones par serre
- **Configuration**: Interface simple pour modifier les règles

### 4️⃣ Statistiques Globales
- Vue d'ensemble en temps réel
- Graphiques de répartition
- Indicateurs de performance
- Alertes sur incohérences
- Historique d'activité

### 5️⃣ Gestion Utilisateurs
- Création d'agriculteurs
- Activation/désactivation comptes
- Gestion des rôles (admin/agriculteur)
- Réinitialisation mots de passe
- Impersonation (prévue)

## 🛡️ Sécurité

### Contrôle d'Accès
- Rôles basés sur les permissions
- Vérification automatique des droits
- Journalisation des actions
- Session sécurisée

### Validation
- Validation côté serveur et client
- Protection CSRF
- Sanitization des entrées
- Vérification des cohérences

## 📊 Journal d'Activité

### Traçabilité
- Qui a fait quoi
- Quand l'action a été effectuée  
- Sur quelle entité
- Anciennes et nouvelles valeurs
- Adresse IP et user agent

## 🚀 Performance

### Optimisations
- Requêtes optimisées
- Mise en cache des statistiques
- Pagination des listes
- Lazy loading des relations

## 🔄 Extensibilité

### Architecture Modulaire
- Contrôleurs séparés par fonctionnalité
- Services réutilisables
- Templates modulaires
- Configuration flexible

## 📱 Responsive Design

### Adaptation
- Mobile-first approach
- Sidebar collapsible
- Tableaux scrollables
- Touch-friendly interfaces

---

## 🎯 Points Clés pour le Jury

1. **Architecture MVC propre** avec séparation des responsabilités
2. **Design moderne et cohérent** avec thème vert professionnel
3. **Fonctionnalités complètes** de supervision et contrôle
4. **Détection automatique** des incohérences
5. **Paramétrage flexible** des règles métier
6. **Sécurité robuste** avec journalisation
7. **Code propre et documenté**
8. **Extensibilité** pour évolutions futures

Le système est prêt à être déployé et peut être facilement étendu avec de nouvelles fonctionnalités.
