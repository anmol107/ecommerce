<?php

namespace UVDesk\CommunityPackages\UVDesk\ECommerce\Utils\Platforms;

use UVDesk\CommunityPackages\UVDesk\ECommerce\Utils\ECommerceChannelInterface;
use UVDesk\CommunityPackages\UVDesk\ECommerce\Utils\Platforms\Shopify\Api\Shop;

class ShopifyECommerceChannel implements ECommerceChannelInterface
{
    const TEMPLATE = __DIR__ . "/../../../templates/configs/shopify/store-template.php";

    private $id;
    private $name;
    private $domain;
    private $client;
    private $password;
    private $timezone;
    private $ianaTimezone;
    private $currencyFormat;
    private $isEnabled = false;
    private $isVerified = false;
    private $verificationErrorMessage;

    public function __construct($id = null)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
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

    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function getTimezone()
    {
        return $this->timezone;
    }

    public function setIanaTimezone($ianaTimezone)
    {
        $this->ianaTimezone = $ianaTimezone;

        return $this;
    }

    public function getIanaTimezone()
    {
        return $this->ianaTimezone;
    }

    public function setCurrencyFormat($currencyFormat)
    {
        $this->currencyFormat = $currencyFormat;

        return $this;
    }

    public function getCurrencyFormat()
    {
        return $this->currencyFormat;
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
            $response = Shop::get($this->getDomain(), $this->getClient(), $this->getPassword());

            $this->id = $response['id'];
            $this->name = $response['name'];
            $this->timezone = $response['timezone'];
            $this->ianaTimezone = $response['iana_timezone'];
            $this->currencyFormat = $response['money_with_currency_in_emails_format'];

            return true;
        } catch (\Exception $e) {
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
            '[[ name ]]' => $this->getName(),
            '[[ client ]]' => $this->getClient(),
            '[[ password ]]' => $this->getPassword(),
            '[[ enabled ]]' => $this->getIsEnabled() ? 'true' : 'false',
            '[[ timezone ]]' => $this->getTimezone(),
            '[[ iana_timezone ]]' => $this->getIanaTimezone(),
            '[[ currency_format ]]' => $this->getCurrencyFormat(),
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

            if (!empty($orderResponse['orders'])) {
                $orderCollection[] = ['order' => $orderResponse['orders']];
                $collectedOrders['validOrders'][] = $requestedOrderId;
            } else {
                $collectedOrders['invalidOrders'][] = $requestedOrderId;
            }
        }

