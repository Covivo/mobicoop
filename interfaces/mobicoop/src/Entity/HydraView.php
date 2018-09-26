<?php

namespace App\Entity;

/**
 * A hydra view from an hydra collection object.
 */
class HydraView
{
    
    /**
     * @var int $id The id of the view.
     */
    private $id;
    
    /**
     * @var string The type of the view.
     */
    private $type;
    
    /**
     * @var string The first item of the view.
     */
    private $first;

    /**
     * @var string The last item of the view.
     */
    private $last;
    
    /**
     * @var string The next item of the view.
     */
    private $next;
    
    public function getId()
    {
        return $this->id;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getFirst()
    {
        return $this->first;
    }

    public function getLast()
    {
        return $this->last;
    }

    public function getNext()
    {
        return $this->next;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function setFirst($first)
    {
        $this->first = $first;
    }

    public function setLast($last)
    {
        $this->last = $last;
    }

    public function setNext($next)
    {
        $this->next = $next;
    }
}
