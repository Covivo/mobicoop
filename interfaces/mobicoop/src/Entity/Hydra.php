<?php 

namespace App\Entity;

/**
 * A hydra collection object (returned by a JSON-LD Rest API).
 */
class Hydra
{
    
    /**
     * @var string $context The context of the collection.
     */
    private $context;
    
    /**
     * @var int $id The id of the collection.
     */
    private $id;
    
    /**
     * @var string $type The type of the collection.
     */
    private $type;
    
    /**
     * @var array $member The hydra member.
     */
    private $member;
    
    /**
     * @var int $totalItems The total number of items of the collection.
     */
    private $totalItems;
    
    /**
     * @var HydraView $view The view of the collection.
     */
    private $view;
        
    public function getId ()
    {
        return $this->id;
    }

    public function getContext ()
    {
        return $this->context;
    }

    public function getType ()
    {
        return $this->type;
    }

    public function getMember ()
    {
        return $this->member;
    }

    public function getTotalItems ()
    {
        return $this->totalItems;
    }

    public function getView ()
    {
        return $this->view;
    }

    public function setId ($id)
    {
        $this->id = $id;
    }

    public function setContext ($context)
    {
        $this->context = $context;
    }

    public function setType ($type)
    {
        $this->type = $type;
    }

    public function setMember ($member)
    {
        $this->member = $member;
    }
    
    public function setTotalItems ($totalItems)
    {
        $this->totalItems = $totalItems;
    }

    public function setView ($view)
    {
        $this->view = $view;
    }
        
}