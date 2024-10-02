<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Meetings;

class Meeting extends Mailable {
use Queueable, SerializesModels;

public $Sales;
public function __construct(Meetings $Sales) {
$this->Sales = $Sales;

}

public function build(){
return $this->markdown('emails.contact.meetings')
            ->with(['contact' => $this->Sales])
            ->subject('Hydot Tech Meeting Schedule');

}



}
