<?php

namespace UVDesk\CommunityPackages\UVDesk\ECommerce\Entity;

use Doctrine\ORM\Mapping as ORM;
use Webkul\UVDesk\CoreFrameworkBundle\Entity\Ticket;

/**
 * @ORM\Entity(repositoryClass="UVDesk\CommunityPackages\UVDesk\ECommerce\Repository\ECommerceOrderRepository")
 */
class ECommerceOrder
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $orderId;

    /**
     * @ORM\Column(type="json_array")
     */
    private $orderDetails;

    /**
     * @ORM\ManyToOne(targetEntity="Webkul\UVDesk\CoreFrameworkBundle\Entity\Ticket")
     * @ORM\JoinColumn(nullable=false)
     */
    private $ticket;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderId(): ?string
    {
        return $this->orderId;
    }

    public function setOrderId(string $orderId): self
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getOrderDetails()
    {
        return $this->orderDetails;
    }

    public function setOrderDetails($orderDetails): self
    {
        $this->orderDetails = $orderDetails;

        return $this;
    }

    public function getTicket(): ?Ticket
    {
        return $this->ticket;
    }

    public function setTicket(?Ticket $ticket): self
    {
        $this->ticket = $ticket;

        return $this;
    }
}
