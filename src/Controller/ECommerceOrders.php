<?php

namespace UVDesk\CommunityPackages\UVDesk\ECommerce\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use UVDesk\CommunityPackages\UVDesk\ECommerce\Entity\ECommerceOrder;
use UVDesk\CommunityPackages\UVDesk\ECommerce\Utils\ECommerceConfiguration;

class ECommerceOrders extends Controller
{
    public function integrateOrders($ticketId, Request $request, ECommerceConfiguration $eCommerceConfiguration, EntityManagerInterface $entityManager)
    {
        $params = json_decode($request->getContent(), true);
        $eCommercePlatform = $eCommerceConfiguration->getECommercePlatformByQualifiedName($params['platform']);

        if (empty($eCommercePlatform)) {
            dump('platform not found');
            die;
        } else {
            $eCommerceChannel = $eCommercePlatform->getECommerceChannel($params['channelId']);

            if (empty($eCommerceChannel)) {
                dump('channel not found');
                die;
            }
        }

        $eCommerceOrderDetails = $eCommerceChannel->fetchECommerceOrderDetails((array) $params['orderId']);
        
        $ticketRepository = $entityManager->getRepository('UVDeskCoreFrameworkBundle:Ticket');
        $eCommerceOrderRepository = $entityManager->getRepository('UVDeskECommercePackage:ECommerceOrder');

        $ticket = $ticketRepository->findOneById($ticketId);

        // // Retrieve any existing ticket order else create one
        // $existingOrders = $eCommerceOrderRepository->findByTicket($ticket);

        // if (empty($existingOrders)) {
        //     $orderExistsFlag = 1;
        // }

        $ecommerceOrder = new ECommerceOrder();

        // Set ECom. Order Details
        $ecommerceOrder->setTicket($ticket);
        $ecommerceOrder->setOrderId($params['orderId']);
        $ecommerceOrder->setOrderDetails(json_encode($eCommerceOrderDetails));

        $entityManager->persist($ecommerceOrder);
        $entityManager->flush();

        // Setup Response
        $response = [
            'success' => true,
            'orderDetails' => $eCommerceOrderDetails,
            'alertClass' => 'success',
            'alertMessage' => 'Success! Order updated successfully.',
            'collectedOrders' => $params['orderId'],
        ];

        return new JsonResponse($response);
    }

    public function public(Request $request)
    {
        dump('public');
        dump($request);
        die;
    }
}
