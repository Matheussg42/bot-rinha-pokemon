<?php

namespace App\Http\Controllers;

use App\Http\Controllers\PokemonController;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Performance\Performance;

class BatalhaController extends Controller
{
    private array $equipe1;
    private array $equipe2;
    private array $round;
    private string $batalha;
    private array $vencedor;
    private bool $status = true;

    public function __construct(array $pokemons)
    {
        $this->equipe1 = $this->formarEquipe($pokemons[0]);
        $this->equipe2 = $this->formarEquipe($pokemons[1]);
    }

    public function getEquipe1(): array
    {
        return $this->equipe1;
    }

    public function getEquipe2(): array
    {
        return $this->equipe2;
    }

    public function getStatus(): bool
    {
        return $this->status;
    }

    public function getRound(): array
    {
        return $this->round;
    }

    public function rinhaPokemon(int $tipo = 1): array
    {
        $this->prepararbatalha();
        $this->defineQuemInicia();
        $this->apresentacaoRinha();
        $this->batalha();

        $result['batalha'] = $this->batalha;
        $result['vencedor'] = $this->vencedor;

        return $result;
    }

    private function formarEquipe($pokemons):array
    {
        $equipeFormada=[];
        $pokemonController = new PokemonController();

        foreach ($pokemons as $equipe){
            try {
                array_push($equipeFormada, ['treinador'=> $equipe['treinador'], 'pokemon'=> $pokemonController->getPokemon(strtolower(trim($equipe['pokemon'])))]);
            } catch (\Exception $e) {
                Log::error("Pokemon({$equipe['pokemon']}) do {$equipe['treinador']} não encontrado. Mensagem: {$e->getMessage()}");
                $this->status = false;
            }
        }

        return $equipeFormada;
    }

    private function prepararbatalha():void
    {
        $pokemonController = new PokemonController();
        $this->round = $pokemonController->defineProximoRound($this->equipe1, $this->equipe2);
    }

    private function defineQuemInicia():void
    {

        if($this->round[0]['pokemon']['stats']['speed'] > $this->round[1]['pokemon']['stats']['speed']){
            $this->round[0]['pokemon']['inicia'] = 1;
        } else {
            $this->round[1]['pokemon']['inicia'] = 1;
        }

        uasort ( $this->round , function ($a, $b) {
            return $a['pokemon']['inicia'] < $b['pokemon']['inicia'];
        });
    }

    private function apresentacaoRinha():void
    {
        $this->batalha = "<div class='row align-items-start'>
                    <div class='col'>
                        {$this->apresentacaoPokemon($this->round[0])}
                    </div>
                    <div class='col'>
                        {$this->apresentacaoPokemon($this->round[1])}
                    </div>
                </div>";

        $corInicia = config("services.colors.{$this->round[array_key_first($this->round)]['pokemon']['corTexto']}");
        $this->batalha .= "<p style='margin-bottom: 1px; color: {$corInicia}'>{$this->round[array_key_first($this->round)]['pokemon']['nome']} foi mais ágil e ataca primeiro!</p>";
    }

    private function batalha(): void
    {
        foreach ($this->round as $key => &$pokemon) {
            $adversario = $key == 1 ? 0 : 1;
            $valor = mt_rand(0, 43);
            $porcentagem = $this->round[$adversario]['pokemon']['stats']['speed'] / 10;

            $corPokemon = config("services.colors.{$pokemon['pokemon']['corTexto']}");
            $corAdversario = config("services.colors.{$this->round[$adversario]['pokemon']['corTexto']}");

            $this->batalha .= "<p style='margin-bottom: 1px; color: {$corPokemon}'>{$pokemon['pokemon']['nome']} se prepara para atacar <span style='color: {$corAdversario}'>{$this->round[$adversario]['pokemon']['nome']}</span> e...</p>";

            if ($valor > $porcentagem) {
                $ataqueRand = array_rand($pokemon['pokemon']['ataques'], 1);
                $this->batalha .= "<p style='margin-bottom: 1px; color: {$corPokemon}'>Efetivo! {$pokemon['pokemon']['nome']} usa {$pokemon['pokemon']['ataques'][$ataqueRand]['nome']} em <span style='color: {$corAdversario}'>{$this->round[$adversario]['pokemon']['nome']}</span> que sofre {$pokemon['pokemon']['ataques'][$ataqueRand]['dano']} de dano.</p>";

                $this->round[$adversario]['pokemon']['stats']['hp'] -= $pokemon['pokemon']['ataques'][$ataqueRand]['dano'];
                $this->round[$adversario]['pokemon']['stats']['hp'] = $this->round[$adversario]['pokemon']['stats']['hp'] <= 0 ? 0 : $this->round[$adversario]['pokemon']['stats']['hp'];

                $this->batalha .= "<p style='margin-bottom: 1px; color: {$corAdversario}'>{$this->round[$adversario]['pokemon']['nome']} possui {$this->round[$adversario]['pokemon']['stats']['hp']} de vida.</p>";
            } else {
                $this->batalha .= "<p style='margin-bottom: 1px; color: #333333'>{$this->round[$adversario]['pokemon']['nome']} esquiva!</p>";
            }

            if ($this->round[$adversario]['pokemon']['stats']['hp'] == 0) {
                $this->batalha .= "<p style='margin-bottom: 1px; color: {$corAdversario}'>{$this->round[$adversario]['pokemon']['nome']} desmaiou!</p>";
                $this->batalha .= "<p style='margin-top: -45px; margin-bottom: 0; height: 65px; color: {$corPokemon}'><img src='{$pokemon['pokemon']['imagem']}' /> {$pokemon['pokemon']['nome']}(HP {$pokemon['pokemon']['stats']['hp']}) venceu!</p>";
                $this->vencedor = $pokemon;
                break;
            }
        }

        if(empty($this->vencedor)){
            $this->batalha();
        }
    }

    private function apresentacaoPokemon(array $pokemon):string
    {
        $cor = config("services.colors.{$pokemon['pokemon']['corTexto']}");
        $retorno = "<p style='margin-top: -10px;margin-bottom: 0; height: 55px; color: {$cor}'>{$pokemon['treinador']} escolheu {$pokemon['pokemon']['nome']}(HP {$pokemon['pokemon']['stats']['hp']}) <img style='width: 70px' src='{$pokemon['pokemon']['imagem']}'></p>";
        if(!empty($pokemon['pokemon']['pokemonOriginal'])){
            $retorno = "<p style='margin-bottom: 0; height: 55px; color: #f368e0'>{$pokemon['treinador']} escolheu Ditto!</p><p style='margin-top: -55px;margin-bottom: 15px; height: 55px; color: #f368e0'>Ditto usa <b>Impostor</b> se transforma em <span style='color: {$cor}'>{$pokemon['pokemon']['nome']}(HP {$pokemon['pokemon']['stats']['hp']})</span> <img style='width: 70px' src='{$pokemon['pokemon']['imagem']}'></p>";
        }

        return $retorno;
    }

}
