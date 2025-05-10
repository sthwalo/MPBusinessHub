<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Carbon\Carbon;

class DatabaseBackup extends Command
{
    protected $signature = 'db:backup';
    protected $description = 'Backup the database';

    public function handle()
    {
        $this->info('Starting database backup...');
        
        $filename = 'backup-' . Carbon::now()->format('Y-m-d-H-i-s') . '.sql';
        $storageDir = Storage::disk('local')->path('backups');
        
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }
        
        $command = sprintf(
            'pg_dump -U %s -h %s %s > %s/%s',
            config('database.connections.pgsql.username'),
            config('database.connections.pgsql.host'),
            config('database.connections.pgsql.database'),
            $storageDir,
            $filename
        );
        
        $process = Process::fromShellCommandline($command);
        $process->setTimeout(3600);
        $process->run();
        
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        
        $this->info('Database backup completed successfully: ' . $filename);
        
        // Keep only the last 7 backups
        $files = glob($storageDir . '/*.sql');
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        
        if (count($files) > 7) {
            $filesToDelete = array_slice($files, 7);
            foreach ($filesToDelete as $file) {
                unlink($file);
                $this->info('Deleted old backup: ' . basename($file));
            }
        }
        
        return 0;
    }
}