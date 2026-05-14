<?php

namespace App\Modules\Administration\Events;

use App\Modules\Administration\Models\SchoolAdmin;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SchoolAdminCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly SchoolAdmin $schoolAdmin) {}
}
