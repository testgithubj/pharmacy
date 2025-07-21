<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendPosInvoiceEmail extends Mailable
{
    use Queueable, SerializesModels;
    public $invoice;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->invoice = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from($this->invoice['from_email'], $this->invoice['company_owner'])
            ->subject($this->invoice['subject'])
            ->view('emails.pos_invoice');
    }
}
