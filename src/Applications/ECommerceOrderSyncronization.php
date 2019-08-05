<?php

namespace UVDesk\CommunityPackages\UVDesk\ECommerce\Applications;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Webkul\UVDesk\ExtensionFrameworkBundle\Application\Routine\ApiRoutine;
use UVDesk\CommunityPackages\UVDesk\ECommerce\Utils\ECommerceConfiguration;
use Webkul\UVDesk\ExtensionFrameworkBundle\Definition\Application\Application;
use Webkul\UVDesk\ExtensionFrameworkBundle\Application\Routine\RenderDashboardRoutine;
use Webkul\UVDesk\ExtensionFrameworkBundle\Definition\Application\ApplicationMetadata;
use Webkul\UVDesk\ExtensionFrameworkBundle\Definition\Application\ApplicationInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application as ConsoleApplication;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class ECommerceOrderSyncronization extends Application implements ApplicationInterface, EventSubscriberInterface
{
    private $eCommerceConfiguration;

    public function __construct(KernelInterface $kernel, ECommerceConfiguration $eCommerceConfiguration)
    {
        $this->kernel = $kernel;
        $this->eCommerceConfiguration = $eCommerceConfiguration;
    }

    public static function getMetadata() : ApplicationMetadata
    {
        return new ECommerceOrderSyncronizationMetadata();
    }

    public static function getSubscribedEvents()
    {
        return array(
            ApiRoutine::getName() => array(
                array('handleApiRequest'),
            ),
            RenderDashboardRoutine::getName() => array(
                array('prepareDashboard'),
            ),
        );
    }

    public function prepareDashboard(RenderDashboardRoutine $event)
    {
        $dashboard = $event->getDashboardTemplate();

        // Add loadable resources to templates
        $dashboard->appendJavascript('bundles/uvdeskextensionframework/extensions/uvdesk/ecommerce/js/main.js');
        $dashboard->appendStylesheet('bundles/uvdeskextensionframework/extensions/uvdesk/ecommerce/css/main.css');

        // Configure dashboard
        $event
            ->setTemplateReference('@_uvdesk_extension_uvdesk_ecommerce/apps/order-syncronization/dashboard.html.twig')
            ->addTemplateData('configuration', $this->eCommerceConfiguration);
    }

    public function handleApiRequest(ApiRoutine $event)
    {
        $request = $event->getRequest();

        switch ($request->query->get('endpoint')) {
            case 'get-stores':
                $response = ['platforms' => []];
               
                foreach ($this->eCommerceConfiguration->getECommercePlatforms() as $eCommercePlatform) {
                    $response['platforms'][$eCommercePlatform->getQualifiedName()] = [
                        'title' => $eCommercePlatform->getName(),
                        'description' => $eCommercePlatform->getDescription(),
                        // 'channels' => array_map(function ($eCommerceChannel)  {
                        'channels' => array_map(function ($eCommerceChannel) use (&$eCommercePlatform) {

                            switch($eCommercePlatform->getQualifiedName()){
                            
                                case 'shopify':
                                    return [
                                        'id' => $eCommerceChannel->getId(),
                                        'name' => $eCommerceChannel->getName(),
                                        'domain' => $eCommerceChannel->getDomain(),
                                        'apiKey' => $eCommerceChannel->getClient(),
                                        'apiPassword' => $eCommerceChannel->getPassword(),
                                        'enabled' => $eCommerceChannel->getIsEnabled()
                                    ];
                                break;
    
                                case 'bigcommerce': 
                                    return [
                                            'id' => $eCommerceChannel->getId(),
                                            'domain' => $eCommerceChannel->getDomain(),
                                            'store_hash' => $eCommerceChannel->getStoreHash(),
                                            'api_client_id' => $eCommerceChannel->getApiClientId(),
                                            'api_token' => $eCommerceChannel->getApiToken(),
                                            'is_enabled' => $eCommerceChannel->getIsEnabled()
                                    ];
                                break;

                                case 'magento':
                                    return [
                                        'id' => $eCommerceChannel->getId(),
                                        'domain' => $eCommerceChannel->getDomain(),
                                        'api_username' => $eCommerceChannel->getApiUsername(),
                                        'api_password' => $eCommerceChannel->getApiPassword(),
                                        'is_enabled' => $eCommerceChannel->getIsEnabled()
                                    ];
                                break;

                                case 'opencart':
                                    return [
                                        'id' => $eCommerceChannel->getId(),
                                        'domain' => $eCommerceChannel->getDomain(),
                                        'api_key' => $eCommerceChannel->getApiKey(),
                                    ];
                                break;

                        }}, $eCommercePlatform->getECommerceChannelCollection())
                    ];
                }

                $event->setResponseData($response);
                break;
            case 'save-store':
                // get request params
                $attributes = json_decode($request->getContent(), true);
                $attributes = !$attributes ? $request->request->all() : $attributes;
               dump("inside order-sync: attributes: "); dump($attributes); 
                $platform = $attributes['platform'];
                $eCommercePlatform = $this->eCommerceConfiguration->getECommercePlatformByQualifiedName($platform);

                if (!empty($eCommercePlatform)) {
                    try {
                        if ('POST' == $request->getMethod()) {
                            dump("inside ord sync: POST");
                            dump($attributes);
                            $channel = $eCommercePlatform->createECommerceChannel($attributes);
                        } else if ('PUT' == $request->getMethod()) {
                            $channel = $eCommercePlatform->updateECommerceChannel($attributes);
                        } else if ('DELETE' == $request->getMethod()) {
                            $channel = $eCommercePlatform->removeECommerceChannel($attributes['attributes']);
                        }

                        $this->getPackage()->updatePackageConfiguration((string) $this->eCommerceConfiguration);

                        $application = new ConsoleApplication($this->kernel);
                        $application->setAutoExit(false);
                        $input = new ArrayInput([
                            'command' => 'cache:clear',
                            '--env' => $this->kernel->getEnvironment(),
                            '--no-warmup' => true
                        ]);

                        $application->run($input, new NullOutput());
                    } catch (\Exception $exception) {
                        $event->setResponseCode(500);
                        $event->setResponseData(['error' => $exception->getMessage()]);
                    }
                }

                break;
            case 'remove-store':
                // get request params
                $attributes = json_decode($request->getContent(), true);
                $attributes = !$attributes ? $request->request->all() : $attributes;

                // get platform id
                $platformId = array_keys($attributes)[0];
                $attributes = $attributes[$platformId];
                $eCommercePlatform = $this->eCommerceConfiguration->getECommercePlatformByQualifiedName('shopify');
                $channel = $eCommercePlatform->removeECommerceChannel($attributes);
                $this->getPackage()->updatePackageConfiguration((string) $this->eCommerceConfiguration);

                break;
            default:
                break;
        }
    }
}
