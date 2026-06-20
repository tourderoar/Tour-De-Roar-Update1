<?php
/**
 * File: includes/mail.php
 * Location: /tour_update/includes/mail.php
 *
 * Transactional email system for Tour de Roar.
 *
 * HOW IT WORKS:
 *   - LOCAL  (APP_ENV === 'local'):  Emails are NOT sent. The full email content
 *     (including activation/reset links) is written to logs/mail.log so you
 *     can test every email flow without needing a real email account.
 *
 *   - PRODUCTION (APP_ENV === 'production'):  Emails are sent via the ZeptoMail API.
 *     ZeptoMail credentials come from config.php (loaded from server environment vars).
 *
 * TEMPLATE FUNCTIONS (bottom of this file):
 *   send_activation_email()      — Welcome + account activation link (24h expiry)
 *   send_password_reset_email()  — Password reset link (1h expiry)
 */

if (!defined('APP_URL')) {
    require_once dirname(__DIR__) . '/config.php';
}

// -----------------------------------------------------------------------
// CORE SEND FUNCTION
// Routes to ZeptoMail on production or the local log file on local.
// -----------------------------------------------------------------------

/**
 * Send a transactional email. On local, writes to logs/mail.log instead.
 *
 * @param string $to_email  Recipient email address
 * @param string $to_name   Recipient display name
 * @param string $subject   Email subject line
 * @param string $html_body Full HTML email content
 * @return bool             True on success or successful log write, false on failure
 */
function send_mail(string $to_email, string $to_name, string $subject, string $html_body): bool
{
    if (APP_ENV === 'local') {
        return log_mail_locally($to_email, $to_name, $subject, $html_body);
    }

    return send_via_zeptomail($to_email, $to_name, $subject, $html_body);
}


// -----------------------------------------------------------------------
// ZEPTOMAIL SENDER (production only)
// -----------------------------------------------------------------------

/**
 * Send an email via the ZeptoMail REST API.
 * Only called when APP_ENV === 'production'.
 *
 * @param string $to_email
 * @param string $to_name
 * @param string $subject
 * @param string $html_body
 * @return bool
 */
function send_via_zeptomail(string $to_email, string $to_name, string $subject, string $html_body): bool
{
    // Build the JSON payload that ZeptoMail's API expects
    $payload = json_encode([
        'from' => [
            'address' => MAIL_FROM_ADDRESS,
            'name'    => MAIL_FROM_NAME,
        ],
        'to' => [[
            'email_address' => [
                'address' => $to_email,
                'name'    => $to_name,
            ],
        ]],
        'subject'  => $subject,
        'htmlbody' => $html_body,
    ]);

    // Use cURL to POST to the ZeptoMail REST API
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => ZEPTOMAIL_API_URL,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'Accept: application/json',
            'Content-Type: application/json',
            // ZeptoMail uses a specific Authorization header format
            'Authorization: Zoho-enczapikey ' . ZEPTOMAIL_TOKEN,
        ],
        CURLOPT_TIMEOUT        => 15, // Give up after 15 seconds
    ]);

    $response   = curl_exec($ch);
    $http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($curl_error) {
        error_log("ZeptoMail cURL error sending to {$to_email}: {$curl_error}");
        return false;
    }

    // ZeptoMail returns 2xx status codes on success
    if ($http_code < 200 || $http_code >= 300) {
        error_log("ZeptoMail API error (HTTP {$http_code}) sending to {$to_email}: {$response}");
        return false;
    }

    return true;
}


// -----------------------------------------------------------------------
// LOCAL LOG WRITER (development only)
// -----------------------------------------------------------------------

/**
 * Write an email to logs/mail.log instead of sending it.
 * This lets you read the full email content (including activation/reset links)
 * during local development without needing a real email address.
 *
 * The logs/ directory is created automatically if it doesn't exist.
 *
 * @param string $to_email
 * @param string $to_name
 * @param string $subject
 * @param string $html_body
 * @return bool  True if the log write succeeded
 */
