<?php

namespace App\Console\Commands;

use App\Http\Controllers\BatalhaController;
use App\Jobs\ProcessTweet;
use App\Services\Output;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Spatie\LaravelTwitterStreamingApi\TwitterStreamingApiFacade as TwitterStreamingApi;
use App\Http\Controllers\TwitterController;

class ListenForHashTags extends Command
{
    use DispatchesJobs;

    private $palavras = [
        '@PokemonRinha'
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitter:listen-for-hash-tags';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listen for hashtags being used on TwitterController';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(): void
    {
        TwitterStreamingApi::publicStream()
            ->whenHears($this->palavras, function (array $tweet) {
                var_dump("{$tweet['user']['name']} twittou e marcou a gente!" . PHP_EOL);
                if(!empty($tweet['in_reply_to_status_id_str'])){
                    $job = (new ProcessTweet($tweet))->onQueue('batalhas');
                    $this->dispatch($job);
                }
            })
            ->startListening();
    }

}
