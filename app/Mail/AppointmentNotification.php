<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $appointment;
    public $actionType;

    public function __construct($appointment, $actionType)
    {
        $this->appointment = $appointment;
        $this->actionType = $actionType;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'MediCore: Su cita ha sido ' . $this->actionType,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.appointment-status',
        );
    }
}