<?php

namespace UVDesk\CommunityPackages\UVDesk\ECommerce\Utils\Platforms\OpenCart\Api;

/**
 * Read More: https://help.shopify.com/en/api/getting-started/authentication/oauth#verification
 */
abstract class Shop
{
    public static function get($apiDomain, $apiKey)
    {
        //gets the api token needed to access orders api for opencart
        $apiToken = null;
        $url = "http://".$apiDomain."/index.php?route=api/login";
        $key = "key=".$apiKey;
        $params = array($key);
        $parameters = implode('&', $params);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
        $result = curl_exec($ch);
        $decoded = json_decode($result, true);
        if($decoded['api_token'])
        {
            $apiToken = $decoded['api_token'];
            return $apiToken;
        } else {
            throw new \Exception("Cannot access OpenCart API.");
        }
        
    }
}
