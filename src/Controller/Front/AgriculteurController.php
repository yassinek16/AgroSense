<?php

namespace App\Controller\Front;

use App\Entity\Serre;
use App\Entity\Zone;
use App\Repository\SerreRepository;
use App\Repository\ZoneRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


#[Route('/agriculteur')]
class AgriculteurController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    #[Route('/dashboard', name: 'app_agriculteur_dashboard')]
    public function dashboard(): Response
    {
        // Récupérer toutes les serres directement depuis l'EntityManager
        $serres = $this->entityManager->getRepository(Serre::class)->findAll();
        $zones = $this->entityManager->getRepository(Zone::class)->findAll();

        // Mini historique - informations récentes
        $historique = [];
        $derniereSerre = null;
        $derniereZone = null;
        $derniereModification = null;
        
        if (!empty($serres)) {
            $derniereSerre = $serres[0];
            foreach ($serres as $serre) {
                if ($serre->getDateMiseEnService() && $derniereSerre->getDateMiseEnService()) {
                    if ($serre->getDateMiseEnService() > $derniereSerre->getDateMiseEnService()) {
                        $derniereSerre = $serre;
                    }
                }
            }
        }
        
        if (!empty($zones)) {
            $derniereZone = $zones[0];
            foreach ($zones as $zone) {
                if ($zone->getId() > $derniereZone->getId()) {
                    $derniereZone = $zone;
                }
            }
        }
        
        // Dernière modification (serre ou zone)
        $toutesEntites = array_merge($serres, $zones);
        if (!empty($toutesEntites)) {
            $derniereModification = $toutesEntites[0];
            foreach ($toutesEntites as $entite) {
                if ($entite instanceof Serre) {
                    $dateEntite = $entite->getDateMiseEnService() ?? new \DateTime('1970-01-01');
                } else {
                    // Pour les zones, on utilise l'ID comme critère de "récence"
                    $dateEntite = new \DateTime('1970-01-01');
                    $dateEntite->setTimestamp($entite->getId());
                }
                
                if ($derniereModification instanceof Serre) {
                    $dateDerniere = $derniereModification->getDateMiseEnService() ?? new \DateTime('1970-01-01');
                } else {
                    $dateDerniere = new \DateTime('1970-01-01');
                    $dateDerniere->setTimestamp($derniereModification->getId());
                }
                
                if ($dateEntite > $dateDerniere) {
                    $derniereModification = $entite;
                }
            }
        }

        $stats = [
            'totalSerres' => count($serres),
            'totalZones' => count($zones),
            'surfaceTotale' => array_sum(array_map(fn($s) => $s->getSurface(), $serres)),
            'serresActives' => count(array_filter($serres, fn($s) => $s->getEtatSerre() === 'actif')),
            'zonesActives' => count(array_filter($zones, fn($z) => $z->getEtatZone() === 'active')),
            'serresEnMaintenance' => count(array_filter($serres, fn($s) => $s->getEtatSerre() === 'maintenance')),
        ];

        // Alertes visuelles améliorées
        $alertes = [];
        
        // Alerte serres sans zones
        foreach ($serres as $serre) {
            $zonesSerre = array_filter($zones, fn($z) => $z->getSerre() && $z->getSerre()->getId() === $serre->getId());
            if (empty($zonesSerre) && $serre->getEtatSerre() === 'actif') {
                $alertes[] = [
                    'type' => 'warning',
                    'message' => "🏠 La serre '{$serre->getNomSerre()}' est active mais n'a aucune zone",
                    'icon' => '⚠️',
                    'action' => 'Créer une zone'
                ];
            }
            
            if ($serre->getEtatSerre() === 'maintenance') {
                $alertes[] = [
                    'type' => 'warning',
                    'message' => "🔧 La serre '{$serre->getNomSerre()}' est en maintenance",
                    'icon' => '🔧'
                ];
            }
            
            // Alerte surface presque pleine (≥ 90%)
            if (!empty($zonesSerre)) {
                $surfaceUtilisee = array_sum(array_map(fn($z) => $z->getSuperficie(), $zonesSerre));
                $pourcentageUtilise = ($surfaceUtilisee / $serre->getSurface()) * 100;
                
                if ($pourcentageUtilise >= 90) {
                    $alertes[] = [
                        'type' => 'warning',
                        'message' => "📏 La serre '{$serre->getNomSerre()}' est presque pleine (" . round($pourcentageUtilise, 1) . "% utilisé)",
                        'icon' => '⚠️'
                    ];
                }
            }
        }
        
        // Alerte zones inactives
        foreach ($zones as $zone) {
            if ($zone->getEtatZone() === 'inactive') {
                $alertes[] = [
                    'type' => 'warning',
                    'message' => "🌱 La zone '{$zone->getNomZone()}' est inactive",
                    'icon' => '⚠️'
                ];
            }
        }

        return $this->render('agriculteur/dashboard.html.twig', [
            'stats' => $stats,
            'alertes' => $alertes,
            'serres' => $serres,
            'zones' => $zones,
            'historique' => [
                'derniereSerre' => $derniereSerre,
                'derniereZone' => $derniereZone,
                'derniereModification' => $derniereModification
            ]
        ]);
    }

    #[Route('/serres', name: 'app_agriculteur_serres')]
    public function serres(Request $request): Response
    {
        // Récupérer toutes les serres
        $serres = $this->entityManager->getRepository(Serre::class)->findAll();

        // Filtrage
        $search = $request->query->get('search');
        $etat = $request->query->get('etat');
        $sortBy = $request->query->get('sort', 'nom');

        if ($search) {
            $serres = array_filter($serres, fn($s) => 
                stripos($s->getNomSerre(), $search) !== false || 
                stripos($s->getLocalisation(), $search) !== false
            );
        }

        if ($etat) {
            $serres = array_filter($serres, fn($s) => $s->getEtatSerre() === $etat);
        }

        // Tri
        switch ($sortBy) {
            case 'surface':
                usort($serres, fn($a, $b) => $b->getSurface() <=> $a->getSurface());
                break;
            case 'date':
                usort($serres, fn($a, $b) => $b->getDateMiseEnService() <=> $a->getDateMiseEnService());
                break;
            default:
                usort($serres, fn($a, $b) => $a->getNomSerre() <=> $b->getNomSerre());
        }

        return $this->render('agriculteur/serres.html.twig', [
            'serres' => $serres,
            'search' => $search,
            'etat' => $etat,
            'sortBy' => $sortBy
        ]);
    }

    #[Route('/zones', name: 'app_agriculteur_zones')]
    public function zones(Request $request): Response
    {
        // Récupérer toutes les zones
        $zones = $this->entityManager->getRepository(Zone::class)->findAll();

        // Filtrage
        $search = $request->query->get('search');
        $etat = $request->query->get('etat');
        $sortBy = $request->query->get('sort', 'nom');

        if ($search) {
            $zones = array_filter($zones, fn($z) => 
                stripos($z->getNomZone(), $search) !== false || 
                stripos($z->getCultureAssociee(), $search) !== false
            );
        }

        if ($etat) {
            $zones = array_filter($zones, fn($z) => $z->getEtatZone() === $etat);
        }

        // Tri
        switch ($sortBy) {
            case 'superficie':
                usort($zones, fn($a, $b) => $b->getSuperficie() <=> $a->getSuperficie());
                break;
            case 'culture':
                usort($zones, fn($a, $b) => $a->getCultureAssociee() <=> $b->getCultureAssociee());
                break;
            default:
                usort($zones, fn($a, $b) => $a->getNomZone() <=> $b->getNomZone());
        }

        // Calcul du nombre de serres utilisées (approche MVC propre)
        $serresUtilisees = [];
        foreach ($zones as $zone) {
            if ($zone->getSerre()) {
                $serresUtilisees[$zone->getSerre()->getId()] = true;
            }
        }
        $nbSerresUtilisees = count($serresUtilisees);

        return $this->render('agriculteur/zones.html.twig', [
            'zones' => $zones,
            'search' => $search,
            'etat' => $etat,
            'sortBy' => $sortBy,
            'nbSerresUtilisees' => $nbSerresUtilisees,
        ]);
    }

     #[Route('/serre/new', name: 'app_agriculteur_serre_new')]
    public function newSerre(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            
            $serre = new Serre();
            $serre->setNomSerre(trim($data['nomSerre']));
            $serre->setLocalisation(trim($data['localisation']));
            $serre->setSurface((float)$data['surface']);
            $serre->setEtatSerre(trim($data['etatSerre']));
            
            if (!empty($data['dateMiseEnService'])) {
                $serre->setDateMiseEnService(new \DateTime($data['dateMiseEnService']));
            }

            // Validation Symfony
            $errors = $this->validator->validate($serre);
            
            if (count($errors) > 0) {
                return $this->render('agriculteur/serre_form.html.twig', [
                    'serre' => null,
                    'action' => 'Créer',
                    'errors' => $errors
                ]);
            }

            $this->entityManager->persist($serre);
            $this->entityManager->flush();

            $this->addFlash('success', 'Serre créée avec succès !');
            return $this->redirectToRoute('app_agriculteur_serres');
        }

        return $this->render('agriculteur/serre_form.html.twig', [
            'serre' => null,
            'action' => 'Créer',
            'errors' => []
        ]);
    }

    #[Route('/serre/{id}/edit', name: 'app_agriculteur_serre_edit')]
    public function editSerre(Serre $serre, Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            
            // Validation des champs non vides
            if (empty(trim($data['nomSerre']))) {
                $this->addFlash('error', 'Le nom de la serre ne peut pas être vide.');
                return $this->redirectToRoute('app_agriculteur_serre_edit', ['id' => $serre->getId()]);
            }
            
            if (empty(trim($data['localisation']))) {
                $this->addFlash('error', 'La localisation ne peut pas être vide.');
                return $this->redirectToRoute('app_agriculteur_serre_edit', ['id' => $serre->getId()]);
            }
            
            if (empty($data['surface']) || (float)$data['surface'] <= 0) {
                $this->addFlash('error', 'La surface doit être un nombre positif.');
                return $this->redirectToRoute('app_agriculteur_serre_edit', ['id' => $serre->getId()]);
            }
            
            if (empty(trim($data['etatSerre']))) {
                $this->addFlash('error', 'L\'état de la serre ne peut pas être vide.');
                return $this->redirectToRoute('app_agriculteur_serre_edit', ['id' => $serre->getId()]);
            }
            
            $serre->setNomSerre(trim($data['nomSerre']));
            $serre->setLocalisation(trim($data['localisation']));
            $serre->setSurface((float)$data['surface']);
            $serre->setEtatSerre(trim($data['etatSerre']));
            
            if (!empty($data['dateMiseEnService'])) {
                $serre->setDateMiseEnService(new \DateTime($data['dateMiseEnService']));
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Serre mise à jour avec succès !');
            return $this->redirectToRoute('app_agriculteur_serre_details', ['id' => $serre->getId()]);
        }

        return $this->render('agriculteur/serre_form.html.twig', [
            'serre' => $serre,
            'action' => 'Modifier'
        ]);
    }

    #[Route('/serre/{id}/delete', name: 'app_agriculteur_serre_delete')]
    public function deleteSerre(Serre $serre, Request $request): Response
    {
        if ($request->isMethod('POST')) {
            // Récupérer et supprimer d'abord toutes les zones associées
            $zones = $this->entityManager->getRepository(Zone::class)->findBy(['serre' => $serre]);
            
            foreach ($zones as $zone) {
                $this->entityManager->remove($zone);
            }
            
            // Puis supprimer la serre
            $this->entityManager->remove($serre);
            $this->entityManager->flush();

            $this->addFlash('success', 'Serre et ses zones supprimées avec succès !');
            return $this->redirectToRoute('app_agriculteur_serres');
        }

        return $this->render('agriculteur/confirm_delete_serre.html.twig', ['serre' => $serre]);
    }

    #[Route('/serre/{id}', name: 'app_agriculteur_serre_details')]
    public function serreDetails(Serre $serre): Response
    {
        $zones = $this->entityManager->getRepository(Zone::class)->findBy(['serre' => $serre]);
        $surfaceUtilisee = array_sum(array_map(fn($z) => $z->getSuperficie(), $zones));
        $surfaceRestante = $serre->getSurface() - $surfaceUtilisee;

        return $this->render('agriculteur/serre_details.html.twig', [
            'serre' => $serre,
            'zones' => $zones,
            'surfaceUtilisee' => $surfaceUtilisee,
            'surfaceRestante' => $surfaceRestante
        ]);
    }

    #[Route('/zone/new', name: 'app_agriculteur_zone_new')]
    public function newZone(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            
            // Validation des champs non vides
            if (empty(trim($data['nomZone']))) {
                $this->addFlash('error', 'Le nom de la zone ne peut pas être vide.');
                return $this->redirectToRoute('app_agriculteur_zone_new');
            }
            
            if (empty(trim($data['typeZone']))) {
                $this->addFlash('error', 'Le type de la zone ne peut pas être vide.');
                return $this->redirectToRoute('app_agriculteur_zone_new');
            }
            
            if (empty($data['superficie']) || (float)$data['superficie'] <= 0) {
                $this->addFlash('error', 'La superficie doit être un nombre positif.');
                return $this->redirectToRoute('app_agriculteur_zone_new');
            }
            
            if (empty(trim($data['etatZone']))) {
                $this->addFlash('error', 'L\'état de la zone ne peut pas être vide.');
                return $this->redirectToRoute('app_agriculteur_zone_new');
            }
            
            $zone = new Zone();
            $zone->setNomZone(trim($data['nomZone']));
            $zone->setTypeZone(trim($data['typeZone']));
            $zone->setSuperficie((float)$data['superficie']);
            $zone->setEtatZone(trim($data['etatZone']));
            $zone->setCultureAssociee(trim($data['cultureAssociee']));
            
            // Association de la serre si fournie
            if (!empty($data['serre'])) {
                $serre = $this->entityManager->getRepository(Serre::class)->find($data['serre']);
                if (!$serre) {
                    $this->addFlash('error', 'Serre non trouvée.');
                    return $this->redirectToRoute('app_agriculteur_zone_new');
                }
                $zone->setSerre($serre);
            }

            $this->entityManager->persist($zone);
            $this->entityManager->flush();

            $this->addFlash('success', 'Zone créée avec succès !');
            
            if ($zone->getSerre()) {
                return $this->redirectToRoute('app_agriculteur_serre_details', ['id' => $zone->getSerre()->getId()]);
            } else {
                return $this->redirectToRoute('app_agriculteur_serres');
            }
        }

        $serres = $this->entityManager->getRepository(Serre::class)->findAll();

        return $this->render('agriculteur/zone_form.html.twig', [
            'zone' => null,
            'serres' => $serres,
            'action' => 'Créer'
        ]);
    }

    #[Route('/zone/{id}/edit', name: 'app_agriculteur_zone_edit')]
    public function editZone(Zone $zone, Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            
            $zone->setNomZone($data['nomZone']);
            $zone->setTypeZone($data['typeZone']);
            $zone->setSuperficie((float)$data['superficie']);
            $zone->setEtatZone($data['etatZone']);
            $zone->setCultureAssociee($data['cultureAssociee']);
            
            // Association de la serre si fournie
            if (!empty($data['serre'])) {
                $serre = $this->entityManager->getRepository(Serre::class)->find($data['serre']);
                if (!$serre) {
                    $this->addFlash('error', 'Serre non trouvée.');
                    return $this->redirectToRoute('app_agriculteur_zone_edit', ['id' => $zone->getId()]);
                }
                $zone->setSerre($serre);
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Zone mise à jour avec succès !');
            return $this->redirectToRoute('app_agriculteur_serre_details', ['id' => $zone->getSerre()->getId()]);
        }

        $serres = $this->entityManager->getRepository(Serre::class)->findAll();

        return $this->render('agriculteur/zone_form.html.twig', [
            'zone' => $zone,
            'serres' => $serres,
            'action' => 'Modifier'
        ]);
    }

    #[Route('/zone/{id}/delete', name: 'app_agriculteur_zone_delete')]
    public function deleteZone(Zone $zone, Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $serreId = $zone->getSerre()->getId();
            $this->entityManager->remove($zone);
            $this->entityManager->flush();

            $this->addFlash('success', 'Zone supprimée avec succès !');
            return $this->redirectToRoute('app_agriculteur_serre_details', ['id' => $serreId]);
        }

        return $this->render('agriculteur/confirm_delete_zone.html.twig', ['zone' => $zone]);
    }
}
