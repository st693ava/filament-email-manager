<?php

namespace St693ava\FilamentEmailManager\Services;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Storage;
use St693ava\FilamentEmailManager\Models\EmailLog;
use Symfony\Component\Mime\Email;

class EmlGeneratorService
{
    /**
     * Generate EML file from a Laravel Mailable
     */
    public function generate(Mailable $mailable, int $logId): string
    {
        // Get the Symfony message from the mailable
        $symfonyMessage = $mailable->getSymfonyMessage();

        // Generate EML content
        $emlContent = $symfonyMessage->toString();

        // Store the file
        $path = $this->getEmlPath($logId);
        Storage::put($path, $emlContent);

        return $path;
    }

    /**
     * Generate EML file from email log data
     */
    public function generateFromLog(EmailLog $log): string
    {
        // Create a new Symfony Email instance
        $email = new Email();

        // Set basic email properties
        $email->subject($log->subject);
        $email->html($log->body_html);

        if ($log->body_text) {
            $email->text($log->body_text);
        }

        // Add recipients
        if (!empty($log->recipients['to'])) {
            foreach ($log->recipients['to'] as $to) {
                $email->addTo($to);
            }
        }

        if (!empty($log->recipients['cc'])) {
            foreach ($log->recipients['cc'] as $cc) {
                $email->addCc($cc);
            }
        }

        if (!empty($log->recipients['bcc'])) {
            foreach ($log->recipients['bcc'] as $bcc) {
                $email->addBcc($bcc);
            }
        }

        // Set from address
        if ($log->smtpServer) {
            $email->from($log->smtpServer->from_email, $log->smtpServer->from_name);
        }

        // Add custom headers
        if (!empty($log->headers)) {
            foreach ($log->headers as $name => $value) {
                $email->getHeaders()->addTextHeader($name, $value);
            }
        }

        // Add attachments (if they still exist on disk)
        if (!empty($log->attachments)) {
            foreach ($log->attachments as $attachment) {
                if (isset($attachment['path']) && Storage::exists($attachment['path'])) {
                    $email->attachFromPath(
                        Storage::path($attachment['path']),
                        $attachment['name'] ?? basename($attachment['path']),
                        $attachment['mime'] ?? 'application/octet-stream'
                    );
                }
            }
        }

        // Generate EML content
        $emlContent = $email->toString();

        // Store the file
        $path = $this->getEmlPath($log->id);
        Storage::put($path, $emlContent);

        // Update the log with the EML path
        $log->update(['eml_file_path' => $path]);

        return $path;
    }

    /**
     * Regenerate EML file for an existing email log
     */
    public function regenerate(EmailLog $log): string
    {
        // Delete old EML file if it exists
        if ($log->eml_file_path && Storage::exists($log->eml_file_path)) {
            Storage::delete($log->eml_file_path);
        }

        // Generate new EML file
        return $this->generateFromLog($log);
    }

    /**
     * Get the storage path for an EML file
     */
    protected function getEmlPath(int $logId): string
    {
        $directory = config('filament-email-manager.eml_storage.directory', 'emails/eml');
        return "{$directory}/{$logId}.eml";
    }

    /**
     * Get EML file content
     */
    public function getEmlContent(EmailLog $log): ?string
    {
        if (!$log->eml_file_path || !Storage::exists($log->eml_file_path)) {
            return null;
        }

        return Storage::get($log->eml_file_path);
    }

    /**
     * Download EML file
     */
    public function downloadEml(EmailLog $log): ?\Symfony\Component\HttpFoundation\StreamedResponse
    {
        if (!$log->eml_file_path || !Storage::exists($log->eml_file_path)) {
            // Try to regenerate if possible
            if ($log->isSuccessful()) {
                $this->generateFromLog($log);
            } else {
                return null;
            }
        }

        return Storage::download(
            $log->eml_file_path,
            "email-{$log->id}.eml",
            ['Content-Type' => 'message/rfc822']
        );
    }

    /**
     * Clean up old EML files
     */
    public function cleanupOldFiles(): int
    {
        $cleanupDays = config('filament-email-manager.eml_storage.cleanup_after_days', 30);
        $cutoffDate = now()->subDays($cleanupDays);

        // Get logs with EML files older than cutoff date
        $oldLogs = EmailLog::whereNotNull('eml_file_path')
            ->where('created_at', '<', $cutoffDate)
            ->get();

        $deletedCount = 0;

        foreach ($oldLogs as $log) {
            if (Storage::exists($log->eml_file_path)) {
                Storage::delete($log->eml_file_path);
                $log->update(['eml_file_path' => null]);
                $deletedCount++;
            }
        }

        return $deletedCount;
    }

    /**
     * Get EML file size
     */
    public function getEmlSize(EmailLog $log): ?int
    {
        if (!$log->eml_file_path || !Storage::exists($log->eml_file_path)) {
            return null;
        }

        return Storage::size($log->eml_file_path);
    }

    /**
     * Check if EML file exists
     */
    public function emlExists(EmailLog $log): bool
    {
        return $log->eml_file_path && Storage::exists($log->eml_file_path);
    }

    /**
     * Get storage disk configuration
     */
    protected function getStorageDisk(): string
    {
        return config('filament-email-manager.eml_storage.disk', 'local');
    }
}