<?php
/**
 * Este archivo contiene la clase Ahorcado
 * @author Erick Madrigal Ríos <erickmadrigal.me>
 */

/**
 * Clase ahorcado, que contiene elementos para permitir jugar ahorcado, llevando un control de cada partida y de los puntajes ganadores
 * @author Erick Madrigal Ríos <erickmadrigalrios@gmail.com>
 * @copyright 2016 Erick Madrigal Ríos
 */
class Ahorcado{
    /**
     * Contiene las palabras que pueden salir en el ahorcado
     */
    const BANCO_PALABRAS = ['murcielago', 'enfermero', 'espiral', 'caleidoscopio'];
 
    /**
     * Contiene el nombre del jugador
     */
    private $nombre_jugador = '';
    
    /**
     * Contiene la palabra que se debe adivinar 
     */
    private $palabra = '';
    
    /**
     * Lleva un control de los caracteres encontrados en una palabra
     */
    private $palabra_progreso = '';
    
    /**
     * Contiene los aciertos del jugador y los espacios faltantes de las palabras
     */
    private $palabra_descubierta = '';
    
    /**
     * Contiene las vidas que le quedan al jugador
     */
    private $vidas = 0;
    
    /**
     * Contador de los intentos que lleva el jugador
     */
    private $intentos = 0;
    
    /**
     * La cantidad de caracteres de la palabra que el jugador debe adivinar.
     * Va bajando conforme a los aciertos del jugador, cuando llega a 0 el jugador gana.
     */
    private $meta = 0;
    
    /**
     * Indicador de una partida terminada, para evitar recibir más entradas
     */
    private $partidaTerminada = false;

    /**
     * Constructor
     * @param string $nombre_jugador Nombre del jugador
     */
    public function __construct($nombre_jugador = 'anonimo'){
        $this->nombre_jugador = $nombre_jugador;
        $this->nuevoJuego();
    }
 
    /**
     * Reinicializa el estado del juego y selecciona una nueva palabra, creando así un juego nuevo
     */
    public function nuevoJuego(){
        $this->palabra = Ahorcado::BANCO_PALABRAS[rand(0,(count(Ahorcado::BANCO_PALABRAS)-1))];
        $this->palabra_progreso = $this->palabra;
        $this->vidas = 5;
        $this->intentos = 0;
        $this->meta = strlen($this->palabra);
        $this->palabra_descubierta = rtrim(str_repeat('_ ', $this->meta));
        $this->partidaTerminada = false;
    }

    /**
     * Recibe una nueva letra que se espera la contenga la palabra que se trata de adivinar
     * @param  string $letra letra enviada para jugar
     * @return array  Estado del juego, contiene un indicador numérico para saber si la partida continua, las vidas restantes y lo que lleva descubierto de la palabra
     * @see Ahorcado::verificarResultado()
     */
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

    /**
     * Recibe una nueva letra que se espera sea la que se trata de adivinar
     * @param  string $palabra palabra enviada para tratar de acertar
     * @return array  Estado del juego, contiene un indicador numérico para saber si la partida continua, las vidas restantes y lo que se lleva descubierto de la palabra
     * @see Ahorcado::verificarResultado()
     */
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
            if( ($puntuacion = $this->obtenerPuntuacion()) > 0){
               $db = new PDO('sqlite:puntajes.sqlite');
               $db->exec("INSERT INTO Puntajes(Nombre, puntaje) VALUES ('{$this->nombre_jugador}', $puntuacion);");
            }
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
              $this->intentos += 8;
              $this->vidas -= 1;
           }
           return [
                    'resultado' => $this->verificarResultado(),
                    'vidasRestantes' => $this->vidas,
                    'descubierto' => $this->palabra_descubierta
                  ];
        }
    }
 
    /**
     * Verifica el resultado. Indica si la partida fue ganada, perdida o si continua abierta. En caso de haber ganado guarda el puntaje junto con el nombre del jugador en la base de datos.
     * @return integer -1 indica que la partida se perdió, 1 que se ganó, y 0 que debe seguir jugando
     */
    private function verificarResultado(){
        if($this->vidas == 0){
            return -1; // partida terminada, perdida
        }
        if($this->meta == 0){
            if( ($puntuacion = $this->obtenerPuntuacion()) > 0){
               $db = new PDO('sqlite:puntajes.sqlite');
               $db->exec("INSERT INTO Puntajes(Nombre, puntaje) VALUES ('{$this->nombre_jugador}', $puntuacion);");
            }
            return 1; // partida terminada, ganada
        }
        return 0; //sigue jugando
    }
    
    /**
     * Agrega a la palabra que puede ver el jugador, sus progresos
     */
    private function actualizarProgreso(){
        for($i = 0; $i < strlen($this->palabra); ++$i){
            if($this->palabra_progreso[$i] === '*'){
               $this->palabra_descubierta[2*$i] = $this->palabra[$i];
            }
        }
    }
 
    /**
     * Cambia el nombre del jugador actual
     * @param string $nombre Nombre del jugador
     */
    public function ingresarNombreJugador($nombre = 'anonimo'){
        $this->nombre_jugador = $nombre;
    }

    /**
     * Calcula y obtiene la puntuacion del jugador
     * @return integer La puntuacion del jugador 
     */
    public function obtenerPuntuacion(){
      return strlen($this->palabra) * 10000 + $this->vidas * 1000 - $this->intentos * 500;
    }
 
   /**
    * Obtiene un estado del juego. Util especialmente para preparar el escenario en el cliente antes de que el jugador comience a jugar
    * @return array [int : la cantidad de vidas restantes, string : el progreso del jugador de la palabra descubierta]
    */
   public function obtenerEstado(){
    return [
             'vidasRestantes' => $this->vidas,
             'descubierto' => $this->palabra_descubierta
           ];
   }

   /**
    * Obtiene un listado el nombre del jugador y su correspondiente puntaje (hasta 10) de aquellos en las que el puntaje sea el más alto.
    * @return array Array de pares ordenados (nombre, puntaje) 
    */
   public function obtenerPuntuacionesAltas(){    
     $db = new PDO('sqlite:puntajes.sqlite');
     $resultados = $db->query("SELECT nombre, puntaje FROM Puntajes ORDER BY puntaje DESC LIMIT 10");
     return $resultados->fetchAll();
   } 
 
}