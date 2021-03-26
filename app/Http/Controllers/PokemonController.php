<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class PokemonController extends Controller
{
    private array $round = [];

    /**
     * Encontrar pokemon
     *
     * @param string $pokemon
     * @return array
     */
    public function getPokemon(string $pokemon):array
    {
        $response = Http::get("https://pokeapi.co/api/v2/pokemon/{$pokemon}");
        $response = $response->getBody();
        $response = json_decode($response);

        if(!empty($response)){
            throw new \Exception("Pokemon Não encontrado");
        }

        $type = $this->getTipoInfo($response->types[0]->type);
        $ataques = $this->getAtaque($response->moves);
        $stats = $this->getStats($response->stats);
        $cor = $this->getCor($response->species->url);

        return [
            'nome'=> $response->name,
            'peso'=> $response->weight,
            'altura'=> $response->height,
            'imagem'=> $response->sprites->front_default,
            'corTexto'=>$cor,
            'tipo'=> $type,
            'stats'=> $stats,
            'ataques'=> $ataques,
            'status' => 1,
            'inicia' => 0
        ];
    }

    public function defineProximoRound(array $equipe1, array $equipe2):array
    {
        $this->roundEquipe($equipe1);
        $this->roundEquipe($equipe2);

        $this->aplicarBonus();

        return $this->round;
    }

    private function aplicarBonus(): void
    {
        if(in_array($this->round[1]['pokemon']['tipo']['nome'], $this->round[0]['pokemon']['tipo']['infringeCriticoEm'])){
            $this->round[0]['pokemon']['ataques'][0]['dano']*=1.4;
            $this->round[0]['pokemon']['ataques'][1]['dano']*=1.4;
        }

        if(in_array($this->round[0]['pokemon']['tipo']['nome'], $this->round[1]['pokemon']['tipo']['infringeCriticoEm'])){
            $this->round[1]['pokemon']['ataques'][0]['dano']*=1.4;
            $this->round[1]['pokemon']['ataques'][1]['dano']*=1.4;
        }
    }

    private function getTipoInfo(object $type):array
    {
        $info=['nome'=>$type->name];
        $response = Http::get($type->url);
        $response = $response->getBody();
        $response = json_decode($response);

        $info['infringeCriticoEm'] = array_map(
            function($array) { return $array->name; },
            $response->damage_relations->double_damage_to
        );

        return $info;
    }

    private function getAtaque(array $ataqueJSOn):array
    {
        $countAtaques = count($ataqueJSOn) == 0 ? 0 : count($ataqueJSOn)-1;

        $move1 = $this->formatarAtaque($ataqueJSOn[mt_rand(0, $countAtaques)]->move->url);
        $move2 = $this->formatarAtaque($ataqueJSOn[mt_rand(0, $countAtaques)]->move->url);

        return [ $move1,$move2 ];
    }

    private function getStats($statsJSOn): array
    {
        $stats=[];
        foreach($statsJSOn as $stat){
            if($stat->stat->name == 'hp' || $stat->stat->name == 'speed'){
                $stats+= [$stat->stat->name => $stat->base_stat];
            }
        }

        return $stats;
    }

    private function getCor($url)
    {
        $response = Http::get($url);
        $response = $response->getBody();
        $response = json_decode($response);

        return $response->color->name;
    }

    private function formatarAtaque(string $endpointAtaque): array
    {
        $move = Http::get($endpointAtaque);
        $move = $move->getBody();
        $move = json_decode($move);

        return ['nome'=>$move->name,'dano' => $move->pp];
    }

    private function roundEquipe(array $equipe):void
    {
        foreach ($equipe as $pokemon){
            if($pokemon['pokemon']['stats']['hp'] > 0){
                array_push($this->round, $pokemon);
                break;
            }
        }

    }
}
