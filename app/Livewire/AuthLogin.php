<?php

namespace App\Livewire;

use Livewire\Component;

class AuthLogin extends Component
{
    public $otp = '';
    public $error = '';

    public function updatedOtp($value)
    {
        if (strlen($value) >= 8) {
            $this->login();
        }
    }

    public function login()
    {
        if ($this->otp === env('POS_OTP', '12345678')) {
            $user = \App\Models\User::where('role', 'Caisse')->first();
            if ($user) {
                \Illuminate\Support\Facades\Auth::login($user, true);
            }
            session(['pos_authenticated' => true]);
            return redirect()->to(route('home'));
        }

        if ($this->otp === env('KITCHEN_OTP', '00000000')) {
            $user = \App\Models\User::where('role', 'Cuisine')->first();
            if ($user) {
                \Illuminate\Support\Facades\Auth::login($user, true);
            }
            session(['kitchen_authenticated' => true]);
            return redirect()->to(route('kitchen'));
        }

        $this->error = 'Code d\'accès incorrect';
        $this->otp = '';
    }

    public function render()
    {
        return view('livewire.auth-login')->layout('layouts.pos');
    }
}
