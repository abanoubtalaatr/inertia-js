<?php

namespace App\Helpers;

class AddressHelper
{
    public static function getAddressFromLatLong($latitude, $longitude)
    {

        $cachedAddress = cache()->get("address_{$latitude}_{$longitude}");
        if ($cachedAddress) {
            return $cachedAddress;
        }
        $apiKey = 'sasas';

        $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=$latitude,$longitude&key=$apiKey";

        $response = file_get_contents($url);

        $json = json_decode($response, true);

        if ($json['status'] === 'OK') {
            $address = $json['results'][0]['formatted_address'];
            //  dd($address);
            cache()->put("address_{$latitude}_{$longitude}", $address, 86400); // تخزين لمدة يوم

            return $address;
        } else {
            //  dd($json);
        }

        return null;
    }
}