function log_mail_locally(string $to_email, string $to_name, string $subject, string $html_body): bool
{
    $log_dir  = dirname(__DIR__) . '/logs';
    $log_file = $log_dir . '/mail.log';

    // Create the logs directory if it doesn't exist yet
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }

    // Format a readable log entry with a clear visual separator.
    // strip_tags removes HTML markup but keeps all the text content,
    // including link URLs which are critical to see (e.g. activation links).
    $entry = implode("\n", [
        str_repeat('=', 70),
        'LOCAL MAIL LOG — ' . date('Y-m-d H:i:s'),
        str_repeat('-', 70),
        'TO:      ' . $to_name . ' <' . $to_email . '>',
        'FROM:    ' . MAIL_FROM_NAME . ' <' . MAIL_FROM_ADDRESS . '>',
        'SUBJECT: ' . $subject,
        str_repeat('-', 70),
        strip_tags($html_body),
        str_repeat('=', 70),
        '',
    ]);

    // FILE_APPEND adds to the end of the file (doesn't overwrite previous emails).
    // LOCK_EX prevents race conditions if two emails are logged simultaneously.
    $result = file_put_contents($log_file, $entry . "\n", FILE_APPEND | LOCK_EX);

    return $result !== false;
}


// -----------------------------------------------------------------------
// EMAIL TEMPLATES
// Each function composes and sends a specific type of transactional email.
// All HTML uses inline styles for maximum compatibility across email clients.
// -----------------------------------------------------------------------

/**
 * Send the account activation email to a newly registered user.
 *
 * The activation link contains the token stored in user_activation_tokens.
 * The link expires after 24 hours.
 *
 * On local: the link appears in logs/mail.log — click it to activate the account.
 *
 * @param string $to_email  The user's email address
 * @param string $to_name   The user's first name (for personalisation)
 * @param string $token     The activation token from the database
 * @return bool
 */
function send_activation_email(string $to_email, string $to_name, string $token): bool
{
    $activation_link = APP_URL . '/account/activate?token=' . urlencode($token);
    $safe_name       = htmlspecialchars($to_name, ENT_QUOTES, 'UTF-8');
    $safe_link       = htmlspecialchars($activation_link, ENT_QUOTES, 'UTF-8');
    $year            = date('Y');

    $subject   = 'Activate Your Tour de Roar Account';
    $html_body = <<<HTML
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
        <div style="background: linear-gradient(135deg, #FF6B1A, #E53E3E, #805AD5); padding: 32px; text-align: center;">
            <h1 style="color: white; margin: 0; font-size: 28px; font-weight: bold;">Tour de Roar</h1>
            <p style="color: rgba(255,255,255,0.85); margin: 8px 0 0; font-size: 14px;">Every child is our child.</p>
        </div>
        <div style="padding: 36px; background: #ffffff;">
            <h2 style="color: #805AD5; margin-top: 0;">Welcome, {$safe_name}!</h2>
            <p style="color: #4a5568; line-height: 1.7;">
                Thank you for creating your Tour de Roar account. One more step — please click the button
                below to verify your email address and activate your account.
            </p>
            <div style="text-align: center; margin: 36px 0;">
                <a href="{$safe_link}"
                   style="background: linear-gradient(45deg, #FF6B1A, #E53E3E); color: white;
                          padding: 16px 36px; text-decoration: none; border-radius: 8px;
                          font-weight: bold; font-size: 16px; display: inline-block; letter-spacing: 0.5px;">
                    Activate My Account
                </a>
            </div>
            <p style="color: #718096; font-size: 14px; line-height: 1.6;">
                <strong>This link expires in 24 hours.</strong><br>
                If you did not create this account, you can safely ignore this email.
            </p>
            <p style="color: #a0aec0; font-size: 12px; word-break: break-all; margin-top: 16px;">
                Having trouble? Copy this link into your browser:<br>
                {$safe_link}
            </p>
        </div>
        <div style="background: #1a202c; padding: 20px; text-align: center;">
            <p style="color: #718096; font-size: 12px; margin: 0;">
                &copy; {$year} Tour de Roar. All rights reserved.
            </p>
        </div>
    </div>
HTML;

    return send_mail($to_email, $to_name, $subject, $html_body);
}

