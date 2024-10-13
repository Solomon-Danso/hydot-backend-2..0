<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Sales;

class HcsPay extends Mailable {
    use Queueable, SerializesModels;

    public $Sales;
    public $packageName;
    public $packageType;
    public $subDays;

    public function __construct(Sales $Sales, $packageName, $packageType,$subDays) {
        $this->Sales = $Sales;
        $this->packageName = $packageName; // New variable
        $this->packageType = $packageType; // New variable
        $this->subDays = $subDays; // New variable
    }

    public function build() {
        return $this->markdown('emails.contact.hcsPay')
                    ->with([
                        'contact' => $this->Sales,
                        'packageName' => $this->packageName, // Pass the package name
                        'packageType' => $this->packageType,  // Pass the package type
                        'subDays' => $this->subDays

                    ])
                    ->subject('Software Subscription Payment');
    }
}
