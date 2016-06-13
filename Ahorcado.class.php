<?php

class Ahorcado{
    private $nombre_jugador = 'E';
    private $palabra = '';
    private $palabra_progreso = '';
    private $palabra_descubierta = '';
    private $vidas = 0;
    private $intentos = 0;
    private $banco_palabras = ['murcielago', 'enfermero', 'espiral', 'caleidoscopio'];
    private $meta = 0;
    private $partidaTerminada = false;

    public function __construct($nombre_jugador = 'anonimo'){
        $this->nombre_jugador = $nombre_jugador;
        $this->nuevoJuego();
    }
 
    public function nuevoJuego(){
        $this->palabra = $this->banco_palabras[rand(0,(count($this->banco_palabras)-1))];
        $this->palabra_progreso = $this->palabra;
        $this->vidas = 5;
        $this->intentos = 0;
        $this->meta = strlen($this->palabra);
        $this->palabra_descubierta = rtrim(str_repeat('_ ', $this->meta));
        $this->partidaTerminada = false;
    }

    public function jugar($letra){
        $letra = mb_strtolower($letra);
        if($this->partidaTerminada){
           return [
                   'resultado' => -2,
                   'vidasRestantes' => -1,
                   'descubierto' => "ERROR: PARTIDA TERMINADA"
                 ];
        }
     
     
        $this->palabra_progreso = str_ireplace($letra, '*', $this->palabra_progreso, $count);
        if($count == 0){
            --$this->vidas;
        }
        else{
            $this->actualizarProgreso();
            $this->meta -= $count;
        }
        ++$this->intentos;
        return [
                 'resultado' => $this->verificarResultado(),
                 'vidasRestantes' => $this->vidas,
                 'descubierto' => $this->palabra_descubierta
               ];
    }

    public function adivinarPalabra($palabra){
        $palabra = mb_strtolower($palabra);
        if($this->partidaTerminada){
           return [
                   'resultado' => -2,
                   'vidasRestantes' => -1,
                   'descubierto' => "ERROR: PARTIDA TERMINADA"
                 ];
        }
     
        if ($palabra == $this->palabra)
        {
           for($i = 0; $i < strlen($this->palabra); ++$i){
                  $this->palabra_descubierta[2*$i] = $this->palabra[$i];
           }
           return [
                   'resultado' => 1,
                   'vidasRestantes' => $this->vidas,
                   'descubierto' => $this->palabra_descubierta
                 ];              
        }
        else 
        {   
           if ($this->vidas > 3){
              $this->vidas -= 3;
           }
           else{
              $this->intentos += 5;
              $this->vidas -= 1;
           }
           return [
                    'resultado' => $this->verificarResultado(),
                    'vidasRestantes' => $this->vidas,
                    'descubierto' => $this->palabra_descubierta
                  ];
        }
    }
 
    private function verificarResultado(){
        if($this->vidas == 0){
            return -1; // partida terminada, perdida
        }
        if($this->meta == 0){
            return 1; // partida terminada, ganada
        }
        return 0; //sigue jugando
    }
    
    private function actualizarProgreso(){
        for($i = 0; $i < strlen($this->palabra); ++$i){
            if($this->palabra_progreso[$i] === '*'){
               $this->palabra_descubierta[2*$i] = $this->palabra[$i];
            }
        }
    }
 
    public function ingresarNombreJugador($nombre = ''){
        $this->nombre_jugador = $nombre;
    }

    public function obtenerResultado(){
      return $this->meta * 10000 + $this->vidas * 1000 - $this->intentos * 500;
    }
 
   public function obtenerEstado(){
    return [
             'vidasRestantes' => $this->vidas,
             'descubierto' => $this->palabra_descubierta
           ];
   }
 
}


