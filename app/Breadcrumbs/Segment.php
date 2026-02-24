<?php

namespace App\Breadcrumbs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class Segment
{
    public function __construct(
        protected Request $request,
        protected string $segment,
    ) {}

    public function name(): string
    {
        return Str::title($this->segment);
    }

    public function model(): ?Model
    {
        return collect($this->request->route()->parameters())
            ->where('slug', $this->segment)
            ->first();
    }

    public function url(): string
    {
        return url(implode('/', array_slice($this->request->segments(), 0, $this->position() + 1)));
    }

    public function position(): int|false
    {
        return array_search($this->segment, $this->request->segments());
    }
}
