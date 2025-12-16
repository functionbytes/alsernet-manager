<?php

namespace App\View\Components\Health;

use Illuminate\View\Component;
use Illuminate\View\View;

class Logo extends Component
{
    public function render(): View
    {
        return view('health::logo');
    }
}
