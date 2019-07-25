<?php

namespace UVDesk\CommunityPackages\UVDesk\ECommerce\Utils\Platforms\Shopify\Api;

/**
 * Read More: https://help.shopify.com/en/api/getting-started/authentication/oauth#verification
 */
abstract class Shop
{
    public static function get($shop_domain, $api_key, $api_password)
    {
        $curlHandler = curl_init();
        curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandler, CURLOPT_URL, "https://$shop_domain/admin/shop.json");
        curl_setopt($curlHandler, CURLOPT_HTTPHEADER, [
            'Accept: application/xml',
            'Content-Type: application/xml',
            'Authorization: Basic ' . base64_encode("$api_key:$api_password")
        ]);

        $curlResponse = curl_exec($curlHandler);
        curl_close($curlHandler);

        $jsonResponse = json_decode($curlResponse, true);

        if (empty($jsonResponse['shop'])) {
            throw new \Exception('Unable to retrieve store details');
        }

        return $jsonResponse['shop'];
    }
}
