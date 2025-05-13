<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Business;

class BusinessStatusChanged extends Notification
{
    protected $business;
    protected $status;

    public function __construct(Business $business, string $status)
    {
        $this->business = $business;
        $this->status = $status;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Business Status Update')
            ->line("Your business {$this->business->name} status has been updated to: {$this->status}")
            ->line("Reason: {$this->business->status_reason}")
            ->action('View Business', url('/dashboard/business'));
    }
}