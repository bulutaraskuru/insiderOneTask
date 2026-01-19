<?php

namespace App\Providers;

use App\Console\Commands\SendMessagesCommand;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Repositories\Contracts\MessageSendRepositoryInterface;
use App\Repositories\CustomerRepository;
use App\Repositories\MessageRepository;
use App\Repositories\MessageSendRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(MessageRepositoryInterface::class, MessageRepository::class);
        $this->app->bind(MessageSendRepositoryInterface::class, MessageSendRepository::class);
        $this->app->bind(CustomerRepositoryInterface::class, CustomerRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //

        if ($this->app->runningInConsole()) {
            $this->commands([
                SendMessagesCommand::class,
            ]);
        }
    }
}
