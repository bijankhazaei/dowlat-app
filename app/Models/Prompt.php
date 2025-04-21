<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prompt extends Model
{
    protected $fillable = [
        'content',
        'agent_name',
        'chain_name',
        'side',
        'ver',
    ];

    protected $table = 'prompts';
    protected $primaryKey = 'id';

    protected $connection = null;
}
