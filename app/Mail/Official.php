<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\ScheduleEmail;

class Official extends Mailable {
    use Queueable, SerializesModels;

    public $Sales;

    public function __construct(ScheduleEmail $Sales) {
        $this->Sales = $Sales;
    }

    public function build() {
        return $this->view('emails.contact.official') // Ensure this is the correct path to your HTML email view
            ->with(['contact' => $this->Sales])
            ->subject('Hydot Technology Official Email'); // Set the subject if needed
    }
}
