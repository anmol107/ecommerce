<?php

namespace UVDesk\CommunityPackages\UVDesk\ECommerce\Utils;

interface ECommercePlatformInterface
{
    public function getQualifiedName() : string;

    public function getName() : string;

    public function getDescription() : string;

    public function initialize(array $attributes = []) : ECommercePlatformInterface;

    public function createECommerceChannel(array $attributes) : ECommerceChannelInterface;
    
    public function updateECommerceChannel(array $attributes) : ECommerceChannelInterface;
    
    public function removeECommerceChannel(array $attributes) : ECommerceChannelInterface;

    public function getECommerceChannelCollection() : array;
}
