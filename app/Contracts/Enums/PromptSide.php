<?php

namespace App\Contracts\Enums;

enum PromptSide: string
{
    case SYSTEM = 'SYSTEM';
    case HUMAN = 'HUMAN';
}
