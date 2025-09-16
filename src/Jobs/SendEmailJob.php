<?php

namespace St693ava\FilamentEmailManager\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\Middleware\ThrottlesExceptions;
use Illuminate\Queue\SerializesModels;
use St693ava\FilamentEmailManager\Models\EmailLog;
use St693ava\FilamentEmailManager\Services\EmailService;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $maxExceptions = 2;
    public int $timeout = 120;

    public function __construct(
        protected array $emailConfig
    ) {
        // Set queue name from config
        $this->onQueue(config('filament-email-manager.queue.queue', 'emails'));
    }

    /**
     * Execute the job
     */
    public function handle(EmailService $emailService): void
    {
        try {
            $emailService->send($this->emailConfig);
        } catch (\Exception $e) {
            // Log the error and re-throw for job failure handling
            \Log::error('Email sending failed in job', [
                'config' => $this->emailConfig,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            throw $e;
        }
    }

    /**
     * Get the middleware the job should pass through
     */
    public function middleware(): array
    {
        $serverId = $this->emailConfig['server_id'] ?? null;

        return [
            // Rate limiting based on SMTP server
            new RateLimited('smtp-server-' . $serverId),

            // Throttle exceptions to prevent overwhelming failed servers
            (new ThrottlesExceptions(5, 5 * 60))->by('smtp-server-' . $serverId),
        ];
    }

    /**
     * Handle a job failure
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error('Email job failed permanently', [
            'config' => $this->emailConfig,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);

        // If we have enough info, try to create a failed email log
        if (isset($this->emailConfig['server_id']) && isset($this->emailConfig['template_id'])) {
            try {
                EmailLog::create([
                    'smtp_server_id' => $this->emailConfig['server_id'],
                    'template_id' => $this->emailConfig['template_id'],
                    'recipients' => $this->emailConfig['recipients'] ?? [],
                    'subject' => 'Email job failed',
                    'body_html' => 'This email failed to send after multiple attempts.',
                    'status' => 'failed',
                    'failed_at' => now(),
                    'error_message' => $exception->getMessage(),
                    'metadata' => [
                        'job_failed' => true,
                        'attempts' => $this->attempts(),
                        'original_config' => $this->emailConfig,
                    ],
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to create email log for failed job', [
                    'original_error' => $exception->getMessage(),
                    'log_error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Determine the time at which the job should timeout
     */
    public function retryUntil(): \DateTime
    {
        return now()->addMinutes(30);
    }

    /**
     * Calculate the number of seconds to wait before retrying the job
     */
    public function backoff(): array
    {
        return [30, 60, 120]; // Wait 30s, then 1min, then 2min
    }

    /**
     * Get tags for monitoring/debugging
     */
    public function tags(): array
    {
        $tags = ['email'];

        if (isset($this->emailConfig['server_id'])) {
            $tags[] = 'smtp-server:' . $this->emailConfig['server_id'];
        }

        if (isset($this->emailConfig['template_id'])) {
            $tags[] = 'template:' . $this->emailConfig['template_id'];
        }

        return $tags;
    }
}