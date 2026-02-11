<?php

require_once 'vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;

// Test simple pour vérifier le routing
$kernel = new Kernel('dev', false);
$kernel->boot();

$request = Request::create('/agriculteur/dashboard');
$response = $kernel->handle($request);

echo "Status: " . $response->getStatusCode() . "\n";
echo "Content-Type: " . $response->headers->get('Content-Type') . "\n";

if ($response->getStatusCode() === 200) {
    echo "✅ Route agriculteur_dashboard fonctionne !\n";
} else {
    echo "❌ Erreur de route\n";
    echo "Content: " . $response->getContent() . "\n";
}

$kernel->shutdown();
