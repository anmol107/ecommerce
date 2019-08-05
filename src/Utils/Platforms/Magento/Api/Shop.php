<?php

namespace UVDesk\CommunityPackages\UVDesk\ECommerce\Utils\Platforms\Magento\Api;

use SoapClient;

abstract class Shop
{
    public static function get($domain, $username, $pw)
    {
        // http://127.0.0.1/magento/api/soap/?wsdl"  http://127.0.0.1/magento/api/soap/?wsdl" 127.0.0.1/magento
        $url = "http://".$domain."/api/soap?wsdl"; 
        $client = new SoapClient($url);   
        $session = $client->login($username, $pw);

        // to get info on a specific store: $result = $client->call($session, 'store.info', $storeId);
        $data = $client->call($session, 'store.list');
        $result = $data[0];
        dump($result); 
       if($result['store_id']){
           return $result;
       } else {
            throw new \Exception('Unable to retrieve Magento store details.');
       }
    }
}