<?php

namespace App\Console\Commands;

use App\Http\Controllers\BatalhaController;
use App\Jobs\ProcessTweet;
use App\Services\Output;
use Illuminate\Console\Command;
use App\Http\Controllers\TwitterController;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Intervention\Image\ImageManager as Image;
use Mpdf\Mpdf;

class ListenForHashTagsTest extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitter:listen-for-hash-tags-test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listen for hashtags being used on TwitterController';
    private array $tweet;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(): void
    {
        $text = "@PokemonRinha desafio aceito! Pokemon:  Blastoise";
        $nome = "PokemonRinha2";
        $tweetOriginal = "@PokemonRinha Desafio o Fulano! Pokemon: Charizard";
        $nomeOriginal = "PokemonRinha1";
        $id = 1367592659087417344;

        var_dump("Entrou no handle.");

        $twitterController = new TwitterController();

        $pokemons = $twitterController->getPokemons($text, $nome, $tweetOriginal, $nomeOriginal);

        var_dump("Identificou os pokemons...");

        $batalhaPokemon = new BatalhaController($pokemons);
        $batalha = $batalhaPokemon->rinhaPokemon();

        var_dump("Fez a batalha...");

        $output = new Output($batalha['batalha']);
        $caminhoIMG = $output->getBatalhaOutput();

        if($caminhoIMG['status'] === true){

            unset($caminhoIMG['status']);

//            $twitterController = new TwitterController();
//            $twitterController->responderTweet($this->tweet['id'], $batalha['vencedor'], $this->tweet['user']['screen_name'], $caminhoIMG);
//            $output->deleteOutputs();

            var_dump("Salvou a imagem, twittou e apagou a imagem.");
        }

        var_dump("Finalizou o processo");
        unset($this->tweet);

    }
}
