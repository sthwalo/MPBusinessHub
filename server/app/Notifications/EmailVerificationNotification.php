<?php

namespace App\Notifications;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class EmailVerificationNotification extends Notification
{
    /**
     * The callback that should be used to create the verify email URL.
     *
     * @var \Closure|null
     */
    public static $createUrlCallback;

    /**
     * Get the notification's channels.
     *
     * @param  mixed  $notifiable
     * @return array|string
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        try {
            $verificationUrl = $this->verificationUrl($notifiable);
            
            return (new MailMessage)
                ->subject('Verify Email Address')
                ->line('Please click the button below to verify your email address.')
                ->action('Verify Email Address', $verificationUrl)
                ->line('If you did not create an account, no further action is required.');
        } catch (\Exception $e) {
            Log::error('Email verification notification error: ' . $e->getMessage());
            // Return a basic message without the action button if URL generation fails
            return (new MailMessage)
                ->subject('Verify Email Address')
                ->line('Please verify your email address.')
                ->line('If you did not create an account, no further action is required.');
        }
    }

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        try {
            if (static::$createUrlCallback) {
                return call_user_func(static::$createUrlCallback, $notifiable);
            }

            $frontendUrl = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000'));
            Log::info('Using frontend URL: ' . $frontendUrl);
            
            // Create a direct URL to the frontend with the necessary parameters
            $userId = $notifiable->getKey();
            $userEmail = $notifiable->getEmailForVerification();
            $hash = sha1($userEmail);
            $expires = Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60))->timestamp;
            
            // Generate a signature manually
            $signatureData = ['id' => $userId, 'hash' => $hash, 'expires' => $expires];
            $signature = hash_hmac('sha256', json_encode($signatureData), config('app.key'));
            
            // Construct a frontend URL with the necessary parameters
            $finalUrl = $frontendUrl . '/verify-email?' . http_build_query([
                'id' => $userId,
                'hash' => $hash,
                'expires' => $expires,
                'signature' => $signature
            ]);
            
            Log::info('Generated verification URL: ' . $finalUrl);
            return $finalUrl;
        } catch (\Exception $e) {
            Log::error('Error generating verification URL: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Set a callback that should be used when creating the email verification URL.
     *
     * @param  \Closure  $callback
     * @return void
     */
    public static function createUrlUsing($callback)
    {
        static::$createUrlCallback = $callback;
    }
}