<?php

namespace St693ava\FilamentEmailManager\Services;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use St693ava\FilamentEmailManager\Jobs\SendEmailJob;
use St693ava\FilamentEmailManager\Models\EmailLog;
use St693ava\FilamentEmailManager\Models\EmailQueue;
use St693ava\FilamentEmailManager\Models\EmailTemplate;
use St693ava\FilamentEmailManager\Models\SmtpServer;

class EmailService
{
    public function __construct(
        protected MailConfigService $mailConfig,
        protected EmlGeneratorService $emlGenerator
    ) {}

    /**
     * Send email immediately using specified configuration
     */
    public function send(array $config): EmailLog
    {
        $server = SmtpServer::findOrFail($config['server_id']);
        $template = EmailTemplate::findOrFail($config['template_id']);

        // Validate server can send emails
        if (!$server->canSendEmail()) {
            throw new \Exception("SMTP server '{$server->name}' cannot send emails (inactive or rate limit exceeded)");
        }

        // Validate template data
        $errors = $template->validateData($config['data'] ?? []);
        if (!empty($errors)) {
            throw new \Exception('Template validation failed: ' . implode(', ', $errors));
        }

        // Create email log
        $log = $this->createEmailLog($server, $template, $config);

        try {
            // Build and send the email
            $this->sendEmail($log, $config);

            // Mark as sent and generate EML file
            $log->markAsSent();
            $emlPath = $this->emlGenerator->generateFromLog($log);
            $log->update(['eml_file_path' => $emlPath]);

        } catch (\Exception $e) {
            $log->markAsFailed($e->getMessage());
            throw $e;
        }

        return $log;
    }

    /**
     * Queue email for later sending
     */
    public function queue(array $config): EmailQueue
    {
        $server = SmtpServer::findOrFail($config['server_id']);
        $template = EmailTemplate::findOrFail($config['template_id']);

        // Validate template data
        $errors = $template->validateData($config['data'] ?? []);
        if (!empty($errors)) {
            throw new \Exception('Template validation failed: ' . implode(', ', $errors));
        }

        return EmailQueue::create([
            'smtp_server_id' => $server->id,
            'template_id' => $template->id,
            'recipients' => $config['recipients'],
            'data' => $config['data'] ?? [],
            'attachments' => $config['attachments'] ?? [],
            'priority' => $config['priority'] ?? 0,
            'scheduled_at' => $config['scheduled_at'] ?? null,
        ]);
    }

    /**
     * Send queued email
     */
    public function sendQueued(EmailQueue $queueItem): EmailLog
    {
        if (!$queueItem->isReady()) {
            throw new \Exception('Email queue item is not ready for sending');
        }

        $queueItem->markAsProcessing();

        try {
            $log = $this->send([
                'server_id' => $queueItem->smtp_server_id,
                'template_id' => $queueItem->template_id,
                'recipients' => $queueItem->recipients,
                'data' => $queueItem->data,
                'attachments' => $queueItem->attachments,
            ]);

            $queueItem->markAsSent();
            return $log;

        } catch (\Exception $e) {
            $queueItem->markAsFailed($e->getMessage());
            throw $e;
        }
    }

    /**
     * Send bulk emails with delay
     */
    public function sendBulk(array $config): array
    {
        $recipients = $config['recipients'];
        $delay = $config['delay_seconds'] ?? config('filament-email-manager.queue.delay_between_emails', 2);
        $jobs = [];

        foreach ($recipients as $index => $recipient) {
            $emailConfig = array_merge($config, [
                'recipients' => ['to' => [$recipient]],
            ]);

            $job = SendEmailJob::dispatch($emailConfig)
                ->delay(now()->addSeconds($index * $delay));

            $jobs[] = $job;
        }

        return $jobs;
    }

    /**
     * Preview email content without sending
     */
    public function preview(EmailTemplate $template, array $data = []): array
    {
        $errors = $template->validateData($data);
        if (!empty($errors)) {
            throw new \Exception('Template validation failed: ' . implode(', ', $errors));
        }

        return [
            'subject' => $template->processSubject($data),
            'html_content' => $template->processContent($data),
            'text_content' => $template->processTextContent($data),
            'placeholders_used' => array_keys($data),
            'missing_placeholders' => array_diff($template->getPlaceholderNames(), array_keys($data)),
        ];
    }

    /**
     * Create email log entry
     */
    protected function createEmailLog(SmtpServer $server, EmailTemplate $template, array $config): EmailLog
    {
        return EmailLog::create([
            'smtp_server_id' => $server->id,
            'template_id' => $template->id,
            'recipients' => $config['recipients'],
            'subject' => $template->processSubject($config['data'] ?? []),
            'body_html' => $template->processContent($config['data'] ?? []),
            'body_text' => $template->processTextContent($config['data'] ?? []),
            'attachments' => $config['attachments'] ?? [],
            'headers' => $config['headers'] ?? [],
            'metadata' => [
                'data_used' => $config['data'] ?? [],
                'sent_at_timestamp' => now()->timestamp,
            ],
            'status' => 'sending',
        ]);
    }