/**
 * Send a password reset email with a time-limited link.
 *
 * The reset link contains the token stored in password_reset_tokens.
 * The link expires after 1 hour.
 *
 * On local: the link appears in logs/mail.log — click it to reset the password.
 *
 * @param string $to_email  The user's email address
 * @param string $to_name   The user's first name
 * @param string $token     The reset token from the database
 * @return bool
 */
function send_password_reset_email(string $to_email, string $to_name, string $token): bool
{
    $reset_link = APP_URL . '/account/reset-password?token=' . urlencode($token);
    $safe_name  = htmlspecialchars($to_name, ENT_QUOTES, 'UTF-8');
    $safe_link  = htmlspecialchars($reset_link, ENT_QUOTES, 'UTF-8');
    $year       = date('Y');

    $subject   = 'Reset Your Tour de Roar Password';
    $html_body = <<<HTML
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
        <div style="background: linear-gradient(135deg, #FF6B1A, #E53E3E, #805AD5); padding: 32px; text-align: center;">
            <h1 style="color: white; margin: 0; font-size: 28px; font-weight: bold;">Tour de Roar</h1>
            <p style="color: rgba(255,255,255,0.85); margin: 8px 0 0; font-size: 14px;">Every child is our child.</p>
        </div>
        <div style="padding: 36px; background: #ffffff;">
            <h2 style="color: #3182CE; margin-top: 0;">Password Reset Request</h2>
            <p style="color: #4a5568; line-height: 1.7;">
                Hi {$safe_name}, we received a request to reset the password for your Tour de Roar account.
                Click the button below to choose a new password.
            </p>
            <div style="text-align: center; margin: 36px 0;">
                <a href="{$safe_link}"
                   style="background: linear-gradient(45deg, #3182CE, #805AD5); color: white;
                          padding: 16px 36px; text-decoration: none; border-radius: 8px;
                          font-weight: bold; font-size: 16px; display: inline-block; letter-spacing: 0.5px;">
                    Reset My Password
                </a>
            </div>
            <p style="color: #718096; font-size: 14px; line-height: 1.6;">
                <strong>This link expires in 1 hour.</strong><br>
                If you did not request this, your password will not change — you can safely ignore this email.
            </p>
            <p style="color: #a0aec0; font-size: 12px; word-break: break-all; margin-top: 16px;">
                Having trouble? Copy this link into your browser:<br>
                {$safe_link}
            </p>
        </div>
        <div style="background: #1a202c; padding: 20px; text-align: center;">
            <p style="color: #718096; font-size: 12px; margin: 0;">
                &copy; {$year} Tour de Roar. All rights reserved.
            </p>
        </div>
    </div>
HTML;

    return send_mail($to_email, $to_name, $subject, $html_body);
}

/**
 * Simple wrapper for send_mail() - accepts email and subject with HTML body
 * Used by webhook handlers for payment confirmations
 *
 * @param string $to_email
 * @param string $subject
 * @param string $html_body
 * @return bool
 */
function send_email(string $to_email, string $subject, string $html_body): bool
{
    // Extract name from email if possible (before @)
    $name_part = explode('@', $to_email)[0];
    $to_name = ucfirst($name_part);
    
    // Wrap HTML body in Tour de Roar email template
    $year = date('Y');
    
    $wrapped_body = <<<HTML
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
        <div style="background: linear-gradient(135deg, #FF6B1A, #E53E3E, #805AD5); padding: 32px; text-align: center;">
            <h1 style="color: white; margin: 0; font-size: 28px; font-weight: bold;">Tour de Roar</h1>
            <p style="color: rgba(255,255,255,0.85); margin: 8px 0 0; font-size: 14px;">Every child is our child.</p>
        </div>
        <div style="padding: 36px; background: #ffffff; color: #4a5568; line-height: 1.7;">
            {$html_body}
        </div>
        <div style="background: #1a202c; padding: 20px; text-align: center;">
            <p style="color: #718096; font-size: 12px; margin: 0;">
                &copy; {$year} Tour de Roar. All rights reserved.<br>
                2860 South State Hwy 161, Ste 160 211, Grand Prairie, TX 75052<br>
                Phone: (972) 979-4608
            </p>
        </div>
    </div>
HTML;
    
    return send_mail($to_email, $to_name, $subject, $wrapped_body);
}

