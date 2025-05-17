<?php 

// src/Form/Model/AccommodationCollection.php
namespace App\Form\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class AccommodationCollection
{
    private Collection $accommodations;

    public function __construct(array $accommodations = [])
    {
        $this->accommodations = new ArrayCollection($accommodations);
    }

    public function getAccommodations(): Collection
    {
        return $this->accommodations;
    }

    public function setAccommodations(Collection $accommodations): void
    {
        $this->accommodations = $accommodations;
    }
}
