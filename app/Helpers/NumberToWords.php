<?php

namespace App\Helpers;

class NumberToWords
{
    /**
     * Convierte un número a palabras en español.
     *
     * @param float|int $numero El número a convertir
     * @param string $moneda La moneda (ej. "PESOS COLOMBIANOS")
     * @return string El número convertido a palabras
     */
    public static function convertir($numero, $moneda = "")
    {
        $numero = (float) $numero;
        
        $parte_entera = floor($numero);
        $parte_decimal = round(($numero - $parte_entera) * 100, 0);
        
        $palabras = self::numeroALetras($parte_entera, $moneda);
        
        if ($parte_decimal > 0) {
            $palabras .= " CON " . self::numeroALetras($parte_decimal, "");
            if ($moneda) {
                $palabras .= " CENTAVOS DE " . $moneda;
            } else {
                $palabras .= " CENTAVOS";
            }
        } else if ($moneda) {
            $palabras .= " " . $moneda;
        }
        
        return $palabras;
    }
    
    /**
     * Convierte un número a letras.
     *
     * @param int $numero
     * @param string $moneda
     * @return string
     */
    private static function numeroALetras($numero, $moneda)
    {
        $unidades = ["", "UN", "DOS", "TRES", "CUATRO", "CINCO", "SEIS", "SIETE", "OCHO", "NUEVE"];
        $decenas = ["", "DIEZ", "VEINTE", "TREINTA", "CUARENTA", "CINCUENTA", "SESENTA", "SETENTA", "OCHENTA", "NOVENTA"];
        $centenas = ["", "CIENTO", "DOSCIENTOS", "TRESCIENTOS", "CUATROCIENTOS", "QUINIENTOS", "SEISCIENTOS", "SETECIENTOS", "OCHOCIENTOS", "NOVECIENTOS"];
        $especiales = ["DIEZ" => ["", "ONCE", "DOCE", "TRECE", "CATORCE", "QUINCE", "DIECISEIS", "DIECISIETE", "DIECIOCHO", "DIECINUEVE"],
                      "VEINTE" => ["", "VEINTIUN", "VEINTIDOS", "VEINTITRES", "VEINTICUATRO", "VEINTICINCO", "VEINTISEIS", "VEINTISIETE", "VEINTIOCHO", "VEINTINUEVE"]];
        
        if ($numero == 0) {
            return "CERO";
        }
        
        if ($numero == 1 && $moneda) {
            return "UN";
        }
        
        $texto = "";
        
        // Procesamiento para números de millones
        if ($numero >= 1000000) {
            $millones = floor($numero / 1000000);
            $resto = $numero % 1000000;
            
            if ($millones == 1) {
                $texto .= "UN MILLON";
            } else {
                $texto .= self::numeroALetras($millones, "") . " MILLONES";
            }
            
            if ($resto > 0) {
                $texto .= " ";
                $numero = $resto;
            } else {
                return $texto;
            }
        }
        
        // Procesamiento para números de miles
        if ($numero >= 1000) {
            $miles = floor($numero / 1000);
            $resto = $numero % 1000;
            
            if ($miles == 1) {
                $texto .= "MIL";
            } else {
                $texto .= self::numeroALetras($miles, "") . " MIL";
            }
            
            if ($resto > 0) {
                $texto .= " ";
                $numero = $resto;
            } else {
                return $texto;
            }
        }
        
        // Procesamiento para números de centenas
        if ($numero >= 100) {
            if ($numero == 100) {
                return $texto . "CIEN";
            }
            
            $resto = $numero % 100;
            $texto .= $centenas[floor($numero / 100)];
            
            if ($resto > 0) {
                $texto .= " ";
                $numero = $resto;
            } else {
                return $texto;
            }
        }
        
        // Procesamiento para números entre 10 y 99
        if ($numero >= 10 && $numero <= 99) {
            $decena = $decenas[floor($numero / 10)];
            $unidad = $numero % 10;
            
            if (isset($especiales[$decena]) && $unidad > 0) {
                $texto .= $especiales[$decena][$unidad];
            } else {
                $texto .= $decena;
                if ($unidad > 0) {
                    $texto .= " Y " . $unidades[$unidad];
                }
            }
            
            return $texto;
        }
        
        // Números menores a 10
        return $texto . $unidades[$numero];
    }
} 
 