<?php

namespace App\Console\Commands;

use App\Models\ConversionHistory;
use Illuminate\Console\Command;

class CleanOldConversions extends Command
{
    protected $signature = 'conversions:clean {--hours=48 : Delete conversions older than this many hours}';

    protected $description = 'Delete conversion history and ZIP files older than 48 hours';

    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $cutoff = now()->subHours($hours);

        $old = ConversionHistory::where('created_at', '<', $cutoff)->get();

        if ($old->isEmpty()) {
            $this->info('No old conversions found.');
            return self::SUCCESS;
        }

        $deleted = 0;

        foreach ($old as $record) {
            $path = $record->full_path;
            if (file_exists($path)) {
                unlink($path);
            }
            $record->delete();
            $deleted++;
        }

        $this->info("Deleted {$deleted} old conversion(s).");
        return self::SUCCESS;
    }
}
