<?php


namespace App\Http\Controllers;


use Illuminate\Support\Facades\Http;
use Abraham\TwitterOAuth\TwitterOAuth;
use Illuminate\Support\Facades\Log;

class TwitterController
{
    private $connection;

    public function __construct()
    {
        $this->connection = new TwitterOAuth(env('TWITTER_CONSUMER_KEY'), env('TWITTER_CONSUMER_SECRET'), env('TWITTER_ACCESS_TOKEN'), env('TWITTER_ACCESS_TOKEN_SECRET'));
    }

    public function getPokemons(string $resposta, string $usuarioResposta, string $tweetOriginal, string $usuarioOriginal): array
    {
        $pokemons[] = $this->getEquipe($tweetOriginal, $usuarioOriginal);
        $pokemons[] = $this->getEquipe($resposta, $usuarioResposta);

        return $pokemons;
    }

    public function responderTweet(int $tweet, array $vencedor, string $username, array $imagens):void
    {
        try {
            $mediaIDstr = $this->getImagensTweet($imagens);

            $parameters = [
                'status' => "Parabéns @{$vencedor['treinador']} você e o seu {$vencedor['pokemon']['nome']} venceram!",
                'media_ids' => $mediaIDstr,
                'in_reply_to_status_id' => $tweet,
                'username' => $username
            ];

            $this->connection->post('statuses/update', $parameters);
        }catch (\Throwable $throwable){
            echo $throwable;
        }

    }

    public function getTweet(int $id): array
    {
        return $this->connection->get("statuses/show/$id");
    }

    private function getEquipe(string $texto, string $treinador):array
    {
        $equipe=[];
        $arrTexto = explode("Pokemon: ", $texto);
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
