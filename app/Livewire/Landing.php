<?php

namespace App\Livewire;

use Livewire\Component;

class Landing extends Component
{
    public function render()
    {
        return view('livewire.landing')
            ->layout('components.layouts.guest-landing', ['title' => 'EduSpace — Belajar Lebih Tenang']);
    }
}
