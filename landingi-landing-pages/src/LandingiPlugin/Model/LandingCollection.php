<?php
namespace Landingi\Wordpress\Plugin\LandingiPlugin\Model;

class LandingCollection
{
    private $landings = [];
    private $count;

    public function createFromApiResponse($data)
    {
        foreach ($data['landings'] as $landing) {
            $this->landings[$landing['id']] = new Landing(
                $landing['id'],
                $landing['name'],
                $landing['hash'],
                $landing['slug']
            );
        }

        $this->count = $data['count'];
    }

    public function addLanding(Landing $landing)
    {
        $this->landings[$landing->getId()] = $landing;
    }

    public function getLandings()
    {
        return $this->landings;
    }

    public function getLanding($id)
    {
        return $this->landings[$id];
    }

    public function getCount()
    {
        return $this->count;
    }
}
