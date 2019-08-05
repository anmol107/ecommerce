<?php

namespace UVDesk\CommunityPackages\UVDesk\ECommerce\Utils\Platforms;

use UVDesk\CommunityPackages\UVDesk\ECommerce\Utils\ECommerceChannelInterface;
use UVDesk\CommunityPackages\UVDesk\ECommerce\Utils\Platforms\BigCommerce\Api\Shop;

class BigCommerceECommerceChannel implements ECommerceChannelInterface
{
    const TEMPLATE = __DIR__ . "/../../../templates/configs/bigcommerce/store-template.php";

    private $id;
    private $domain;
    private $storeHash;
    private $apiClientId;
    private $apiToken;
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

    public function getStoreHash()
    {
        return $this->storeHash;
    }

    public function setStoreHash($storeHash)
    {
        $this->storeHash = $storeHash;
        return $this;
    }

    public function getApiClientId()
    {
        return $this->apiClientId;
    }

    public function setApiClientId($apiClientId)
    {
        $this->apiClientId = $apiClientId;
        return $this;
    }

    public function getApiToken()
    {
        return $this->apiToken;
    }

    public function setApiToken($apiToken)
    {
        $this->apiToken = $apiToken;
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
            $response = Shop::get($this->getStoreHash(), $this->getApiClientId(), $this->getApiToken());
            $this->id = $response['id'];
            return true;

        } catch (\Exception $e) {
            throw new \Exception('Error while loading BigCommerce request.');
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
            '[[ store_hash ]]' => $this->getStoreHash(),
            '[[ api_token ]]' => $this->getApiToken(),
            '[[ api_client_id ]]' => $this->getApiClientId(),
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
 
            if (!empty($orderResponse['id'])) {
                $orderCollection[] = ['order' => $orderResponse['id']];
                $collectedOrders['validOrders'][] = $requestedOrderId;
            } else {
                $collectedOrders['invalidOrders'][] = $requestedOrderId;
            }
        }

