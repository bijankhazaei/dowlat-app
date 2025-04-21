<?php

namespace App\Helpers;

use SplObjectStorage;

class CartItemSorter
{
    private array $processed = [];

    public function sort(iterable $items)
    {
        $collection = collect($items)
            ->map(fn($item) => (object) [
                'id' => $item->journey->id,
                'dependency_id' => $item->journey->dependency_id,
                'original' => $item
            ])
            ->sort(fn($a, $b) => $this->compare($a, $b))
            ->map(fn($x) => $x->original);

        return $collection;
    }

    private function compare(object $a, object $b): int
    {
        if ($this->isIndependent($a)) {
            $this->markProcessed($a);
            if ($this->isIndependent($b)) {
                $this->markProcessed($b);
                return 0;
            }
            return -1;
        }
        if ($this->isIndependent($b)) {
            $this->markProcessed($b);
            return 1;
        }

        return $this->compareDependent($a, $b);
    }

    private function isIndependent(object $journey): bool
    {
        return $journey->dependency_id === null;
    }

    private function compareDependent(object $a, object $b): int
    {
        $aDepProcessed = isset($this->processed[$a->dependency_id]);
        $bDepProcessed = isset($this->processed[$b->dependency_id]);

        return match (true) {
            $aDepProcessed && !$bDepProcessed => -1,
            !$aDepProcessed && $bDepProcessed => 1,
            default => $this->resolveTie($a, $b)
        };
    }

    private function resolveTie(object $a, object $b): int
    {
        $this->markProcessed($a, $b);
        return 0;
    }

    private function markProcessed(object ...$journeys): void
    {
        foreach ($journeys as $journey) {
            if (!isset($this->processed[$journey->id])) {
                $this->processed[$journey->id] = true;
            }
        }
    }
}