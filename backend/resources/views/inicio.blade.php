<x-catedra>
  @push('estilos')
  <style>
    .hero-grid{display:grid; grid-template-columns:minmax(0, 1fr);}
    .hero-grid .redes{padding-bottom:56px; max-width:440px;}
    .hero-grid .hero-inner{padding-bottom:40px;}
    @media (min-width:1000px){
      .hero-grid{grid-template-columns:minmax(0, 1fr) 360px; gap:56px; align-items:center;}
      .hero-grid .hero-inner{padding-bottom:72px;}
      .hero-grid .redes{padding-block:48px;}
    }

    .pt-grid{display:grid; grid-template-columns:1fr; gap:20px;}
    @media (min-width:640px){ .pt-grid{grid-template-columns:1fr 1fr;} }
    @media (min-width:960px){ .pt-grid{grid-template-columns:1fr 1fr 1fr;} }
    .proyectos-pie{margin-top:28px;}
  </style>
  @endpush

  <section class="hero">
    <div class="wrap hero-grid">
      <div class="hero-inner">
        <span class="eyebrow">Universidad de Granada · Ciudad Autónoma de Ceuta</span>
        <h1>Inteligencia artificial al servicio del bienestar en Ceuta</h1>
        <p class="lead">La Cátedra Ceuta-IATEB investiga, forma y transfiere conocimiento en Inteligencia Artificial y Tecnologías Emergentes aplicadas a la Educación, la Salud, los Servicios Sociales y la Economía de la ciudad.</p>
        <div class="hero-actions">
          <a class="btn btn-accent" href="#proyectos">Ver nuestros proyectos →</a>
          <a class="btn btn-ghost" style="color:#fff; border-color:rgba(255,255,255,.45);" href="#quienes-somos">Conócenos</a>
        </div>
      </div>
      <x-redes-sociales />
    </div>
  </section>

  <section id="quienes-somos">
    <div class="wrap">
      <div class="split">
        <div class="section-head" style="margin-bottom:0;">
          <span class="kicker">Quiénes somos</span>
          <h2>Una cátedra entre la universidad y la ciudad</h2>
        </div>
        <div>
          <p class="lede">La Cátedra UGR - Ciudad Autónoma de Ceuta en Inteligencia Artificial y Tecnologías Emergentes para el Bienestar (Cátedra Ceuta-IATEB) es una iniciativa conjunta entre la Universidad de Granada y la Ciudad Autónoma de Ceuta.</p>
          <p class="lede">Su objetivo es convertirse en un núcleo relevante para la investigación, la formación y la transferencia de conocimiento en el ámbito de la Inteligencia Artificial y las Tecnologías Emergentes, con proyectos que respondan a necesidades reales de la ciudadanía.</p>
          <div class="partners">
            <span class="partner-tag">Universidad de Granada</span>
            <span class="partner-tag">Ciudad Autónoma de Ceuta</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="band">
    <div class="wrap">
      <div class="section-head">
        <span class="kicker">Qué hacemos</span>
        <h2>Investigación, formación y transferencia</h2>
      </div>
      <div class="card-row cols-3">
        <div class="card">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.6"/><path d="M16 16l4.5 4.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
          <h3>Investigación</h3>
          <p>Estudiamos cómo la inteligencia artificial y las tecnologías emergentes pueden mejorar los servicios públicos y la calidad de vida en Ceuta.</p>
        </div>
        <div class="card">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2 9l10-5 10 5-10 5L2 9z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M6 11v5c0 1.5 2.7 3 6 3s6-1.5 6-3v-5" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
          <h3>Formación</h3>
          <p>Acercamos la inteligencia artificial a estudiantes, profesionales y administración mediante cursos, talleres y jornadas.</p>
        </div>
        <div class="card">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 12h12M12 6l6 6-6 6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
          <h3>Transferencia</h3>
          <p>Convertimos el conocimiento en herramientas útiles para la ciudad, desarrolladas con criterios de privacidad, transparencia y supervisión humana.</p>
        </div>
      </div>
    </div>
  </section>

  <section id="lineas">
    <div class="wrap">
      <div class="section-head">
        <span class="kicker">Líneas de trabajo</span>
        <h2>Cuatro ámbitos para el bienestar</h2>
        <p class="lede">Concentramos nuestra actividad en los ámbitos donde la tecnología puede tener un impacto más directo en la vida de las personas.</p>
      </div>
      <div class="cat-grid">
        <div class="cat"><h3>Educación</h3><p>Tecnologías para el aprendizaje, la orientación y la mejora de la práctica docente.</p></div>
        <div class="cat"><h3>Salud</h3><p>Herramientas de apoyo a la atención sanitaria, la prevención y la salud pública.</p></div>
        <div class="cat"><h3>Servicios Sociales</h3><p>Apoyo a la atención social, la inclusión y la detección de necesidades.</p></div>
        <div class="cat"><h3>Economía</h3><p>Transformación digital, empleo, emprendimiento y desarrollo económico de la ciudad.</p></div>
      </div>
    </div>
  </section>

  <section id="proyectos" class="band">
    <div class="wrap">
      <div class="section-head">
        <span class="kicker">Proyectos</span>
        <h2>Lo que estamos construyendo</h2>
      </div>

      @if ($proyectos->count() === 1)
        <x-proyecto-tarjeta :proyecto="$proyectos->first()" destacado />
      @elseif ($proyectos->isNotEmpty())
        <div class="pt-grid">
          @foreach ($proyectos as $proyecto)
            <x-proyecto-tarjeta :proyecto="$proyecto" />
          @endforeach
        </div>
      @endif

      <div class="proyectos-pie">
        <a class="btn btn-ghost" href="{{ route('proyectos.index') }}">Ver todos los proyectos →</a>
      </div>
    </div>
  </section>

  <section class="cta-final">
    <div class="wrap">
      <div>
        <h2>¿Quieres saber más o colaborar?</h2>
        <p>Consulta toda la información de la cátedra en la web de la Universidad de Granada.</p>
      </div>
      <a class="btn btn-accent" href="https://catedras.ugr.es/iateb/" target="_blank" rel="noopener">Web de la cátedra →</a>
    </div>
  </section>
</x-catedra>
