<?php

namespace App\Controller\Admin;

use App\Entity\Setting;
use App\Repository\SettingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/admin')]
class AdminSettingsController extends AbstractController
{
    private SettingRepository $settingRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        SettingRepository $settingRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->settingRepository = $settingRepository;
        $this->entityManager = $entityManager;
    }

    #[Route('/settings', name: 'app_admin_settings')]
    public function settings(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $settings = $request->request->all();
            
            foreach ($settings as $key => $value) {
                $setting = $this->settingRepository->findByKey($key);
                if ($setting) {
                    $setting->setValue($value);
                    $this->entityManager->flush();
                }
            }

            $this->addFlash('success', 'Paramètres mis à jour avec succès');
        }

        $settings = $this->settingRepository->findAll();
        $groupedSettings = [];
        
        foreach ($settings as $setting) {
            $groupedSettings[$setting->getCategory()][] = $setting;
        }

        return $this->render('admin/settings_metier.html.twig', [
            'settings' => $groupedSettings
        ]);
    }

    #[Route('/settings/init', name: 'app_admin_settings_init')]
    public function initSettings(): Response
    {
        $defaultSettings = [
            'SERRE_ETATS' => ['actif', 'maintenance', 'inactive'],
            'ZONE_ETATS' => ['active', 'inactive', 'maintenance'],
            'CULTURES_AUTORISEES' => ['Tomates', 'Salades', 'Carottes', 'Concombres', 'Poivrons'],
            'SURFACE_MIN_SERRE' => '10',
            'SURFACE_MAX_SERRE' => '10000',
            'SURFACE_MIN_ZONE' => '1',
            'SURFACE_MAX_ZONE' => '5000',
            'MAX_ZONES_PAR_SERRE' => '10',
            'NOTIFICATIONS_ENABLED' => '1'
        ];

        foreach ($defaultSettings as $key => $value) {
            $existing = $this->settingRepository->findByKey($key);
            if (!$existing) {
                $setting = new Setting();
                $setting->setKey($key);
                $setting->setValue(is_array($value) ? json_encode($value) : $value);
                $setting->setCategory('METIER');
                $setting->setType(is_array($value) ? 'JSON' : 'STRING');
                $setting->setIsPublic(false);
                $setting->setUpdatedAt(new \DateTime());
                
                $this->entityManager->persist($setting);
            }
        }

        $this->entityManager->flush();
        $this->addFlash('success', 'Paramètres par défaut initialisés');

        return $this->redirectToRoute('app_admin_settings');
    }
}
