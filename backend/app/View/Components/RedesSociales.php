<?php

namespace App\View\Components;

use App\Support\InstagramUltimoPost;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Tarjetas de redes de la cátedra para el hero de la landing.
 *
 * - Instagram: último post leído de la API y pintado por nosotros (sin embed de Meta).
 * - Facebook y LinkedIn: sus embeds solo se cargan cuando la persona pulsa el botón,
 *   así que no hay cookies de terceros al entrar en la página.
 */
class RedesSociales extends Component
{
    /** @var array{id: string, texto: string, enlace: string, fecha: string, tipo: string}|null */
    public ?array $instagramPost;

    public bool $instagramImagen;

    /** @var array{instagram: array<string, ?string>, facebook: array<string, ?string>, linkedin: array<string, ?string>} */
    public array $redes;

    public function __construct(InstagramUltimoPost $instagram)
    {
        $this->redes = config('services.redes');
        $this->instagramPost = $this->redes['instagram']['url'] ? $instagram->obtener() : null;
        $this->instagramImagen = $this->instagramPost !== null && $instagram->tieneImagen();
    }

    public function shouldRender(): bool
    {
        return collect($this->redes)->contains(fn (array $red) => filled($red['url']));
    }

    public function render(): View
    {
        return view('components.redes-sociales');
    }
}
