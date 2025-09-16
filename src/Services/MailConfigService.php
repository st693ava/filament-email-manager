<?php

namespace St693ava\FilamentEmailManager\Services;

use Illuminate\Mail\Mailer;
use Illuminate\Support\Facades\Mail;
use St693ava\FilamentEmailManager\Models\SmtpServer;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

class MailConfigService
{
    /**
     * Create a mailer instance configured for a specific SMTP server
     */
    public function configureMailer(SmtpServer $server): Mailer
    {
        $config = $this->buildMailConfig($server);

        return Mail::build($config);
    }

    /**
     * Build mail configuration array for a SMTP server
     */
    public function buildMailConfig(SmtpServer $server): array
    {
        $config = [
            'transport' => 'smtp',
            'host' => $server->host,
            'port' => $server->port,
            'username' => $server->username,
            'password' => $server->password,
            'from' => [
                'address' => $server->from_email,
                'name' => $server->from_name,
            ],
        ];

        // Add encryption if specified
        if (!empty($server->encryption)) {
            $config['encryption'] = $server->encryption;
        }

        // Add additional settings from the server's settings JSON field
        if (!empty($server->settings)) {
            $config = array_merge($config, $server->settings);
        }

        return $config;
    }

    /**
     * Test SMTP server connection
     */
    public function testConnection(SmtpServer $server): array
    {
        try {
            $mailer = $this->configureMailer($server);
            $transport = $mailer->getSymfonyTransport();

            // For SMTP transport, try to start the connection
            if ($transport instanceof EsmtpTransport) {
                $transport->start();
                $transport->stop();
            }

            return [
                'success' => true,
                'message' => 'SMTP connection successful',
                'details' => [
                    'host' => $server->host,
                    'port' => $server->port,
                    'encryption' => $server->encryption,
                ]
            ];

        } catch (TransportException $e) {
            return [
                'success' => false,
                'message' => 'SMTP connection failed',
                'error' => $e->getMessage(),
                'details' => [
                    'host' => $server->host,
                    'port' => $server->port,
                    'encryption' => $server->encryption,
                ]
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Unexpected error during SMTP test',
                'error' => $e->getMessage(),
                'details' => [
                    'host' => $server->host,
                    'port' => $server->port,
                    'encryption' => $server->encryption,
                ]
            ];
        }
    }

    /**
     * Send a test email using the specified SMTP server
     */
    public function sendTestEmail(SmtpServer $server, string $toEmail, string $subject = 'Test Email'): array
    {
        try {
            $mailer = $this->configureMailer($server);

            $mailer->raw(
                'This is a test email sent from ' . $server->name . ' SMTP server.',
                function ($message) use ($toEmail, $subject, $server) {
                    $message->to($toEmail)
                           ->subject($subject)
                           ->from($server->from_email, $server->from_name);
                }
            );

            return [
                'success' => true,
                'message' => 'Test email sent successfully',
                'details' => [
                    'to' => $toEmail,
                    'from' => $server->from_email,
                    'server' => $server->name,
                ]
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to send test email',
                'error' => $e->getMessage(),
                'details' => [
                    'to' => $toEmail,
                    'from' => $server->from_email,
                    'server' => $server->name,
                ]
            ];
        }
    }

    /**
     * Get the default SMTP server
     */
    public function getDefaultServer(): ?SmtpServer
    {
        return SmtpServer::where('is_default', true)
                        ->where('is_active', true)
                        ->first();
    }

    /**
     * Set a server as default (removes default from others)
     */
    public function setAsDefault(SmtpServer $server): void
    {
        // Remove default status from all servers
        SmtpServer::where('is_default', true)->update(['is_default' => false]);

        // Set this server as default
        $server->update(['is_default' => true]);
    }

    /**
     * Get available encryption options
     */
    public function getEncryptionOptions(): array
    {
        return [
            '' => 'None',
            'ssl' => 'SSL',
            'tls' => 'TLS',
        ];
    }

    /**
     * Get common SMTP port suggestions based on encryption
     */
    public function getSuggestedPorts(): array
    {
        return [
            'none' => [25, 587, 2525],
            'tls' => [587, 2525],
            'ssl' => [465, 993],
        ];
    }

    /**
     * Validate SMTP server configuration
     */
    public function validateConfig(array $config): array
    {
        $errors = [];

        // Required fields
        $required = ['host', 'port', 'from_email', 'from_name'];
        foreach ($required as $field) {
            if (empty($config[$field])) {
                $errors[] = "Field '{$field}' is required";
            }
        }

        // Validate email format
        if (!empty($config['from_email']) && !filter_var($config['from_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format for from_email';
        }

        // Validate port
        if (!empty($config['port']) && (!is_numeric($config['port']) || $config['port'] < 1 || $config['port'] > 65535)) {
            $errors[] = 'Port must be a number between 1 and 65535';
        }

        // Validate encryption
        if (!empty($config['encryption']) && !in_array($config['encryption'], ['ssl', 'tls'])) {
            $errors[] = 'Encryption must be either ssl or tls';
        }

        // Validate rate limit
        if (isset($config['rate_limit_per_hour']) && (!is_numeric($config['rate_limit_per_hour']) || $config['rate_limit_per_hour'] < 0)) {
            $errors[] = 'Rate limit must be a non-negative number';
        }

        return $errors;
    }
}