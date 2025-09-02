<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailConfig {
    private static function getMailer() {
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';  // Replace with your SMTP host
            $mail->SMTPAuth = true;
            $mail->Username = 'ahmedeslaiti@gmail.com';  // Replace with your email
            $mail->Password = 'djuj pmxk kcon apoh';  // Replace with your app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->setFrom('ahmedeslaiti@gmail.com', 'surveymaster_admin');
            
            return $mail;
        } catch (Exception $e) {
            throw new Exception('Error initializing mailer: ' . $e->getMessage());
        }
    }
    
    public static function sendPasswordResetEmail($to, $resetLink) {
        try {
            $mail = self::getMailer();
            
            // Recipients
            $mail->addAddress($to);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body = "<p>Hello,</p>
                          <p>You have requested to reset your password. Click the link below to reset your password:</p>
                          <p><a href='$resetLink'>Reset Password</a></p>
                          <p>This link will expire in 1 hour.</p>
                          <p>If you did not request this, please ignore this email.</p>";
            $mail->AltBody = "Hello,\n\nYou have requested to reset your password. Click the link below to reset your password:\n\n$resetLink\n\nThis link will expire in 1 hour.\n\nIf you did not request this, please ignore this email.";
            
            return $mail->send();
        } catch (Exception $e) {
            throw new Exception('Error sending password reset email: ' . $e->getMessage());
        }
    }
}