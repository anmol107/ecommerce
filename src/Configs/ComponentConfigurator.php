<?php

namespace UVDesk\CommunityPackages\UVDesk\ECommerce\Configs;

use UVDesk\CommunityPackages\UVDesk\ECommerce\ECommercePackage;
use UVDesk\CommunityPackages\UVDesk\ECommerce\Utils\ECommerceConfiguration;

class ComponentConfigurator
{
    public function __construct(ECommercePackage $eCommercePackage)
    {
        $this->eCommercePackage = $eCommercePackage;
    }

    public function prepareECommerceConfiguration(ECommerceConfiguration $eCommerceConfiguration)
    {
        foreach ($this->eCommercePackage->getConfigurationParameters() as $id => $attributes) {
            $eCommercePlatform = $eCommerceConfiguration->getECommercePlatformByQualifiedName($id);

            if (!empty($eCommercePlatform)) {
                $eCommercePlatform->initialize($attributes);
            }
        }
    }
}
