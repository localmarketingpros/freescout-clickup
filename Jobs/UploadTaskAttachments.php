<?php

namespace Modules\ClickupIntegration\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\ClickupIntegration\Services\ClickupService;

class UploadTaskAttachments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $conversationId;

    public $taskId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($conversationId, $taskId)
    {
        $this->conversationId = $conversationId;
        $this->taskId = $taskId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        # Attaches all conversation attachments at this point to the task Id
        (new ClickupService)->addAttachments($this->conversationId, $this->taskId);
    }
}
