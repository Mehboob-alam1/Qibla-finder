<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['path', 'ip_address', 'user_agent', 'referrer'])]
class PageView extends Model
{
    //
}
