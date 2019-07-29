<p align="center"><a href="https://www.uvdesk.com/en/" target="_blank">
    <img src="https://s3-ap-southeast-1.amazonaws.com/cdn.uvdesk.com/uvdesk/bundles/webkuldefault/images/uvdesk-wide.svg">
</a></p>

UVDesk Community ECommerce Package
--------------

The eCommerce package is a free uvdesk community extension which brings various ecommerce related functionalities to your community helpdesk system.

This package allows integrators/developers to integrate any number of ecommerce stores across different platforms with your helpdesk system. Integrating with such ecommerce platforms enables support agents to seamlessly fetch order details and integrate it with tickets enabling them to provide support efficiently without them having to leave the helpdesk system.

**Supported Platforms**

Currently, the package provides integrations with the following platforms by default:

- Shopify
- Prestashop (coming soon)
- Opencart (coming soon)
- Magento (coming soon)
- Magento 2 (coming soon)
- BigCommerce (coming soon)
- WooCommerce (coming soon)

We plan on adding more platforms to our default list. However, developers can easily create integrations with their own platform of liking which will be described in our documentation (comming soon).

Installation
--------------

To add this package to your helpdesk system, simply clone this package to the *apps/uvdesk/ecommerce* directory relative to your project's root.

Once you've copied the package to the specified directory, run the following command from your project's root:

```bash
$ php bin/console uvdesk_extensions:configure-extensions
$ php bin/console assets:install
$ php bin/console doctrine:migrations:diff
$ php bin/console doctrine:migrations:migrate
```

This command will automatically search and configure any available packages found within the apps directory. Once your packages have been configured successfully, they are ready for use.