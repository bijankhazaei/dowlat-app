<?php

namespace App\Http\Resources;

use App\Contracts\Enums\EOrderStates;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

class OJLifeStyleScoreResource extends JsonResource
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
        $free = $this->meta->metadata['free'];
        $formId = collect($this->journey->extra ?? [])->where('type', $free ? 'free-form-id' : 'form-id')->firstOrFail()['value'];
        $query = explode('?', URL::signedRoute(
            'ws-porsline',
            [
                'uid' => bin2hex(strval($request->user()->id) . ":" . $this->meta->id . ":" . $formId)
            ]
        ))[1];

        $res = [
            'id' => $this->id,
            'meta_id' => $this->meta->id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'slug' => $this->journey->slug,
            'name' => $this->journey->name,
            'journey_id' => $this->journey->id,
            'free' => $free,
            'porsline_url' => "https://zeenome.porsline.ir/s/" . $formId . "?" . $query . (config("app.env") === "local" ? ("&origin=" . str_replace(["https://", "http://"], ["", ""], config('app.url'))) : ""),
            'result' => $this->meta->result,
            'error' => $this->meta->error,
            'prepared_at' => $this->meta->prepared_at,
            'parsed_at' => $this->meta->parsed_at,
            'enqueued_at' => $this->meta->enqueued_at,
            'calculated_at' => $this->meta->calculated_at,
            'created_at' => $this->created_at,
            'status' => !is_null($this->meta->error)
                ? 'error'
                : (!is_null($this->meta->calculated_at)
                    ? 'calculated'
                    : (!is_null($this->meta->enqueued_at)
                        ? 'enqueued'
                        : (!is_null($this->meta->parsed_at)
                            ? 'parsed'
                            : (!is_null($this->meta->prepared_at)
                                ? 'prepared'
                                : ($this->order->status === EOrderStates::Pending
                                    ? 'not-started'
                                    : ($this->order->status === EOrderStates::Canceled
                                        ? 'cancelled'
                                        : 'pending')))))),
            'extra' => $this->extra,
        ];

        return $res;
    }
}
