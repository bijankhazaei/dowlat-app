<?php

namespace App\Repositories\Journey;

use App\Contracts\Enums\EJourneyTypes;
use App\Helpers\Memo;
use App\Models\Journey;
use App\Repositories\BaseRepository;
use App\Repositories\Cart\CartRepository;
use App\Repositories\Order\OrderRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends parent<Journey>
 */
class JourneyRepository extends BaseRepository
{
    private const MODEL = Journey::class;
    public function __construct()
    {
        parent::__construct(JourneyRepository::MODEL);
    }
    protected static function instantiate(): static
    {
        return new JourneyRepository();
    }

    protected function getBaseJourneys(): Collection
    {
        return $this->whereNull('dependency_id')->get();
    }
}
