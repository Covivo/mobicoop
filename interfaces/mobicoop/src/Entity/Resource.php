<?php
namespace App\Entity;

/**
 * Resource interface.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 *        
 */
interface Resource
{
    public function getId(): ?int;
    public function setId(int $id);
}

