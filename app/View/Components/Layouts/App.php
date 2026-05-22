<?php

namespace App\View\Components\Layouts;

use Illuminate\View\Component;
use Illuminate\View\View;

class App extends Component
{
    public function __construct(
        public ?string $title = 'EduSpace'
    ) {}

    public function render(): View
    {
        return view('components.layouts.app');
    }
}
