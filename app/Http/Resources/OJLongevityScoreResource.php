<?php

namespace App\Http\Resources;

use App\Contracts\Enums\EOrderStates;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

class OJLongevityScoreResource extends JsonResource
{
    public function __construct($resource)
    {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $sampling = $this->meta->metadata['sampling'];

        $res = [
            'id' => $this->id,
            'meta_id' => $this->meta->id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'slug' => $this->journey->slug,
            'name' => $this->journey->name,
            'journey_id' => $this->journey->id,

            'extra' => $this->extra,

            'sampling' => $sampling,
            'book_time' => $this->meta->metadata['book_time'],
            'book_address' => $this->meta->metadata['book_address'],

            'result' => $this->meta->result,
            'error' => $this->meta->error,
            'sampled_at' => $this->meta->sampled_at,
            'prepared_at' => $this->meta->prepared_at,
            'approved_at' => $this->meta->approved_at,
            'rejected_at' => $this->meta->rejected_at,
            'rejected_due' => $this->meta->rejected_due,
            'parsed_at' => $this->meta->parsed_at,
            'enqueued_at' => $this->meta->enqueued_at,
            'calculated_at' => $this->meta->calculated_at,
            'created_at' => $this->created_at,

            'status' => !is_null($this->meta->error)
                ? 'error'
                : (!is_null($this->meta->rejected_at)
                    ? 'rejected'
                    : (!is_null($this->meta->calculated_at)
                        ? 'calculated'
                        : (!is_null($this->meta->enqueued_at)
                            ? 'enqueued'
                            : (!is_null($this->meta->parsed_at)
                                ? 'parsed'
                                : (!is_null($this->meta->approved_at)
                                    ? 'approved'
                                    : (!is_null($this->meta->prepared_at)
                                        ? 'prepared'
                                        : ($sampling && !is_null($this->meta->sampled_at)
                                            ? 'pending-upload'
                                            : ($this->order->status === EOrderStates::Pending
                                                ? 'not-started'
                                                : ($this->order->status === EOrderStates::Canceled
                                                    ? 'cancelled'
                                                    : ($sampling
                                                        ? 'pending-sample'
                                                        : 'pending-upload')))))))))),
        ];

        return $res;
    }
}
