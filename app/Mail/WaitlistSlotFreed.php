<?php

namespace App\Mail;

use App\Models\Waitlist;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WaitlistSlotFreed extends Mailable
{
    use Queueable, SerializesModels;

    public $waitlist;
    public $doctorName;
    public $specialtyName;
    public $slotTime;

    /**
     * Create a new message instance.
     */
    public function __construct(Waitlist $waitlist, string $doctorName, string $specialtyName, string $slotTime)
    {
        $this->waitlist = $waitlist;
        $this->doctorName = $doctorName;
        $this->specialtyName = $specialtyName;
        $this->slotTime = $slotTime;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Cupo Disponible! Lista de Espera - Clínica MediCore',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.waitlist_slot_freed',
        );
    }
}
