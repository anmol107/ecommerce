<?php

namespace UVDesk\CommunityPackages\UVDesk\ECommerce;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use UVDesk\CommunityPackages\UVDesk\ECommerce\Utils\ECommerceConfiguration;
use UVDesk\CommunityPackages\UVDesk\ECommerce\Utils\ECommercePlatformInterface;
use Webkul\UVDesk\ExtensionFrameworkBundle\Definition\Package\ConfigurablePackage;
use UVDesk\CommunityPackages\UVDesk\ECommerce\DependencyInjection\PackageConfiguration;
use Webkul\UVDesk\ExtensionFrameworkBundle\Definition\Package\ConfigurablePackageInterface;
use Webkul\UVDesk\ExtensionFrameworkBundle\Definition\Package\ContainerBuilderAwarePackageInterface;

class ECommercePackage extends ConfigurablePackage implements ConfigurablePackageInterface, ContainerBuilderAwarePackageInterface
{
    public function getConfiguration() : ?ConfigurationInterface
    {
        return new PackageConfiguration();
    }

    public function process(ContainerBuilder $container)
    {
        if ($container->has(ECommerceConfiguration::class)) {
            $eCommerceConfiguration = $container->findDefinition(ECommerceConfiguration::class);

            foreach ($container->findTaggedServiceIds(ECommercePlatformInterface::class) as $reference => $tags) {
                $eCommerceConfiguration->addMethodCall('addECommercePlatform', array(new Reference($reference), $tags));
            }
        }
    }

    public function install() : void
    {
        $template = file_get_contents(__DIR__ . "/../templates/configs/defaults.yaml");

        $this->updatePackageConfiguration($template);
    }
}
