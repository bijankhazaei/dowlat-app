<?php
namespace App\Helpers;

use Illuminate\Pagination\LengthAwarePaginator;
class Paginator extends LengthAwarePaginator
{
    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        $items = $this->items->toArray();
        $currentPageItemsCount = count($items);
        $res = [
            'items' => $items,
            'currentPage' => $this->currentPage(),
            'totalPages' => $this->lastPage(),
            'totalItems' => $this->total(),
            'itemsPerPage' => $this->perPage(),
            'currentPageFrom' => (($this->currentPage() - 1) * $this->perPage()) + 1
        ];
        $res['currentPageTo'] = $res['currentPageFrom'] - 1 + $currentPageItemsCount;
        if ($res['currentPageTo'] <= 0) {
            $res['currentPageTo'] = null;
            $res['currentPageFrom'] = null;
        }
        return $res;
    }
}
