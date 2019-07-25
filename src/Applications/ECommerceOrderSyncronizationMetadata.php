<?php

namespace UVDesk\CommunityPackages\UVDesk\ECommerce\Applications;

use Webkul\UVDesk\ExtensionFrameworkBundle\Definition\Application\ApplicationMetadata;

class ECommerceOrderSyncronizationMetadata extends ApplicationMetadata
{
    public function getName() : string
    {
        return "ECommerce Order Syncronization";
    }

    public function getSummary() : string
    {
        return "Import ecommerce order details to your support tickets from different available platforms";
    }

    public function getDescription() : string
    {
        return "Improve the efficiency of your support staff by displaying the order related details on the ticket system. It reduces the time spent by the support staff by fetching the order related details on the ticket system only. No need to leave ticket system to check the details.";
    }

    public function getQualifiedName() : string
    {
        return "order-syncronization";
    }

    public function getDashboardTemplate() : string
    {
        return '@_uvdesk_extension_uvdesk_ecommerce/apps/order-syncronization/dashboard.html.twig';
    }
}
