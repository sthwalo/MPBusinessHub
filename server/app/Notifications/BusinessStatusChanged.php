<?php

namespace App\Notifications;

use App\Models\Business;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BusinessStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    protected $business;
    protected $oldStatus;
    protected $newStatus;
    protected $reason;

    /**
     * Create a new notification instance.
     *
     * @param Business $business
     * @param string $oldStatus
     * @param string $newStatus
     * @param string|null $reason
     */
    public function __construct(Business $business, string $oldStatus, string $newStatus, ?string $reason = null)
    {
        $this->business = $business;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->reason = $reason;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $message = (new MailMessage)
            ->subject('Business Status Update - ' . $this->business->name)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('The status of your business "' . $this->business->name . '" has been updated.');

        if ($this->newStatus === 'approved') {
            $message->line('Congratulations! Your business has been approved and is now visible in the directory.');
            $message->action('View Your Business', url('/business/' . $this->business->id));
        } elseif ($this->newStatus === 'rejected') {
            $message->line('Unfortunately, your business listing has been rejected.');
            if ($this->reason) {
                $message->line('Reason: ' . $this->reason);
            }
            $message->line('You can update your business information and resubmit for approval.');
            $message->action('Edit Your Business', url('/dashboard/business-profile'));
        } else {
            $message->line('Your business is now pending review.');
            $message->line('We will notify you once the review is complete.');
        }

        return $message->line('Thank you for using MPBusinessHub!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'business_id' => $this->business->id,
            'business_name' => $this->business->name,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'reason' => $this->reason,
        ];
    }
}