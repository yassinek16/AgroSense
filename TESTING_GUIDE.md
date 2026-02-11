# 🧪 Guide de Test - Système d'Administration GreenTech

## 📋 Fichiers Essentiels Conservés

### Templates Admin (Essentiels)
- ✅ `base_admin.html.twig` - Layout principal avec design vert
- ✅ `dashboard.html.twig` - Tableau de bord administratif
- ✅ `serres_supervision.html.twig` - Supervision des serres
- ✅ `zones_supervision.html.twig` - Supervision des zones
- ✅ `settings_metier.html.twig` - Paramètres métier

### Templates Supprimés (Non essentiels)
- ❌ `serres.html.twig` - Ancienne version (remplacée par serres_supervision)
- ❌ `zones.html.twig` - Ancienne version (remplacée par zones_supervision)
- ❌ `settings.html.twig` - Ancienne version (remplacée par settings_metier)
- ❌ `users.html.twig` - Gestion utilisateurs (optionnelle pour démo)
- ❌ `edit_user.html.twig` - Édition utilisateur (optionnelle)
- ❌ `new_user.html.twig` - Création utilisateur (optionnelle)
- ❌ `reset_password.html.twig` - Réinitialisation mot de passe
- ❌ `forgot_password.html.twig` - Mot de passe oublié
- ❌ `login.html.twig` - Connexion (peut être ajoutée plus tard)
- ❌ `base_back.html.twig` - Ancien layout (remplacé par base_admin)

## 🚀 Comment Tester le Système

### 1️⃣ Prérequis Symfony

```bash
# Vérifier Symfony CLI
symfony version

# Démarrer le serveur local
symfony server:start
```

### 2️⃣ Routes Principales à Tester

#### 🏠 Tableau de Bord Admin
```
URL: http://localhost:8000/admin/
Route: app_admin_dashboard
Controller: AdminDashboardController
Template: admin/dashboard.html.twig
```

#### 🏠 Supervision des Serres
```
URL: http://localhost:8000/admin/serres
Route: app_admin_serres
Controller: AdminSerreController
Template: admin/serres_supervision.html.twig
```

#### 📏 Supervision des Zones
```
URL: http://localhost:8000/admin/zones
Route: app_admin_zones
Controller: AdminZoneController
Template: admin/zones_supervision.html.twig
```

#### ⚙️ Paramètres Métier
```
URL: http://localhost:8000/admin/settings
Route: app_admin_settings
Controller: AdminSettingsController
Template: admin/settings_metier.html.twig
```

### 3️⃣ Configuration des Routes

Ajoutez ces routes dans `config/routes.yaml`:

```yaml
# config/routes.yaml
admin_dashboard:
    path: /admin/
    controller: App\Controller\Admin\AdminDashboardController::dashboard

admin_serres:
    path: /admin/serres
    controller: App\Controller\Admin\AdminSerreController::serres

admin_zones:
    path: /admin/zones
    controller: App\Controller\Admin\AdminZoneController::zones

admin_settings:
    path: /admin/settings
    controller: App\Controller\Admin\AdminSettingsController::settings
```

### 4️⃣ Test des Fonctionnalités

#### 🏠 Supervision Serres
- ✅ Affichage liste des serres
- ✅ Filtres par état et agriculteur
- ✅ Changement d'état (actif/maintenance)
- ✅ Suppression serre
- ✅ Statistiques en temps réel

#### 📏 Supervision Zones
- ✅ Affichage liste des zones
- ✅ Détection incohérences automatiques
- ✅ Validation surface zone ≤ surface serre
- ✅ Alertes zones sans serre
- ✅ Changement état et suppression

#### ⚙️ Paramètres Métier
- ✅ Configuration états possibles
- ✅ Types de cultures autorisées
- ✅ Règles de surface min/max
- ✅ Nombre max zones par serre
- ✅ Éditeur JSON pour listes

### 5️⃣ Test du Design

#### 🎨 Vérification Visuelle
- ✅ Thème vert cohérent
- ✅ Sidebar fonctionnel
- ✅ Cards avec animations
- ✅ Badges colorés
- ✅ Tableaux responsives
- ✅ Mode mobile adapté

#### 📱 Test Responsive
- Desktop (>1200px)
- Tablet (768px-1200px)
- Mobile (<768px)

### 6️⃣ Données de Test

#### Créer des serres de test:
```sql
INSERT INTO serre (nom_serre, localisation, surface, etat_serre, date_mise_en_service) VALUES
('Serre Alpha', 'Zone Nord', 500.0, 'actif', '2024-01-15'),
('Serre Beta', 'Zone Sud', 750.0, 'maintenance', '2024-02-20'),
('Serre Gamma', 'Zone Est', 300.0, 'actif', '2024-03-10');
```

#### Créer des zones de test:
```sql
INSERT INTO zone (nom_zone, type_zone, superficie, etat_zone, culture_associee, serre_id) VALUES
('Zone A1', 'Tomates', 100.0, 'active', 'Tomates cerises', 1),
('Zone A2', 'Salades', 80.0, 'active', 'Laitues', 1),
('Zone B1', 'Carottes', 150.0, 'maintenance', 'Carottes', 2);
```

### 7️⃣ Points de Validation

#### ✅ Fonctionnalités à Valider
1. **Navigation** - Tous les liens fonctionnent
2. **Filtres** - Filtres par état et utilisateur
3. **Actions** - Changement état et suppression
4. **Incohérences** - Détection automatique
5. **Paramètres** - Modification et sauvegarde
6. **Design** - Interface responsive et moderne
7. **Performance** - Chargement rapide (<2s)

#### ⚠️ Points d'Attention
- Vérifier que les contrôleurs existent
- Confirmer les routes dans `routes.yaml`
- Tester avec des données réelles
- Valider la cohérence des données

### 8️⃣ Dépannage

#### 🐛 Problèmes Communs
```bash
# Si erreur de route non trouvée
php bin/console debug:router

# Si erreur de contrôleur
php bin/console debug:container

# Si erreur de template
php bin/console debug:twig
```

#### 🔧 Solutions Rapides
1. **Vider le cache**: `php bin/console cache:clear`
2. **Vérifier les permissions**: `chmod -R 755 var/`
3. **Redémarrer le serveur**: `symfony server:stop && symfony server:start`

---

## 🎯 Checklist de Test Final

- [ ] Tableau de bord s'affiche correctement
- [ ] Liste des serres avec filtres fonctionne
- [ ] Liste des zones avec détection incohérences
- [ ] Paramètres métier modifiables
- [ ] Design vert cohérent sur toutes les pages
- [ ] Navigation responsive mobile/desktop
- [ ] Actions (changer état, supprimer) fonctionnent
- [ ] Statistiques et indicateurs corrects

Le système est prêt pour la démo ! 🌿
