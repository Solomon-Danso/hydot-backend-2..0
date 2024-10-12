<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\PrePaidMeter;

class Subscription extends Mailable {
use Queueable, SerializesModels;

public $Sales;
public function __construct(PrePaidMeter $Sales) {
$this->Sales = $Sales;

}

public function build(){
return $this->markdown('emails.contact.subscribe')
            ->with(['contact' => $this->Sales])
            ->subject('Software Subscription Token');

}



}
