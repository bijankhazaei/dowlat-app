<?php

namespace App\Contracts\Enums;

enum ERabbitQueues: string
{
    case LifeStyleParsingProduction = 'life_style_parsing_requests';
    case LifeStyleParsingConsumption = 'life_style_parsed_results';

    case LifeStyleScoringProduction = 'life_style_scoring_requests';
    case LifeStyleScoringConsumption = 'life_style_score_results';

    case LongevityParsingProduction = 'longevity_parsing_requests';
    case LongevityParsingConsumption = 'longevity_parsed_results';

    case LongevityScoringProduction = 'longevity_scoring_requests';
    case LongevityScoringConsumption = 'longevity_score_results';

    public static function values()
    {
        return array_map(function ($item) {
            return $item->value;
        }, self::cases());
    }
    public static function names()
    {
        return array_map(function ($item) {
            return $item->name;
        }, self::cases());
    }

    public function getRoutingKey()
    {
        return match ($this) {
            ERabbitQueues::LifeStyleParsingProduction => "qrawl_rk",
            ERabbitQueues::LifeStyleParsingConsumption => "qrawl_rk",
            ERabbitQueues::LifeStyleScoringProduction => "kally_rk",
            ERabbitQueues::LifeStyleScoringConsumption => "kally_rk",
            ERabbitQueues::LongevityParsingProduction => "lablamma_rk",
            ERabbitQueues::LongevityParsingConsumption => "lablamma_rk",
            ERabbitQueues::LongevityScoringProduction => "mortiai_rk",
            ERabbitQueues::LongevityScoringConsumption => "mortiai_rk",
            default => throw new \InvalidArgumentException("Provided Rabbit Queue value is not valid")
        };
    }

    public function getConsumerClass()
    {
        return match ($this) {
            ERabbitQueues::LifeStyleParsingConsumption => \App\Jobs\LifeStyleParsingConsumer::class,
            ERabbitQueues::LifeStyleScoringConsumption => \App\Jobs\LifeStyleScoringConsumer::class,
            ERabbitQueues::LongevityParsingConsumption => \App\Jobs\LongevityParsingConsumer::class,
            ERabbitQueues::LongevityScoringConsumption => \App\Jobs\LongevityScoringConsumer::class,
            default => throw new \InvalidArgumentException("Provided Rabbit Queue value is not a valid consumer")
        };
    }
}


