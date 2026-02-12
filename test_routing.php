<?php

require_once 'vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RequestContext;

// Test du routing Symfony
try {
    $kernel = new Kernel('dev', false);
    $kernel->boot();
    
    $router = $kernel->getContainer()->get('router');
    
    echo "=== Test du Routing Symfony ===\n";
    
    // Test 1: Vérifier si les routes existent
    $routes = $router->getRouteCollection();
    echo "Nombre total de routes: " . $routes->count() . "\n\n";
    
    // Chercher les routes agriculteur
    $agriculteurRoutes = [];
    foreach ($routes as $name => $route) {
        if (strpos($name, 'agriculteur') !== false) {
            $agriculteurRoutes[] = $name;
            echo "Route trouvée: $name\n";
        }
    }
    
    echo "\n=== Routes Agriculteur Trouvées ===\n";
    foreach ($agriculteurRoutes as $route) {
        echo "- $route\n";
    }
    
    // Test 2: Essayer de résoudre une route
    try {
        $route = $router->getRouteCollection()->get('agriculteur_dashboard');
        if ($route) {
            echo "\n✅ Route 'agriculteur_dashboard' trouvée !\n";
            echo "Path: " . $route->getPath() . "\n";
            echo "Controller: " . $route->getDefaults()['_controller'] . "\n";
        } else {
            echo "\n❌ Route 'agriculteur_dashboard' NON trouvée !\n";
        }
    } catch (Exception $e) {
        echo "\n❌ Erreur lors du test de route: " . $e->getMessage() . "\n";
    }
    
    // Test 3: Vérifier les entités enregistrées
    try {
        $entityManager = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        $metadataFactory = $entityManager->getMetadataFactory();
        
        echo "\n=== Entités Enregistrées ===\n";
        $allMetadata = $metadataFactory->getAllMetadata();
        foreach ($allMetadata as $metadata) {
            echo "- " . $metadata->getName() . "\n";
        }
        
        // Chercher spécifiquement l'entité Serre
        $serreMetadata = $metadataFactory->getMetadataFor('App\\Entity\\Serre');
        if ($serreMetadata) {
            echo "\n✅ Entité 'App\\Entity\\Serre' trouvée !\n";
        } else {
            echo "\n❌ Entité 'App\\Entity\\Serre' NON trouvée !\n";
        }
        
    } catch (Exception $e) {
        echo "\n❌ Erreur lors du test des entités: " . $e->getMessage() . "\n";
    }
    
    $kernel->shutdown();
    
} catch (Exception $e) {
    echo "❌ Erreur générale: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
