<?php

namespace App\Console\Commands;

use App\Http\Controllers\BatalhaController;
use App\Jobs\Batalha;
use App\Jobs\Capturar;
use App\Jobs\Encontrar;
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
                if(!empty($tweet['in_reply_to_status_id_str'])){

                    $texto = strtoupper($tweet['text']);

                    if((str_contains($texto , 'POKEMON:') || str_contains($texto, 'POKÉMON:'))){
                        $job = (new Batalha($tweet))->onQueue('batalhas');
                        $this->dispatch($job);
                    }
                }
            })->startListening();
    }

}
