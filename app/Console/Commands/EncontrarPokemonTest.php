<?php

namespace App\Console\Commands;

use App\Http\Controllers\BatalhaController;
use App\Http\Controllers\PokemonController;
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
    protected $signature = 'twitter:encontrar-pokemon-test';

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
        $text = "@PokemonRinha encontre pokemon";
        $nome = "matheussg42";
        $tweetOriginal = "@PokemonRinha Pokemon: arcanine";
        $nomeOriginal = "matheussg42";
        $id = 1423484548575375361;

        var_dump("Entrou no handle.");

        try {
            var_dump("Entrou no handle.");
            $twitterController = new TwitterController();
            $idPokemon = mt_rand(1, 300);
            var_dump("Escolher o Pokemon {$idPokemon}");

            $pokemonController = new PokemonController();
            $pokemon = $pokemonController->getNomePokemon($idPokemon);

            $twitterController = new TwitterController();
            $twitterController->responderEncontrar($id, $nome, $nomeOriginal, $pokemon);
            var_dump("Postou");
        } catch (Exception $e) {
            echo 'Exceção capturada: ',  $e->getMessage(), "\n";
        }

    }
}
