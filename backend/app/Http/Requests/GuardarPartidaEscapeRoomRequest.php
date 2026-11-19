<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

class GuardarPartidaEscapeRoomRequest extends FormRequest
{
    /** Forma de las claves de «datos» y «opinion»: nombres de columna del CSV del juego. */
    private const PATRON_COLUMNA = '/^[a-z0-9_]{1,60}$/';

    public function authorize(): bool
    {
        // Juego público: cualquiera puede enviar las métricas de su partida.
        return true;
    }

    /**
     * Las columnas las decide el juego (cambian si cambia content.js), así que no se fija una lista:
     * solo se limitan su forma y su tamaño.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'codigo' => ['required', 'string', 'regex:/^OV-[A-Z0-9]{4}-[A-Z0-9]{4}$/'],
            'acepta_aviso' => ['accepted'],
            'version_aviso' => ['required', 'string', 'max:20'],

            'datos' => ['required', 'array', 'max:60', $this->columnasValidas()],
            'datos.*' => ['nullable', $this->valorSimple(100)],

            'opinion' => ['nullable', 'array', 'max:60', $this->columnasValidas()],
            'opinion.*' => ['nullable', $this->valorSimple(2000)],
        ];
    }

    private function columnasValidas(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            foreach (array_keys((array) $value) as $columna) {
                if (! is_string($columna) || preg_match(self::PATRON_COLUMNA, $columna) !== 1) {
                    $fail("El campo {$attribute} tiene una columna no válida.");

                    return;
                }
            }
        };
    }

    /** Texto, número o booleano (nunca listas ni objetos) de longitud limitada. */
    private function valorSimple(int $maximo): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($maximo): void {
            if (! is_scalar($value) || mb_strlen((string) $value) > $maximo) {
                $fail("El campo {$attribute} no es válido.");
            }
        };
    }
}