        return $this->formatOrderDetails($orderCollection, $collectedOrders);
    }

    private function getOrderResponse($orderId)
    {
        $curlHandler = curl_init();
        curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandler, CURLOPT_URL, 'https://' . $this->getDomain() . '/admin/orders.json?name=' . $orderId . '&status=any');
        curl_setopt($curlHandler, CURLOPT_HTTPHEADER, [
            'Accept: application/xml',
            'Content-Type: application/xml',
            'Authorization: Basic ' . base64_encode($this->getClient() . ':' . $this->getPassword())
        ]);

        $curlResponse = curl_exec($curlHandler);
        curl_close($curlHandler);

        return json_decode($curlResponse, true);
    }

    private function getProductResponse($productId)
    {
        $curlHandler = curl_init();
        curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandler, CURLOPT_URL, 'https://' . $this->getDomain() . '.myshopify.com/admin/products.json?ids=' . $productId);
        curl_setopt($curlHandler, CURLOPT_HTTPHEADER, [
            'Accept: application/xml',
            'Content-Type: application/xml',
            'Authorization: Basic ' . base64_encode($this->getClient() . ':' . $this->getPassword())
        ]);

        $curlResponse = curl_exec($curlHandler);
        curl_close($curlHandler);

        return json_decode($curlResponse, true);
    }

    public function formatOrderDetails($orderCollection, $collectedOrders)
    {
        // Format Data
        $formattedOrderDetails = ['orders' => []];

        foreach ($orderCollection as $orderInstance) {
            $orderDetails = $orderInstance['order'];

            foreach ($orderDetails as $orderItem) {
                // Order Information
                $formattedOrderInstance = [
                    'id' => $orderItem['order_number'],
                    'total_price' => implode(' ', [$orderItem['currency'], $orderItem['total_price']]),
                ];

                if (!empty($orderItem['refunds'])) {
                    $formattedOrderInstance['total_refund'] = implode(' ', [$orderItem['currency'], number_format((float) $orderItem['refunds'][0]['transactions'][0]['amount'], 2, '.', '')]);
                }

                $orderPlacedTime = new \DateTime($orderItem['created_at']);
                $orderPlacedTime->setTimeZone(new \DateTimeZone('UTC'));
                $formattedOrderInstance['order_details']['Order Placed'] = $orderPlacedTime->format('Y-m-d H:i:s');
                // $formattedOrderInstance['order_details']['Order Closing Date'] = !empty($orderItem['closed_at']) ? $orderItem['closed_at'] : 'Not closed';

                // Order Cancellation Status
                if (!empty($orderItem['cancelled_at'])) {
                    $formattedOrderInstance['order_details']['Order Cancellation Status'] = 'Cancelled';
                    $formattedOrderInstance['order_details']['Order Cancellation Date'] = $orderItem['cancelled_at'];
                    $formattedOrderInstance['order_details']['Order Cancellation Reason'] = $orderItem['cancel_reason'];
                } else {
                    $formattedOrderInstance['Order Cancellation Status'] = 'Not Cancelled';
                }

                // Payment Information
                $formattedOrderInstance['payment_details'] = [
                    'Payment Status' => ucwords($orderItem['financial_status']),
                    'Order Processing Method' => ucwords($orderItem['processing_method']),
                    'Order Payment Gateways' => (!empty($orderItem['payment_gateway_names']) ? ucwords(implode(', ', $orderItem['payment_gateway_names'])) : 'NA'),
                    'Order Fulfillment Status' => ($orderItem['fulfillment_status'] == 'fulfilled') ? 'Fulfilled' : (($orderItem['fulfillment_status'] == 'partial') ? 'Partial' : 'Pending'),
                ];

                // Customer Details
                // if (!empty($orderItem['customer'])) {
                //     $customerDetails = $orderItem['customer'];
                //     $formattedOrderInstance['Customer ID'] = $customerDetails['id'];
                //     $formattedOrderInstance['Customer Name'] = $customerDetails['first_name'] . ' ' . $customerDetails['last_name'];
                //     $formattedOrderInstance['Customer Email'] = $customerDetails['email'];
                //     $formattedOrderInstance['Customer Phone'] = $customerDetails['phone'];
                // }

                // Shipping Address
                if (!empty($orderItem['shipping_address'])) {
                    $shippingDetails = $orderItem['shipping_address'];
                    $shippingAddressItems = [
                        $shippingDetails['name'],
                        implode(', ', [$shippingDetails['address1'], (!empty($shippingDetails['address2']) ? $shippingDetails['address2'] : '')]) . ', ' . $shippingDetails['city'] . (!empty($shippingDetails['province']) ? ', ' . $shippingDetails['province'] : ''),
                        $shippingDetails['country_code'],
                    ];

                    $formattedOrderInstance['shipping_details']['Shipping Address'] = implode('</br>', $shippingAddressItems);
                } else {
                    $formattedOrderInstance['shipping_details']['Shipping Address'] = 'NA';
                }

                // Order Items
                foreach ($orderItem['line_items'] as $orderItemInstance) {
                    $productResponse = $this->getProductResponse($orderItemInstance['product_id']);
                    if (!empty($productResponse['products'][0]['handle'])) {
                        $productLink = 'https://' . $this->getDomain() .  '/products/' . $productResponse['products'][0]['handle'];
                    } else {
                        $productLink = '';
                    }

                    $formattedOrderInstance['product_details'][] = [
                        'title' => ucwords($orderItemInstance['title']),
                        'link' => !empty($productLink) ? $productLink : '',
                        'price' => implode(' ', [$orderItem['currency'], $orderItemInstance['price']]),
                        'quantity' => (int) floor($orderItemInstance['quantity']),
                    ];
                }
            }

            $formattedOrderDetails['orders'][] = $formattedOrderInstance;
        }

        return $formattedOrderDetails;
    }
}
