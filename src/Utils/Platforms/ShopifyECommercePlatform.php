<?php

namespace UVDesk\CommunityPackages\UVDesk\ECommerce\Utils\Platforms;

use UVDesk\CommunityPackages\UVDesk\ECommerce\Utils\ECommerceChannelInterface;
use UVDesk\CommunityPackages\UVDesk\ECommerce\Utils\ECommercePlatformInterface;

class ShopifyECommercePlatform implements ECommercePlatformInterface
{
    const TEMPLATE = __DIR__ . "/../../../templates/configs/shopify/template.php";

    private $collection = [];

    public function getQualifiedName() : string
    {
        return 'shopify';
    }

    public function getName() : string
    {
        return 'Shopify';
    }

    public function getDescription() : string
    {
        return 'Shopify description';
    }

    public function initialize(array $attributes = []) : ECommercePlatformInterface
    {
        foreach ($attributes['channels'] as $attributes) {
            ($eCommerceChannel = new ShopifyECommerceChannel($attributes['id']))
                ->setName($attributes['name'])
                ->setDomain($attributes['domain'])
                ->setClient($attributes['client'])
                ->setPassword($attributes['password'])
                ->setTimezone($attributes['timezone'])
                ->setIanaTimezone($attributes['iana_timezone'])
                ->setCurrencyFormat($attributes['currency_format'])
                ->setIsEnabled($attributes['enabled']);
            
            $this->collection[$attributes['domain']] = $eCommerceChannel;
        }

        return $this;
    }

    public function createECommerceChannel(array $attributes) : ECommerceChannelInterface
    {
        ($eCommerceChannel = new ShopifyECommerceChannel())
            ->setDomain($attributes['domain'])
            ->setClient($attributes['api_key'])
            ->setPassword($attributes['api_password'])
            ->setIsEnabled((bool) $attributes['is_enabled']);

        if (false == $eCommerceChannel->load()) {
            throw new \Exception('An error occurred while verifying your credentials. Please check the entered details.');
        }

        $this->collection[$attributes['domain']] = $eCommerceChannel;

        return $eCommerceChannel;
    }

    public function updateECommerceChannel(array $attributes) : ECommerceChannelInterface
    {
        ($eCommerceChannel = new ShopifyECommerceChannel())
            ->setDomain($attributes['domain'])
            ->setClient($attributes['api_key'])
            ->setPassword($attributes['api_password'])
            ->setIsEnabled((bool) $attributes['is_enabled']);
        
        if (false == $eCommerceChannel->load()) {
            throw new \Exception('An error occurred while verifying your credentials. Please check the entered details.');
        }

        $this->collection[$attributes['domain']] = $eCommerceChannel;

        return $eCommerceChannel;
    }

    public function removeECommerceChannel(array $attributes) : ECommerceChannelInterface
    {
        $eCommerceChannel = $this->collection[$attributes['domain']];
        
        unset($this->collection[$attributes['domain']]);
        
        return $eCommerceChannel;
    }

    public function getECommerceChannel($id) : ?ECommerceChannelInterface
    {
        foreach ($this->collection as $eCommerceChannel) {
            if ($eCommerceChannel->getId() == $id) {
                return $eCommerceChannel;
            }
        }

        return null;
    }

    public function getECommerceChannelCollection() : array
    {
        return array_values($this->collection);
    }

    public function __toString()
    {
        if (!empty($this->collection)) {
            $stream = array_reduce($this->collection, function($stream, $eCommerceChannel) {
                return $stream . (string) $eCommerceChannel;
            }, '');
    
            return strtr(require self::TEMPLATE, [
                '[[ STORES ]]' => $stream,
            ]);
        }

        return file_get_contents(__DIR__ . "/../../../templates/configs/defaults.yaml");
    }
}
