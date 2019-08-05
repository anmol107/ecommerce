<?php

namespace UVDesk\CommunityPackages\UVDesk\ECommerce\Utils\Platforms;

use UVDesk\CommunityPackages\UVDesk\ECommerce\Utils\ECommerceChannelInterface;
use UVDesk\CommunityPackages\UVDesk\ECommerce\Utils\Platforms\OpenCart\Api\Shop;

class OpenCartECommerceChannel implements ECommerceChannelInterface
{
    const TEMPLATE = __DIR__ . "/../../../templates/configs/opencart/store-template.php";

    private $id;
    private $domain;
    private $apiKey;
    private $isEnabled = false;
    private $isVerified = false;
    private $verificationErrorMessage;

    public function __construct($id = null)
    {
        $this->id = $id;
    }

    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function getApiKey()
    {
        return $this->apiKey;
    }

    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
        return $this; 
    }


    public function getId()
    {
        return $this->id;
    }

    // public function setName($name)
    // {
    //     $this->domain = $name;

    //     return $this;
    // }

    public function getName()
    {
        return $this->domain;
    }


    public function setIsEnabled(bool $isEnabled)
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    public function getIsEnabled() : bool
    {
        return $this->isEnabled;
    }

    public function load() : bool
    {

        try {
            $response = Shop::get( $this->getDomain(), $this->getApiKey());
            $this->id = $response; //contains the api token
            return true;

        } catch (\Exception $e) {
            dump("exception caught in opencart->load()");
            dump($e);
            throw new \Exception('Error while loading OpenCart request.');
        }
        
        return false;
    }

    public function getVerificationErrorMessage() : ?string
    {
        return $this->verificationErrorMessage ?? null;
    }

    public function __toString()
    {
        $template = require self::TEMPLATE;

        return strtr($template, [
            '[[ id ]]' => $this->getId(),
            '[[ domain ]]' => $this->getDomain(),
            '[[ api_key ]]' => $this->getApiKey(),
            '[[ enabled ]]' => $this->getIsEnabled() ? 'true' : 'false',

        ]);
    }

    public function fetchECommerceOrderDetails(array $requestedOrderIds = [])
    {
        $orderCollection = [];
        $collectedOrders = ['validOrders' => [], 'invalidOrders' => []];

        foreach ($requestedOrderIds as $requestedOrderId) {
            // Get Order Details
            $orderInstance = [];
            $orderResponse = $this->getOrderResponse($requestedOrderId);
            if (!empty($orderResponse['order_id'])) {
                $orderCollection[] = ['order' => $orderResponse['order_id']];
                $collectedOrders['validOrders'][] = $requestedOrderId;
            } else {
                $collectedOrders['invalidOrders'][] = $requestedOrderId;
            }
        }

        return $this->formatOrderDetails($orderCollection, $collectedOrders);
    }

    private function getApiToken()
    {
        //gets the api token needed to access orders api for opencart
        $apiToken = null;
        $url = "http://".$this->getDomain()."/index.php?route=api/login";
        $key = "key=".$this->getApiKey();
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
        }
        return $apiToken;
    }

    private function getOrderResponse($orderId)
    {
        //get api token:
        $apiToken = $this->getApiToken();
        // dump('api token'); dump($apiToken); 
        if($apiToken == null)
        {
             throw new \Exception("OpenCart API Token is null.");
        
        } else {

            $url = "http://".$this->getDomain()."/index.php?route=api/order/info";
            $url.= "&api_token=".$apiToken."&order_id=".$orderId;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_HTTPHEADER => array(
                    "accept: application/json",
                    "content-type: application/json",
                ),
            ));
            $response = curl_exec($curl);
            $data = json_decode($response, true);
            // dump($data['order']); die; 
            if($data['order'])
            {
                // dump("data['order']"); dump($data['order']); dump("data"); dump($data);
                return $data['order'];
            } else {
                throw new \Exception("Unable to fetch OpenCart Order details.");
            }
        }

        return json_decode($response, true); 
    }


    private function getProductResponse($productId, $orderId)
    {
       
    }

    private function getOrderResources($url)
    {
      
    }


    public function formatOrderDetails($orderCollection, $collectedOrders)
    {
        // Format Response Data
        $formattedOrderDetails = ['orders' => []];


        foreach ($orderCollection as $orderInstance) {

            $orderDetails = $orderInstance['order'];
            $orderInstance = $this->getOrderResponse($orderDetails); 

            // Order Information ==== below here, orderdetils -> orderinstance
            $formattedOrderInstance = [
                'id' => $orderInstance['order_id'],
                'total_price' => implode(' ', [$orderInstance['currency_code'], $orderInstance['total']]),
            ];

            $orderPlacedTime = new \DateTime($orderInstance['date_added']);
            $formattedOrderInstance['order_details'] = [
                'Order Placed' => $orderPlacedTime->format('Y-m-d H:i:s'),
                'Order Status' => ucwords($orderInstance['order_status']),
            ];

            // Payment Information
            // Billing Address
 
            $billingAddressItems = [
                implode(' ', [$orderInstance['payment_firstname'], (!empty($orderInstance['payment_lastname']) ? $orderInstance['payment_lastname'] : '')]),
                implode(', ', [$orderInstance['payment_address_1'], (!empty($orderInstance['payment_address_2']) ? $orderInstance['payment_address_2'] : '')]),
                implode(', ', [$orderInstance['payment_city'], (!empty($orderInstance['payment_zone']) ? $orderInstance['payment_zone'] : '')]),
                implode(' ', [$orderInstance['payment_country'], (!empty($orderInstance['payment_postcode']) ? '(' . $orderInstance['payment_postcode'] . ')' : '')]),
            ];

            if (!empty($orderInstance['payment_method'])) {
                $formattedOrderInstance['payment_details']['Payment Method'] = ucwords($orderInstance['payment_method']);
            }
            $formattedOrderInstance['payment_details']['Payment Status'] = !empty($orderInstance['payment_status']) ? ucwords($orderInstance['payment_status']) : 'NA';
            $formattedOrderInstance['payment_details']['Payment Address'] = ucwords(implode('</br>', $billingAddressItems));

            // Shipping Information
            if (!empty($orderInstance['shipping_method'])) {
                // Shipping Address

                $shippingAddressItems = [
                    implode(' ', [$orderInstance['shipping_firstname'], (!empty($orderInstance['shipping_lastname']) ? $orderInstance['shipping_lastname'] : '')]),
                    implode(', ', [$orderInstance['shipping_address_1'], $orderInstance['shipping_address_2']]),
                    implode(', ', [$orderInstance['shipping_city'], (!empty($orderInstance['shipping_zone']) ? $orderInstance['shipping_zone'] : '')]),
                    implode(' ', [$orderInstance['shipping_country'], (!empty($orderInstance['shipping_postcode']) ? '(' . $orderInstance['shipping_postcode'] . ')' : '')]),
                ];

                $formattedOrderInstance['shipping_details'] = [
                    'Shipping Method' => (!empty($orderInstance['shipping_method']) ? ucwords($orderInstance['shipping_method']) : 'NA'),
                    'Shipping Address' => ucwords(implode('</br>', $shippingAddressItems)),
                ];
            }

            // // products details
            // if (!empty($orderInstance['products'])) {

            //     foreach ($productCollection as $orderItemInstance) {
            //         // Get Product Link
            //         // $productLink = (!empty($productResponse['custom_url']) ? $channelPath . '.mybigcommerce.com' . $productResponse['custom_url'] : '');

            //         $formattedOrderInstance['product_details'][] = [
            //             'title' => ucwords($orderItemInstance['name']),
            //             // 'link' => $productLink,
            //             'price' => implode(' ', [$orderInstance['currency_code'], $orderItemInstance['total_inc_tax']]),
            //             'quantity' => (int) floor($orderItemInstance['quantity']),
            //         ];
            //     }
            // }

            $formattedOrderDetails['orders'][] = $formattedOrderInstance;
        }

        return $formattedOrderDetails;
  
    }
}

