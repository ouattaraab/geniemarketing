<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\NewsletterSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsletterConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly NewsletterSubscription $subscription) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmez votre inscription — '.$this->subscription->newsletter->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.newsletter-confirmation',
            with: [
                'subscription' => $this->subscription,
                'newsletter' => $this->subscription->newsletter,
                'confirmUrl' => route('newsletter.confirm', $this->subscription->confirmation_token),
                'unsubscribeUrl' => route('newsletter.unsubscribe', $this->subscription->unsubscribe_token),
            ],
        );
    }
}
