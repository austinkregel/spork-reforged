<?php

declare(strict_types=1);

namespace App\Services\Geocoding;

use App\Contracts\Services\GeocodingServiceContract;
use GuzzleHttp\Client;

class GoogleMapsGeocodingService implements GeocodingServiceContract
{
    public function geocode(string $address): array
    {
        $client = new Client(); // GuzzleHttp\Client
        $baseURL = 'https://maps.googleapis.com/maps/api/geocode/json?address=';
        $addressURL = urlencode($address).'&key='.env('GOOGLE_MAPS_API_KEY');
        $url = $baseURL.$addressURL;
        $response = $client->request('GET', $url)->getBody()->getContents();
        $response = json_decode($response);

        if ($response->status === 'ZERO_RESULTS') {
            return ['latitude' => null, 'longitude' => null, 'address' => $address];
        }

        try {
            $latitude = $response->results[0]->geometry->location->lat;
            $longitude = $response->results[0]->geometry->location->lng;

            $address = $response->results[0]->formatted_address;

            return compact('latitude', 'longitude', 'address');
        } catch (\Throwable $e) {
            info('Failed to gecode '.$address, [
                'address' => $address,
                'exception' => $e,
            ]);

            dd($response, $address);
        }
    }
    public function findBusinesses(string $name): array
    {
        $client = new Client(); // GuzzleHttp\Client
        $baseURL = 'https://maps.googleapis.com/maps/api/place/textsearch/json';
        $baseQuery = sprintf('?fields=formatted_address,name,rating,opening_hours,geometry&query=%s&inputtype=textquery&radius=321869&location=michigan', urlencode($name));
        $addressURL = $baseURL.$baseQuery.'&key='.env('GOOGLE_MAPS_API_KEY');
        $resultSet = [];
        do {
            $response = cache()->remember($addressURL, now()->addDay(), fn() => $client->request('GET', $addressURL)->getBody()->getContents());
            $response = json_decode($response);

            if ($response->status === 'ZERO_RESULTS') {
                return [];
            }
            foreach ($response->results as $result) {
                $resultSet[] = $result;
            }
            if (isset($response->next_page_token)) {
                $addressURL = $baseURL.'?key='.env('GOOGLE_MAPS_API_KEY').'&pagetoken='.$response->next_page_token;
            }
        } while (isset($response->next_page_token));

        return $resultSet;
    }
}
