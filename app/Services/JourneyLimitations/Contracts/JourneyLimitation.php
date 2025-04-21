<?php
namespace App\Services\JourneyLimitations\Contracts;

use App\Models\Journey;

class JourneyLimitation
{
    /** @var int|null */
    public ?int $maxCount;

    /** @var string|null */
    public ?string $description;

    /** @var Journey|null */
    public ?Journey $missing_dependency;

    public function __construct(?int $maxCount, ?string $description = null, ?Journey $missing_dependency = null)
    {
        $this->maxCount = $maxCount;
        $this->description = $description;
        $this->missing_dependency = $missing_dependency;
    }
}