    /**
     * Actually send the email using the configured mailer
     */
    protected function sendEmail(EmailLog $log, array $config): void
    {
        $server = $log->smtpServer;
        $mailer = $this->mailConfig->configureMailer($server);

        // Create a mailable from the log data
        $mailable = new class($log, $config) extends Mailable {
            public function __construct(
                protected EmailLog $log,
                protected array $config
            ) {}

            public function build()
            {
                $mail = $this->subject($this->log->subject)
                           ->html($this->log->body_html);

                if ($this->log->body_text) {
                    $mail->text($this->log->body_text);
                }

                // Add recipients
                if (!empty($this->log->recipients['to'])) {
                    foreach ($this->log->recipients['to'] as $to) {
                        $mail->to($to);
                    }
                }

                if (!empty($this->log->recipients['cc'])) {
                    foreach ($this->log->recipients['cc'] as $cc) {
                        $mail->cc($cc);
                    }
                }

                if (!empty($this->log->recipients['bcc'])) {
                    foreach ($this->log->recipients['bcc'] as $bcc) {
                        $mail->bcc($bcc);
                    }
                }

                // Add attachments
                if (!empty($this->log->attachments)) {
                    foreach ($this->log->attachments as $attachment) {
                        if (isset($attachment['path'])) {
                            $mail->attach(
                                $attachment['path'],
                                [
                                    'as' => $attachment['name'] ?? basename($attachment['path']),
                                    'mime' => $attachment['mime'] ?? 'application/octet-stream',
                                ]
                            );
                        }
                    }
                }

                // Add custom headers
                if (!empty($this->log->headers)) {
                    foreach ($this->log->headers as $name => $value) {
                        $mail->withSymfonyMessage(function ($message) use ($name, $value) {
                            $message->getHeaders()->addTextHeader($name, $value);
                        });
                    }
                }

                return $mail;
            }
        };

        // Send the email
        $mailer->send($mailable);
    }

    /**
     * Check rate limit for server
     */
    protected function checkRateLimit(SmtpServer $server): void
    {
        if (!$server->canSendEmail()) {
            $remaining = $server->getRemainingRateLimit();
            throw new \Exception(
                "Rate limit exceeded for server '{$server->name}'. " .
                "Limit: {$server->rate_limit_per_hour} emails per hour. " .
                "Remaining: {$remaining}"
            );
        }
    }

    /**
     * Get email statistics
     */
    public function getStatistics(?\DateTimeInterface $from = null, ?\DateTimeInterface $to = null): array
    {
        $query = EmailLog::query();

        if ($from) {
            $query->where('created_at', '>=', $from);
        }

        if ($to) {
            $query->where('created_at', '<=', $to);
        }

        return [
            'total' => $query->count(),
            'sent' => $query->clone()->where('status', 'sent')->count(),
            'failed' => $query->clone()->where('status', 'failed')->count(),
            'pending' => $query->clone()->where('status', 'pending')->count(),
            'sending' => $query->clone()->where('status', 'sending')->count(),
            'success_rate' => $this->calculateSuccessRate($query),
            'by_server' => $this->getStatsByServer($query),
            'by_template' => $this->getStatsByTemplate($query),
        ];
    }

    /**
     * Calculate success rate percentage
     */
    protected function calculateSuccessRate($query): float
    {
        $total = $query->whereIn('status', ['sent', 'failed'])->count();
        if ($total === 0) {
            return 0.0;
        }

        $sent = $query->clone()->where('status', 'sent')->count();
        return round(($sent / $total) * 100, 2);
    }

    /**
     * Get statistics by SMTP server
     */
    protected function getStatsByServer($query): array
    {
        return $query->clone()
            ->selectRaw('smtp_server_id, status, COUNT(*) as count')
            ->groupBy('smtp_server_id', 'status')
            ->with('smtpServer:id,name')
            ->get()
            ->groupBy('smtp_server_id')
            ->map(function ($logs) {
                return [
                    'server_name' => $logs->first()->smtpServer->name ?? 'Unknown',
                    'stats' => $logs->pluck('count', 'status')->toArray(),
                ];
            })
            ->toArray();
    }

    /**
     * Get statistics by email template
     */
    protected function getStatsByTemplate($query): array
    {
        return $query->clone()
            ->selectRaw('template_id, status, COUNT(*) as count')
            ->whereNotNull('template_id')
            ->groupBy('template_id', 'status')
            ->with('template:id,name')
            ->get()
            ->groupBy('template_id')
            ->map(function ($logs) {
                return [
                    'template_name' => $logs->first()->template->name ?? 'Unknown',
                    'stats' => $logs->pluck('count', 'status')->toArray(),
                ];
            })
            ->toArray();
    }
}