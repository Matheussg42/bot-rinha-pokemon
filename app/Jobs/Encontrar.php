<?php

namespace App\Jobs;

use App\Http\Controllers\BatalhaController;
use App\Http\Controllers\PokemonController;
use App\Http\Controllers\TwitterController;
use App\Services\Output;
use Egulias\EmailValidator\Exception\ExpectingQPair;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Log\Logger;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class Encontrar implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $tweet;

    /**
     * Create a new job instance.
     *
     * @param $tweet
     */
    public function __construct($tweet)
    {
        $this->tweet = $tweet;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            var_dump("Entrou no handle.");
            $twitterController = new TwitterController();
            $idPokemon = mt_rand(1, 300);
            var_dump("Escolher o Pokemon {$idPokemon}");

            $pokemonController = new PokemonController();
            $pokemon = $pokemonController->getNomePokemon($idPokemon);

            $tweetOriginal = $twitterController->getTweet($this->tweet['in_reply_to_status_id']);

            $twitterController = new TwitterController();
            $twitterController->responderEncontrar($this->tweet['id'], $this->tweet['user']['screen_name'], $tweetOriginal->user->screen_name, $pokemon);
            var_dump("Postou");
        } catch (Exception $e) {
            echo 'Exceção capturada: ',  $e->getMessage(), "\n";
        }

    }
}
