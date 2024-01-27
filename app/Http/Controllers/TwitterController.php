<?php


namespace App\Http\Controllers;


use Illuminate\Support\Facades\Http;
use Abraham\TwitterOAuth\TwitterOAuth;
use Exception;
use Performance\Performance;
use Illuminate\Support\Facades\Log;

class TwitterController
{
    private $connection;

    public function __construct()
    {
        $this->connection = new TwitterOAuth(
            env('TWITTER_CONSUMER_KEY'), 
            env('TWITTER_CONSUMER_SECRET'), 
            env('TWITTER_ACCESS_TOKEN'), 
            env('TWITTER_ACCESS_TOKEN_SECRET')
        );
    }

    public function getPokemons(string $resposta, string $usuarioResposta, string $tweetOriginal, string $usuarioOriginal): array
    {
        $pokemons[] = $this->getEquipe($tweetOriginal, $usuarioOriginal);
        $pokemons[] = $this->getEquipe($resposta, $usuarioResposta);

        return $pokemons;
    }

    public function responderTweet(int $tweet, array $vencedor, string $username, array $imagens, int $tipo = 0):void
    {
        try {
            $mediaIDstr = $this->getImagensTweet($imagens);
            $treinadorVencedor = $vencedor['treinador'] == $username ? "Você" : "@{$vencedor['treinador']}";

            $msg = "@$username A Rinha de Pokémon acabou: {$treinadorVencedor} e {$vencedor['pokemon']['nome']} venceram! #Pokemon #PokemonBattle #BatalhaPokemon #RinhaDePokemon";
            if($tipo){
                $msg = $vencedor['treinador'] == $username ? "@$username Você acaba de capturar o Pokémon!" : "@$username O Pokémon escapou!";
            }

            $parameters = [
                'status' => $msg,
                'media_ids' => $mediaIDstr,
                'in_reply_to_status_id' => $tweet,
                'username' => $username
            ];

            $this->connection->post('statuses/update', $parameters);
        }catch (\Throwable $throwable){
            throw new Exception("Erro ao processar tweet! Mensagem: {$throwable->getMessage()}", 500);
        }

    }

    public function getTweet(int $id): object
    {
        return $this->connection->get("statuses/show/$id");
    }

    private function getEquipe(string $texto, string $treinador):array
    {
        $equipe=[];
        $arrTexto = explode("Pokemon: ", $texto);

        if(empty($arrTexto[1])){
            $arrTexto = explode("Pokémon: ", $texto);
        }

        $pokemonsDaEquipe = explode(',', $arrTexto[1]);

        foreach ($pokemonsDaEquipe as $membro){
            array_push($equipe, ['pokemon'=>$membro, 'treinador'=>$treinador]);
        }

        return $equipe;
    }

    private function getImagensTweet(array $imagens):string
    {
        $mediaIDS = [];

        foreach($imagens AS $key => $media_path) {
            $mediaOBJ = $this->connection->upload('media/upload', ['media' => $media_path]);
            array_push($mediaIDS, $mediaOBJ->media_id_string);
        }

        return implode(',', $mediaIDS);
    }

}
