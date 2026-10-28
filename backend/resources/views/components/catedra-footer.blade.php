{{-- Footer común de toda la web pública. --}}
<footer>
  <div class="wrap">
    <div class="footer-row">
      <div class="footer-brand">
        <img src="{{ asset('logo-footer.webp') }}" alt="Cátedra Ceuta-IATEB" />
        <span class="footer-brand-text">Cátedra Ceuta-IATEB<br>Universidad de Granada · Ciudad Autónoma de Ceuta</span>
      </div>
      <div class="footer-links">
        <a href="{{ route('proyectos.index') }}">Proyectos</a>
        <a href="{{ route('recursos.index') }}">Recursos</a>
        <a href="{{ route('sugerencias.create') }}">Buzón ciudadano</a>
        <a href="{{ route('privacidad') }}">Privacidad</a>
        <a href="https://catedras.ugr.es/iateb/" target="_blank" rel="noopener">catedras.ugr.es/iateb</a>
        <a href="https://www.ugr.es" target="_blank" rel="noopener">ugr.es</a>
        <a href="https://www.ceuta.es" target="_blank" rel="noopener">ceuta.es</a>
      </div>
    </div>
  </div>
</footer>
