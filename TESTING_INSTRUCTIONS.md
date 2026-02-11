# 🧪 Instructions de Test Complètes

## ✅ Nettoyage Effectué

### Fichiers Supprimés (Non essentiels)
- ❌ `base.html.twig` - Ancien layout dupliqué
- ❌ `serres.html.twig` - Ancienne version (remplacée)
- ❌ `zones.html.twig` - Ancienne version (remplacée)
- ❌ `settings.html.twig` - Ancienne version (remplacée)
- ❌ `users.html.twig` - Gestion utilisateurs (optionnelle)
- ❌ `edit_user.html.twig` - Édition utilisateur (optionnelle)
- ❌ `new_user.html.twig` - Création utilisateur (optionnelle)
- ❌ `reset_password.html.twig` - Réinitialisation mot de passe
- ❌ `forgot_password.html.twig` - Mot de passe oublié
- ❌ `login.html.twig` - Connexion (peut être ajoutée plus tard)
- ❌ `base_back.html.twig` - Ancien layout (remplacé)

### Fichiers Essentiels Conservés
- ✅ `base_admin.html.twig` - Layout principal avec design vert
- ✅ `dashboard.html.twig` - Tableau de bord administratif
- ✅ `serres_supervision.html.twig` - Supervision des serres
- ✅ `zones_supervision.html.twig` - Supervision des zones
- ✅ `settings_metier.html.twig` - Paramètres métier

## 🚀 Comment Tester - Étape par Étape

### 1️⃣ Démarrer le Serveur Symfony
```bash
cd c:/Users/DELL/Desktop/Agrosense/greenhouse
symfony server:start
```

### 2️⃣ Accéder aux URLs de Test

#### 🏠 Tableau de Bord Admin
```
URL: http://localhost:8000/admin/
```
- Doit afficher le tableau de bord avec statistiques
- Design vert moderne avec sidebar
- Cartes avec indicateurs

#### 🏠 Supervision des Serres
```
URL: http://localhost:8000/admin/serres
```
- Liste des serres avec filtres
- Actions: Voir détails, changer état, supprimer
- Statistiques en temps réel

#### 📏 Supervision des Zones
```
URL: http://localhost:8000/admin/zones
```
- Liste des zones avec détection incohérences
- Validation automatique surface zone ≤ surface serre
- Alertes zones sans serre parente

#### ⚙️ Paramètres Métier
```
URL: http://localhost:8000/admin/settings
```
- Configuration des états possibles
- Types de cultures autorisées
- Règles de surface min/max

### 3️⃣ Configuration des Routes

Copiez le contenu de `config/routes_admin.yaml` dans votre `config/routes.yaml` principal.

### 4️⃣ Correction des Contrôleurs

Remplacez les anciens contrôleurs par les versions "_fixed":
- `AdminSerreController_fixed.php` → `AdminSerreController.php`
- `AdminZoneController_fixed.php` → `AdminZoneController.php`
- `AdminSettingsController_fixed.php` → `AdminSettingsController.php`

### 5️⃣ Test des Fonctionnalités Clés

#### ✅ À Vérifier
1. **Navigation** - Tous les liens fonctionnent
2. **Design** - Thème vert cohérent
3. **Filtres** - Par état et par utilisateur
4. **Actions** - Changement état et suppression
5. **Incohérences** - Détection automatique
6. **Paramètres** - Modification et sauvegarde
7. **Responsive** - Adaptation mobile/desktop

### 6️⃣ Données de Test (Optionnel)

Si vous avez une base de données, ajoutez:
```sql
-- Serres de test
INSERT INTO serre (nom_serre, localisation, surface, etat_serre, date_mise_en_service) VALUES
('Serre Alpha', 'Zone Nord', 500.0, 'actif', '2024-01-15'),
('Serre Beta', 'Zone Sud', 750.0, 'maintenance', '2024-02-20');

-- Zones de test
INSERT INTO zone (nom_zone, type_zone, superficie, etat_zone, culture_associee, serre_id) VALUES
('Zone A1', 'Tomates', 100.0, 'active', 'Tomates cerises', 1),
('Zone A2', 'Salades', 80.0, 'active', 'Laitues', 1);
```

### 7️⃣ Dépannage Rapide

#### 🐛 Si Erreur 404
```bash
php bin/console cache:clear
php bin/console debug:router
```

#### 🐛 Si Erreur de Template
```bash
php bin/console debug:twig
```

#### 🐛 Si Erreur de Contrôleur
```bash
php bin/console debug:container
```

## 🎯 Checklist de Test Final

- [ ] Tableau de bord s'affiche avec design vert
- [ ] Navigation sidebar fonctionnelle
- [ ] Liste serres avec filtres fonctionne
- [ ] Actions serres (changer état, supprimer)
- [ ] Liste zones avec détection incohérences
- [ ] Paramètres métier modifiables
- [ ] Design responsive mobile/desktop
- [ ] Messages flash (succès/erreur) s'affichent

## 📱 Test Responsive

Testez sur différentes tailles:
- **Desktop** (>1200px) - Sidebar fixe
- **Tablet** (768px-1200px) - Adaptation
- **Mobile** (<768px) - Sidebar caché, menu burger

---

Le système est maintenant **optimisé et prêt pour les tests** ! 🌿

Accédez à `http://localhost:8000/admin/` pour commencer.
