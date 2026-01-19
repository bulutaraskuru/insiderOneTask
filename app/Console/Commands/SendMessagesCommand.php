<?php

namespace App\Console\Commands;

use App\Services\MessageService;
use Illuminate\Console\Command;

class SendMessagesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'messages:send {--limit=2}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Müşterilere bekleyen mesajları gönderir';

    public function __construct(private MessageService $messageService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        $this->info("Mesaj gönderme işlemi başlatılıyor...");
        $this->info("Batch limiti: {$limit} mesaj");

        $sentCount = $this->messageService->sendMessages($limit);


        if ($sentCount > 0) {
            $this->info("{$sentCount} mesaj başarıyla gönderildi");
            return Command::SUCCESS;
        }

        $this->warn("Bekleyen mesaj yok");
        return Command::SUCCESS;



        return Command::SUCCESS;
    }
}
