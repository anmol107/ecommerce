<?php

namespace UVDesk\CommunityPackages\UVDesk\ECommerce\Utils\Platforms\BigCommerce\Api;

abstract class Shop
{
    public static function get($storeHash, $apiClientId, $apiToken)
    {

        $url = "https://api.bigcommerce.com/stores/".$storeHash."/v2/store";
        $curl = curl_init();
        curl_setopt_array( $curl, array (
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "accept: application/json",
                "content-type: application/json",
                "x-auth-client: ".$apiClientId,
                "x-auth-token: ".$apiToken

            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        $jsonResponse = json_decode($response, true);

        if( $jsonResponse['id']) {
            return $jsonResponse;
        } else {
            throw new \Exception('Unable to retrieve BigCommerce store details.');
        }

    }
}