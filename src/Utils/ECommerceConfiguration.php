<?php

namespace UVDesk\CommunityPackages\UVDesk\ECommerce\Utils;

use UVDesk\CommunityPackages\UVDesk\ECommerce\ECommercePackage;

class ECommerceConfiguration
{
    const DEFAULT_TEMPLATE = __DIR__ . "/../../templates/configs/defaults.yaml";
    const TEMPLATE = __DIR__ . "/../../templates/configs/template.php";

    private $eCommercePlatforms = [];

    public function addECommercePlatform(ECommercePlatformInterface $eCommercePlatform, array $tags = [])
    {
        $this->eCommercePlatforms[$eCommercePlatform->getQualifiedName()] = $eCommercePlatform;

        return $this;
    }

    public function getECommercePlatforms()
    {
        return $this->eCommercePlatforms;
    }

    public function getECommercePlatformByQualifiedName($qualifiedName)
    {
        return $this->eCommercePlatforms[$qualifiedName] ?? null;
    }

    public function __toString()
    {
        if (!empty($this->eCommercePlatforms)) {
            $stream = array_reduce($this->eCommercePlatforms, function($stream, $eCommercePlatform) {
                return $stream . (string) $eCommercePlatform;
            }, '');

            if (trim($stream) != 'uvdesk_ecommerce: ~') {
                return strtr(require self::TEMPLATE, [
                    '[[ PLATFORMS ]]' => $stream,
                ]);
            }
        }

        return file_get_contents(self::DEFAULT_TEMPLATE);
    }
}
