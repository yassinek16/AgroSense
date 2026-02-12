<?php
// src/Service/WeatherService.php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Psr\Cache\CacheItemPoolInterface;

class WeatherService
{
    private HttpClientInterface $httpClient;
    private CacheInterface $cache;
    private string $apiKey;

    public function __construct(
        HttpClientInterface $httpClient,
        CacheInterface $cache,
        string $weatherApiKey = ''
    ) {
        $this->httpClient = $httpClient;
        $this->cache = $cache;
        $this->apiKey = $weatherApiKey;
    }

    /**
     * Récupère la météo actuelle pour une localisation
     */
    public function getCurrentWeather(string $localisation): ?array
    {
        $cacheKey = 'weather_' . md5($localisation);
        
        return $this->cache->get($cacheKey, function ($item) use ($localisation) {
            $item->expiresAfter(1800); // Cache 30 minutes

            try {
                $response = $this->httpClient->request('GET', 'https://api.openweathermap.org/data/2.5/weather', [
                    'query' => [
                        'q' => $localisation,
                        'appid' => $this->apiKey,
                        'units' => 'metric',
                        'lang' => 'fr',
                    ],
                    'timeout' => 5,
                ]);

                $data = $response->toArray();
                
                return [
                    'temperature' => round($data['main']['temp'], 1),
                    'humidity' => $data['main']['humidity'],
                    'description' => $data['weather'][0]['description'],
                    'icon' => $data['weather'][0]['icon'],
                    'windSpeed' => $data['wind']['speed'] ?? 0,
                    'pressure' => $data['main']['pressure'],
                    'updatedAt' => new \DateTime(),
                ];
            } catch (\Exception $e) {
                return null;
            }
        });
    }

    /**
     * Récupère les prévisions sur 5 jours
     */
    public function getForecast(string $localisation): ?array
    {
        $cacheKey = 'forecast_' . md5($localisation);
        
        return $this->cache->get($cacheKey, function ($item) use ($localisation) {
            $item->expiresAfter(3600); // Cache 1 heure

            try {
                $response = $this->httpClient->request('GET', 'https://api.openweathermap.org/data/2.5/forecast', [
                    'query' => [
                        'q' => $localisation,
                        'appid' => $this->apiKey,
                        'units' => 'metric',
                        'lang' => 'fr',
                    ],
                    'timeout' => 5,
                ]);

                $data = $response->toArray();
                
                // Agréger par jour (l'API retourne des données toutes les 3h)
                $dailyForecast = [];
                foreach ($data['list'] as $item) {
                    $date = date('Y-m-d', strtotime($item['dt_txt']));
                    if (!isset($dailyForecast[$date])) {
                        $dailyForecast[$date] = [
                            'date' => new \DateTime($item['dt_txt']),
                            'tempMin' => $item['main']['temp_min'],
                            'tempMax' => $item['main']['temp_max'],
                            'description' => $item['weather'][0]['description'],
                            'icon' => $item['weather'][0]['icon'],
                        ];
                    } else {
                        $dailyForecast[$date]['tempMin'] = min($dailyForecast[$date]['tempMin'], $item['main']['temp_min']);
                        $dailyForecast[$date]['tempMax'] = max($dailyForecast[$date]['tempMax'], $item['main']['temp_max']);
                    }
                }

                return array_slice(array_values($dailyForecast), 0, 5);
            } catch (\Exception $e) {
                return null;
            }
        });
    }

    /**
     * Vérifie si les conditions sont favorables pour la culture
     */
    public function isFavorableForCulture(array $weatherData, string $typeCulture = 'standard'): bool
    {
        $temp = $weatherData['temperature'] ?? null;
        
        if ($temp === null) return true; // Par défaut favorable si pas de données

        // Seuils selon le type de culture
        $seuils = [
            'standard' => ['min' => 10, 'max' => 35],
            'tropicale' => ['min' => 20, 'max' => 40],
            'fraiche' => ['min' => 5, 'max' => 25],
        ];

        $seuil = $seuils[$typeCulture] ?? $seuils['standard'];

        return $temp >= $seuil['min'] && $temp <= $seuil['max'];
    }
}