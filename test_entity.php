<?php

require_once 'vendor/autoload.php';

use App\Entity\Serre;

// Test simple pour vérifier si l'entité Serre est bien chargée
try {
    $serre = new Serre();
    echo "✅ Entité Serre chargée avec succès\n";
    echo "Namespace: " . get_class($serre) . "\n";
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
