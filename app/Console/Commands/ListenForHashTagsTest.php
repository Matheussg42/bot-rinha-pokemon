<?php

namespace App\Console\Commands;

use App\Http\Controllers\BatalhaController;
use App\Jobs\Batalha;
use App\Services\Output;
use Illuminate\Console\Command;
use App\Http\Controllers\TwitterController;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Performance\Performance;
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
        $text = "@PokemonRinha desafio aceito! Pokemon:  Venusaur";
        $nome = "PokemonRinha2";
        $tweetOriginal = "@PokemonRinha Desafio o Fulano! Pokemon: Charizard";
        $nomeOriginal = "PokemonRinha1";
        $id = 1367592659087417344;

        var_dump("Entrou no handle.");

        $twitterController = new TwitterController();
//        $tweetOriginal = $twitterController->getTweet($id);
        $pokemons = $twitterController->getPokemons($text, $nome, $tweetOriginal, $nomeOriginal);

        var_dump("Identificou os pokemons...");

        $batalhaPokemon = new BatalhaController($pokemons);

        if($batalhaPokemon->getStatus()) {

            $batalha = $batalhaPokemon->rinhaPokemon();

            var_dump("Fez a batalha...");

            $output = new Output($batalha['batalha']);
            $caminhoIMG = $output->getBatalhaOutput();

    //        if($caminhoIMG['status'] === true){
    //
    //            unset($caminhoIMG['status']);

    //            $twitterController = new TwitterController();
    //            $twitterController->responderTweet($this->tweet['id'], $batalha['vencedor'], $this->tweet['user']['screen_name'], $caminhoIMG);
    //            $output->deleteOutputs();

    //            var_dump("Salvou a imagem, twittou e apagou a imagem.");
    //        }

            var_dump("Finalizou o processo");
        }

    }
}
