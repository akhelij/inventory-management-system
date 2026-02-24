<?php

namespace App\Breadcrumbs;

use Illuminate\Http\Request;

class Breadcrumbs
{
    public function __construct(
        protected Request $request,
    ) {}

    public function segments(): array
    {
        return array_map(
            fn (string $segment) => new Segment($this->request, $segment),
            $this->request->segments(),
        );
    }
}
