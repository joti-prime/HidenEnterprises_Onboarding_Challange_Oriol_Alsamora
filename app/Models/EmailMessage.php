<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailMessage extends Model
{
    use HasFactory;

    protected $table = 'email_messages';

    protected $fillable = [
        'key',
        'language',
        'subject',
        'content',
    ];

    public function getContentBuyKey($key, $language = 'en')
    {
        $message = $this->where('key', $key)->where('language', $language)->first()->content ?? null;
        if ($message) {
            return $message;
        }
        $message = $this->where('key', $key)->where('language', settings('language', 'en'))->first()->content ?? null;
        if ($message) {
            return $message;
        }

        return $this->defaultMessage($key);
    }

    public function defaultMessage($key): ?string
    {
        return self::getAllMessages()[$key] ?? null;
    }

    public static function getAllMessages(): array
    {
        return [
            'account_deletion_requested' => 'You have requested to have your account permanent deleted from our platform. To protect you, we have placed your account in a queue of 72 hours, after which your account will be permanently deleted. this action is irreversible.',
            'cancelled' => 'We are writing to inform you that you have cancelled your service. Should you change your mind; you can dismiss your cancellation within the grace period. <br><br> We hope to see you as a client in the future.',
            'new_device' => 'It looks like you signed into your account from a new device and IP address.',
            'outro' => 'Should you have any further inquiries or require ongoing support, please feel free to reach out to us through our online portal. Our dedicated team is available to assist you promptly and efficiently via the ticketing system.',
            'payment_paid' => 'We are pleased to inform you that your payment has been successfully processed and received. This email serves as a confirmation that your payment has been successfully completed.',
            'refund' => 'We are writing to inform you that we have initiated a refund for one of your payments. Your refund is being processed in the background. Please allow 3 - 5 business days to receive your refund. For Payments made with Balance this is instant.',
            'suspended' => 'We regret to inform you that your service has been suspended due to overdue payment. To avoid termination, please settle any outstanding invoices within '. settings('orders::terminate_suspended_after', 7) .' days from the due date. If payment is not received within this timeframe, your service will be terminated, resulting in the deletion or revocation of all associated data, files, and licenses.',
            'terminated' => 'We regret to inform you that your service has been terminated due to overdue payment. Termination can happen due to a couple of reasons. You were late on payment, or you cancelled the service. All data / files / licenses belonging to this service have been deleted or revoked. <br> We hope to see you as a client in the future.',
            'welcome_email' => 'Welcome to HCTestDash! We are thrilled to have you and can\'t wait to offer you our exceptional services. Our platform is designed to cater to your needs, whether you\'re connecting with others, streamlining workflows, or exploring new possibilities. With a seamless user experience, we look forward to providing you with top-notch services that will enhance your online experience.',
            'order_created' => 'We emailing to inform you that a brand new order was created for your account and is ready for use.',
            'verification' => 'Thank you for creating an account on ' . settings('app_name', 'HCTestDash') . '. You can find your verification code below which you can enter on our website or use the button. If you did not create an account on our website, you can ignore this email.',
            'subscription_paid' => 'We are e-mailing you to inform that an automatic subscription payment has successfully been processed for one of your orders.',
            'subscription_cancel' => 'We are e-mailing you to inform that an automatic subscription payment has successfully been canceled for one of your orders.',
        ];
    }
}
