<?php

namespace App\Livewire;

use Livewire\Component;

class AuthLogin extends Component
{
    public $otp = '';
    public $error = '';

    public function addDigit($digit)
    {
        if (strlen($this->otp) < 8) {
            $this->otp .= $digit;
            
            if (strlen($this->otp) === 8) {
                $this->login();
            }
        }
    }

    public function clearOtp()
    {
        $this->otp = '';
        $this->error = '';
    }

    public function login()
    {
        $posOtp = (string) env('POS_OTP', '51141016');
        $kitchenOtp = (string) env('KITCHEN_OTP', '29736849');

        if ($this->otp === $posOtp) {
            $user = \App\Models\User::where('role', 'Caisse')->first();
            if ($user) {
                \Illuminate\Support\Facades\Auth::login($user, true);
            }
            session(['pos_authenticated' => true]);
            return redirect()->to(route('home'));
        }

        if ($this->otp === $kitchenOtp) {
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
