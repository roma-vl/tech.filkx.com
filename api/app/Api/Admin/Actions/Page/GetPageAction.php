<?php

namespace App\Api\Admin\Actions\Page;

use App\Models\Page;

class GetPageAction
{
    public function execute(int $id): Page
    {
        return Page::findOrFail($id);
    }
}
