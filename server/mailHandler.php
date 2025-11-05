<?php
// server/mailHandler.php
namespace App;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

class MailHandler
{
    private $mailer;
    private $emailHost;
    private $emailPort;
    private $emailUsername;
    private $emailPassword;
    private $emailFrom;
    private $emailTo;

    public function __construct($emailHost, $emailPort, $emailUsername, $emailPassword, $emailFrom, $emailTo)
    {
        $this->emailHost     = $emailHost;
        $this->emailPort     = $emailPort;
        $this->emailUsername = $emailUsername;
        $this->emailPassword = $emailPassword;
        $this->emailFrom     = $emailFrom;
        $this->emailTo       = $emailTo;

        $this->mailer = new PHPMailer(true);
        $this->setupSMTP(); // call AFTER setting credentials
    }

    private function setupSMTP()
    {
        $this->mailer->isSMTP();
        $this->mailer->Host       = 'smtp.gmail.com';
        $this->mailer->SMTPAuth   = true;
        $this->mailer->Username   = $this->emailUsername;
        $this->mailer->Password   = $this->emailPassword;
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port       = 587;

        // now this will have a valid email address
        $this->mailer->setFrom($this->emailFrom, 'Holly Jolly & Hallmark');
    }

    public function sendFanSubmission($data)
    {
        try {
            // Send to admin
            $this->mailer->addAddress($this->emailTo);
            $this->mailer->addReplyTo($data['email'], $data['name'] ?? $data['email']);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Holly Jolly & Hallmark Fan Submission';

            $body = $this->buildEmailBody($data);
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);
            $this->mailer->send();

            return ['success' => true, 'message' => 'Thank you for subscribing!'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Mailer Error: ' . $this->mailer->ErrorInfo];
        }
    }

    private function buildEmailBody($data)
    {
        $name         = htmlspecialchars($data['name'] ?? '');
        $email        = htmlspecialchars($data['email']);
        $plotline     = htmlspecialchars($data['plotline'] ?? '');
        $favoriteStar = htmlspecialchars($data['favorite_star'] ?? '');
        $participate  = htmlspecialchars($data['participate'] ?? '');
        $message      = nl2br(htmlspecialchars($data['message'] ?? ''));

        return "
        <html>
        <body style='font-family: Arial, sans-serif; background:#f8f8f8; padding:20px;'>
            <div style='max-width:600px;margin:auto;background:#fff;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.1);padding:25px;'>
                <h2 style='color:#C0392B;text-align:center;'>Holly Jolly & Hallmark Fan Submission</h2>
                <p><strong>A Hallmark fan has subscribed!</strong></p>
                <table style='width:100%;border-collapse:collapse;margin-top:15px;'>
                    <tr><td style='padding:8px;border-bottom:1px solid #eee;'><strong>Name:</strong></td><td>$name</td></tr>
                    <tr><td style='padding:8px;border-bottom:1px solid #eee;'><strong>Email:</strong></td><td>$email</td></tr>
                    <tr><td style='padding:8px;border-bottom:1px solid #eee;'><strong>Plotline:</strong></td><td>$plotline</td></tr>
                    <tr><td style='padding:8px;border-bottom:1px solid #eee;'><strong>Favorite Star:</strong></td><td>$favoriteStar</td></tr>
                    <tr><td style='padding:8px;border-bottom:1px solid #eee;'><strong>Interested in participating?</strong></td><td>$participate</td></tr>
                    <tr><td style='padding:8px;'><strong>Comments:</strong></td><td>$message</td></tr>
                </table>
                <p style='margin-top:20px;color:#999;font-size:0.9em;text-align:center;'>Sent on " . date('Y-m-d H:i:s') . "</p>
            </div>
        </body>
        </html>";
    }

    /**
     * Send confirmation email to the user
     */
    public function sendConfirmationToUser($userEmail, $userName)
    {
        try {
            $confirmation = new PHPMailer(true);
            $confirmation->isSMTP();
            $confirmation->Host       = $this->emailHost;
            $confirmation->SMTPAuth   = true;
            $confirmation->Username   = $this->emailUsername;
            $confirmation->Password   = $this->emailPassword;
            $confirmation->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $confirmation->Port       = $this->emailPort;

            $confirmation->setFrom($this->emailFrom, 'Holly Jolly & Hallmark');
            $confirmation->addAddress($userEmail, $userName ?: 'Hallmark Fan');
            $confirmation->isHTML(true);
            $confirmation->Subject = 'Your officially part of our cozy crew!';

            $confirmation->Body = "
            <html>
            <body style='font-family: Arial, sans-serif; background:#f8f8f8; padding:20px;'>
                <div style='max-width:600px;margin:auto;background:#fff;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.1);padding:25px;'>
                    <h2 style='color:#C0392B;text-align:center;'>Your officially part of our cozy crew!</h2>
                    <p>This is going to be so much fun!</p>
                    <p>We’re in the pre-production phase of bringing the <strong>Holly, Jolly & Hallmark</strong> podcast to life, 
                    and your ideas, requests, and suggestions mean the world to us.</p>
                    <p>Let’s get to know each other, share some laughs, and celebrate everything we love about Hallmark together.</p>
                    <p>Keep an eye on your inbox — cozy updates and early-access surprises are on the way!</p>
                    <p>Got some requests, ideas or suggestions?<br>
                    Reach out to me at <a href='mailto:pam@HollyJollyHallmark.com'>pam@HollyJollyHallmark.com</a></p>
                    <p style='margin-top:20px;color:#999;font-size:0.9em;text-align:center;'>Sent with love from the Holly Jolly & Hallmark team ❤️</p>
                </div>
            </body>
            </html>";

            $confirmation->AltBody = "This is going to be so much fun!\n\nWe’re in the pre-production phase of bringing the Holly, Jolly & Hallmark podcast to life...\n\nReach out anytime at pam@HollyJollyHallmark.com";

            $confirmation->send();

            return ['success' => true, 'message' => 'Send email to user successfully!'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Mailer Error: ' . $this->mailer->ErrorInfo];
        }
    }
}