        return $this->formatOrderDetails($orderCollection, $collectedOrders);
    }

    private function getOrderResponse($orderId)
    {
        $orderUrl = "https://api.bigcommerce.com/stores/".$this->getStoreHash()."/v2/orders/".$orderId; 

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $orderUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
              "accept: application/json",
              "content-type: application/json",
              "x-auth-client: ".$this->getApiClientId(),
              "x-auth-token: ".$this->getApiToken()
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        return json_decode($response, true); 
    }


    private function getProductResponse($productId, $orderId)
    {
        //path: https://api.bigcommerce.com/stores/{store_hash}/v2/orders/{order_id}/products/{id}
        $curlHandler = curl_init();
        curl_setopt($curlHandler, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandler, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'X-Auth-Client: ' . $this->getApiClientId(),
            'X-Auth-Token: ' . $this->getApiToken(),
        ]);

        // curl_setopt($curlHandler, CURLOPT_URL, $channelDetails['api_path'] . 'products/' . $productId);
        curl_setopt($curlHandler, CURLOPT_URL, "https://api.bigcommerce.com/stores/".$this->getStoreHash()."/v2/orders/".$orderId."/products/".$productId);
        $curlResponse = curl_exec($curlHandler);
        curl_close($curlHandler);

        return json_decode($curlResponse, true);
    }

    private function getOrderResources($url)
    {
        //used for shipping address and product details 
        $curlHandler = curl_init();
        curl_setopt($curlHandler, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandler, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'X-Auth-Client: ' . $this->getApiClientId(),
            'X-Auth-Token: ' . $this->getApiToken(),
        ]);
        curl_setopt($curlHandler, CURLOPT_URL, $url);
        $curlResponse = curl_exec($curlHandler);
        curl_close($curlHandler);
        return json_decode($curlResponse, true);
    }


    public function formatOrderDetails($orderCollection, $collectedOrders)
    {
        // dump($orderCollection); die; 
        // Format Response Data
        $formattedOrderDetails = ['orders' => []];


        foreach ($orderCollection as $orderInstance) {
            $orderDetails = $orderInstance['order'];
            // dump($orderDetails); die; -> this contains the order id.
            $orderInstance = $this->getOrderResponse($orderDetails); 
            // dump($orderInstance); die; -> this dumps an instance of order details .

            // Order Information ==== below here, orderdetils -> orderinstance
            $formattedOrderInstance = [
                'id' => $orderInstance['id'],
                'total_price' => implode(' ', [$orderInstance['currency_code'], $orderInstance['total_inc_tax']]),
            ];


            if (!empty($orderInstance['refunded_amount'])) {
                $formattedOrderInstance['total_refund'] = implode(' ', [$orderInstance['currency_code'], number_format((float) $orderInstance['refunded_amount'], 2, '.', '')]);
            }

            $orderPlacedTime = new \DateTime($orderInstance['date_created']);
            $formattedOrderInstance['order_details'] = [
                'Order Placed' => $orderPlacedTime->format('Y-m-d H:i:s'),
                'Order Status' => ucwords($orderInstance['status']),
            ];

            // Payment Information
            // Billing Address
            $billingAddress = $orderInstance['billing_address'];
            $billingAddressItems = [
                implode(' ', [$billingAddress['first_name'], (!empty($billingAddress['last_name']) ? $billingAddress['last_name'] : '')]),
                implode(', ', [$billingAddress['street_1'], (!empty($billingAddress['street_2']) ? $billingAddress['street_2'] : '')]),
                implode(', ', [$billingAddress['city'], (!empty($billingAddress['state']) ? $billingAddress['state'] : '')]),
                implode(' ', [$billingAddress['country'], (!empty($billingAddress['zip']) ? '(' . $billingAddress['zip'] . ')' : '')]),
            ];

            if (!empty($orderInstance['payment_method'])) {
                $formattedOrderInstance['payment_details']['Payment Method'] = ucwords($orderInstance['payment_method']);
            }
            $formattedOrderInstance['payment_details']['Payment Status'] = !empty($orderInstance['payment_status']) ? ucwords($orderInstance['payment_status']) : 'NA';
            $formattedOrderInstance['payment_details']['Payment Address'] = ucwords(implode('</br>', $billingAddressItems));

            // Shipping Information
            if (!empty($orderInstance['shipping_addresses']['url'])) {
                // Shipping Address
                $shippingDetails = $this->getOrderResources($orderInstance['shipping_addresses']['url']);
                $shippingDetails = $shippingDetails[0];

                $shippingAddressItems = [
                    implode(' ', [$shippingDetails['first_name'], (!empty($shippingDetails['last_name']) ? $shippingDetails['last_name'] : '')]),
                    implode(', ', [$shippingDetails['street_1'], $shippingDetails['street_2']]),
                    implode(', ', [$shippingDetails['city'], (!empty($shippingDetails['state']) ? $shippingDetails['state'] : '')]),
                    implode(' ', [$shippingDetails['country'], (!empty($shippingDetails['zip']) ? '(' . $shippingDetails['zip'] . ')' : '')]),
                ];

                $formattedOrderInstance['shipping_details'] = [
                    'Shipping Method' => (!empty($shippingDetails['shipping_method']) ? ucwords($shippingDetails['shipping_method']) : 'NA'),
                    'Shipping Cost' => (!empty($shippingDetails['cost_inc_tax']) ? $orderInstance['currency_code'] . ' ' . $shippingDetails['cost_inc_tax'] : 'NA'),
                    'Shipping Address' => ucwords(implode('</br>', $shippingAddressItems)),
                ];
            }

            // products details
            if (!empty($orderInstance['products']['url'])) {
                $productCollection = $this->getOrderResources($orderInstance['products']['url']);

                foreach ($productCollection as $orderItemInstance) {
                    // Get Product Link

                    $formattedOrderInstance['product_details'][] = [
                        'title' => ucwords($orderItemInstance['name']),
                        'price' => implode(' ', [$orderInstance['currency_code'], $orderItemInstance['total_inc_tax']]),
                        'quantity' => (int) floor($orderItemInstance['quantity']),
                    ];
                }
            }

            $formattedOrderDetails['orders'][] = $formattedOrderInstance;
        }

        return $formattedOrderDetails;
    }
}

