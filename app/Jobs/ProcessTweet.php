<?php

namespace App\Jobs;

use App\Http\Controllers\BatalhaController;
use App\Http\Controllers\TwitterController;
use App\Services\Output;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Log\Logger;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessTweet implements ShouldQueue
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
        var_dump("Entrou no handle.");

        $twitterController = new TwitterController();

        $tweetOriginal = $twitterController->getTweet($this->tweet['in_reply_to_status_id']);
        $pokemons = $twitterController->getPokemons($this->tweet['text'], $this->tweet['user']['screen_name'], $tweetOriginal['text'], $tweetOriginal['user']['screen_name']);

        var_dump("Identificou os pokemons...");

        $batalhaPokemon = new BatalhaController($pokemons);
        $batalha = $batalhaPokemon->batalha();

        var_dump("Fez a batalha...");

        $output = new Output($batalha['batalha']);
        $caminhoIMG = $output->getBatalhaOutput();

        if($caminhoIMG['status'] === true){

            unset($caminhoIMG['status']);

            $twitterController = new TwitterController();
            $twitterController->responderTweet($this->tweet['id'], $batalha['vencedor'], $this->tweet['user']['screen_name'], $caminhoIMG);
//            $output->deleteOutputs();

            var_dump("Salvou a imagem, twittou e apagou a imagem.");
        }

        var_dump("Finalizou o processo");
        unset($this->tweet);
    }
}
