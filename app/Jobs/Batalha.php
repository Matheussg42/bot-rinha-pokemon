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

class Batalha implements ShouldQueue
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
            $twitterController = new TwitterController();

            $tweetOriginal = $twitterController->getTweet($this->tweet['in_reply_to_status_id']);
            $pokemons = $twitterController->getPokemons(
                $this->tweet['text'], 
                $this->tweet['user']['screen_name'], 
                $tweetOriginal->text, 
                $tweetOriginal->user->screen_name
            );

            $batalhaPokemon = new BatalhaController($pokemons);

            if($batalhaPokemon->getStatus()) {
                $batalha = $batalhaPokemon->rinhaPokemon();

                $output = new Output($batalha['batalha']);
                $caminhoIMG = $output->getBatalhaOutput();

                if($caminhoIMG['status'] === true){

                    unset($caminhoIMG['status']);

                    $twitterController = new TwitterController();
                    $twitterController->responderTweet($this->tweet['id'], $batalha['vencedor'], $this->tweet['user']['screen_name'], $caminhoIMG);
                    $output->deleteOutputs($caminhoIMG);
                }

                unset($this->tweet);
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage(), [
                'file' => $th->getFile(),
                'line'=> $th->getLine(), 
                'trace'=> $th->getTrace()
            ]);
        }
        
    }
}
