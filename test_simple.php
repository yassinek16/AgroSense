<?php

// Test simple pour vérifier si l'entité Serre fonctionne
require_once 'vendor/autoload.php';

use App\Entity\Serre;

try {
    // Test basique de création d'entité
    $serre = new Serre();
    $serre->setNomSerre('Test Serre');
    $serre->setLocalisation('Test Localisation');
    $serre->setSurface(100.0);
    $serre->setEtatSerre('actif');
    
    echo "✅ Entité Serre fonctionne !\n";
    echo "Nom: " . $serre->getNomSerre() . "\n";
    echo "Localisation: " . $serre->getLocalisation() . "\n";
    echo "Surface: " . $serre->getSurface() . "\n";
    echo "État: " . $serre->getEtatSerre() . "\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
