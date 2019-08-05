<?php

namespace UVDesk\CommunityPackages\UVDesk\ECommerce\Utils\Platforms;

use UVDesk\CommunityPackages\UVDesk\ECommerce\Utils\ECommerceChannelInterface;
use UVDesk\CommunityPackages\UVDesk\ECommerce\Utils\Platforms\Magento\Api\Shop;
use SoapClient; 
class MagentoECommerceChannel implements ECommerceChannelInterface
{
    const TEMPLATE = __DIR__ . "/../../../templates/configs/magento/store-template.php";

    private $id;
    private $domain;
    private $apiUsername;
    private $apiPassword;
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

    public function getApiUsername()
    {
        return $this->apiUsername;
    }

    public function setApiUsername($apiUsername)
    {
        $this->apiUsername = $apiUsername;
        return $this;
    }

    public function getApiPassword()
    {
        return $this->apiPassword;
    }

    public function setApiPassword($apiPassword)
    {
        $this->apiPassword = $apiPassword;
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
            $response = Shop::get($this->getDomain(), $this->getApiUsername(), $this->getApiPassword());
            $this->id = $response['store_id'];
            return true;

        } catch (\Exception $e) {

            throw new \Exception('Error while loading Magento request.');
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
            '[[ api_username ]]' => $this->getApiUsername(),
            '[[ api_password ]]' => $this->getApiPassword(),
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

            // if (!empty($orderResponse['orders'])) {   orderResponse['orders'] not working, use orderResponse['order_id']
            if (!empty($orderResponse['increment_id'])) { 
                $orderCollection[] = ['order' => $orderResponse['increment_id']];
                $collectedOrders['validOrders'][] = $requestedOrderId;
            } else {
                $collectedOrders['invalidOrders'][] = $requestedOrderId;
            }
        }

        return $this->formatOrderDetails($orderCollection, $collectedOrders);
    }

    private function getOrderResponse($orderId)
    {

        $url = "http://".$this->getDomain()."/api/soap?wsdl"; 
        $client = new SoapClient($url);   
        $session = $client->login($this->getApiUsername(), $this->getApiPassword());
        $result = $client->call($session, 'sales_order.info', $orderId);
        //no need to json encode, already in array format.
        return $result;
         
    }


    private function getProductResponse($productId)
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


    public function formatOrderDetails($orderCollection, $collectedOrders)
    {
        // Format Response Data
        $formattedOrderDetails = ['orders' => []];


        foreach ($orderCollection as $orderInstance) {
            $orderDetails = $orderInstance['order'];
            // dump($orderDetails); die; -> this contains the order id.
            $orderInstance = $this->getOrderResponse($orderDetails); 
            // dump($orderInstance); die; -> this dumps an instance of order details .
            
            // Order Information ==== below here, orderdetails -> orderinstance
            $formattedOrderInstance = [
                'id' => $orderInstance['increment_id'],
                'total_price' => implode(' ', [$orderInstance['global_currency_code'], $orderInstance['grand_total']]),
            ];


            if (!empty($orderInstance['payment']['amount_refunded'])) {
                $formattedOrderInstance['total_refund'] = implode(' ', [$orderInstance['global_currency_code'], number_format((float) $orderInstance['payment']['amount_refunded'], 2, '.', '')]);
            }

            $orderPlacedTime = new \DateTime($orderInstance['created_at']);
            $formattedOrderInstance['order_details'] = [
                'Order Placed' => $orderPlacedTime->format('Y-m-d H:i:s'),
                'Order Status' => ucwords($orderInstance['status']),
            ];

            // Payment Information
            // Billing Address
            $billingAddress = $orderInstance['billing_address'];
            $billingAddressItems = [
                implode(' ', [$billingAddress['firstname'], (!empty($billingAddress['lastname']) ? $billingAddress['lastname'] : '')]),
                implode(', ', [$billingAddress['street'], (!empty($billingAddress['street2']) ? $billingAddress['street2'] : '')]),
                implode(', ', [$billingAddress['city'], (!empty($billingAddress['state']) ? $billingAddress['state'] : '')]),
                implode(' ', [$billingAddress['country_id'], (!empty($billingAddress['postcode']) ? '(' . $billingAddress['postcode'] . ')' : '')]),
            ];

            if (!empty($orderInstance['payment'])) {
                $formattedOrderInstance['payment_details']['Payment Method'] = ucwords($orderInstance['payment']['method']);
            }

            //could not find payment_status field in order detail data array.
            // $formattedOrderInstance['payment_details']['Payment Status'] = !empty($orderInstance['payment_status']) ? ucwords($orderInstance['payment_status']) : 'NA';
            $formattedOrderInstance['payment_details']['Payment Address'] = ucwords(implode('</br>', $billingAddressItems));

            // Shipping Information
            if (!empty($orderInstance['shipping_address'])) {
                // Shipping Address

                $shippingAddressItems = [
                    implode(' ', [$orderInstance['shipping_address']['firstname'], (!empty($orderInstance['shipping_address']['lastname']) ? $orderInstance['shipping_address']['lastname'] : '')]),
                    implode(', ', [$orderInstance['shipping_address']['street']]),
                    implode(', ', [$orderInstance['shipping_address']['city'], (!empty($orderInstance['shipping_address']['region']) ? $orderInstance['shipping_address']['region'] : '')]),
                    implode(' ', [$orderInstance['shipping_address']['country_id'], (!empty($orderInstance['shipping_address']['postcode']) ? '(' . $orderInstance['shipping_address']['postcode'] . ')' : '')]),
                ];

                $formattedOrderInstance['shipping_details'] = [
                    // 'Shipping Method' => (!empty($shippingDetails['shipping_method']) ? ucwords($shippingDetails['shipping_method']) : 'NA'),
                    'Shipping Cost' => (!empty($orderInstance['shipping_amount']) ? $orderInstance['global_currency_code'] . ' ' . $orderInstance['shipping_amount'] : 'NA'),
                    'Shipping Address' => ucwords(implode('</br>', $shippingAddressItems)),
                ];
            }

            // products details
            if (!empty($orderInstance['items'])) {

                foreach ($orderInstance['items'] as $orderItemInstance) {
                    // Get Product Link
                    $formattedOrderInstance['product_details'][] = [
                        'title' => ucwords($orderItemInstance['name']),
                        'price' => implode(' ', [$orderInstance['global_currency_code'], $orderItemInstance['base_row_total_incl_tax']]),
                        'quantity' => (int) floor($orderItemInstance['qty_ordered']),
                    ];
                }
            }

            $formattedOrderDetails['orders'][] = $formattedOrderInstance;
        }

        return $formattedOrderDetails;
    }
}

