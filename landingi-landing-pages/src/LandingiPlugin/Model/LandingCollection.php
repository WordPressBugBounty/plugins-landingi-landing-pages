<?php
namespace Landingi\Wordpress\Plugin\LandingiPlugin\Model;

class LandingCollection
{
    private $landings = [];
    private int $count;

    public function createFromApiResponse($data): void
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

    public function addLanding(Landing $landing): void
    {
        $this->landings[$landing->getId()] = $landing;
    }

    public function getLandings(): array
    {
        return $this->landings;
    }

    public function getLanding($id)
    {
        return $this->landings[$id];
    }

    public function getCount(): int
    {
        return $this->count;
    }
}
