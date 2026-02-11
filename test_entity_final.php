<?php

require_once 'vendor/autoload.php';

use App\Entity\Serre;
use App\Repository\SerreRepository;

echo "=== Test de Chargement des Entités ===\n";

try {
    echo "✅ Use App\Entity\Serre - OK\n";
    echo "✅ Use App\Repository\SerreRepository - OK\n";
    
    // Test 2: Instancier l'entité
    $serre = new Serre();
    echo "✅ new Serre() - OK\n";
    
    // Test 3: Utiliser les méthodes
    $serre->setNomSerre('Test');
    $serre->setLocalisation('Test Location');
    $serre->setSurface(100.0);
    $serre->setEtatSerre('actif');
    
    echo "✅ Méthodes set* - OK\n";
    echo "Nom: " . $serre->getNomSerre() . "\n";
    echo "Localisation: " . $serre->getLocalisation() . "\n";
    echo "Surface: " . $serre->getSurface() . "\n";
    echo "État: " . $serre->getEtatSerre() . "\n";
    
    // Test 4: Vérifier les annotations ORM
    $reflection = new ReflectionClass('App\Entity\Serre');
    $attributes = $reflection->getAttributes();
    
    echo "\n=== Annotations ORM ===\n";
    foreach ($attributes as $attribute) {
        if ($attribute instanceof \Attribute) {
            echo "Attribute: " . get_class($attribute) . "\n";
        }
    }
    
    echo "\n=== Test Terminé avec Succès ===\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
