<?php

namespace UVDesk\CommunityPackages\UVDesk\ECommerce\Routing;

use Webkul\UVDesk\ExtensionFrameworkBundle\Definition\Routing\ExposedRoutingResourceInterface;

class ExposedRoutingResource implements ExposedRoutingResourceInterface
{
    public static function getResourcePath()
    {
        return __DIR__ . "/../Resources/config/routes/public.yaml";
    }

    public static function getResourceType()
    {
        return ExposedRoutingResourceInterface::YAML_RESOURCE;
    }
}
