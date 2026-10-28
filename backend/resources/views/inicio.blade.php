<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="description" content="Cátedra UGR - Ciudad Autónoma de Ceuta en Inteligencia Artificial y Tecnologías Emergentes para el Bienestar">
<title>Cátedra Ceuta-IATEB</title>
<link rel="icon" type="image/svg+xml" href="{{ asset('paloma2.svg') }}">
<link rel="stylesheet" href="{{ asset('styles.css') }}">
<style>
  .nav-brand img{height:52px; border-radius:6px;}
  .nav-brand span{font-size:1.2rem;}
  @media (max-width:639px){ .nav-brand img{height:44px;} .nav-brand span{display:none;} }

  .nav-links{display:flex; align-items:center; gap:22px;}
  .nav-links a:not(.btn){font-size:.9rem; font-weight:600; color:var(--ink-soft); text-decoration:none; white-space:nowrap;}
  .nav-links a:not(.btn):hover{color:var(--primary);}
  @media (max-width:899px){ .nav-links a:not(.btn){display:none;} }

  .hero-grid{display:grid; grid-template-columns:minmax(0, 1fr);}
  .hero-grid .redes{padding-bottom:56px; max-width:440px;}
  .hero-grid .hero-inner{padding-bottom:40px;}
  @media (min-width:1000px){
    .hero-grid{grid-template-columns:minmax(0, 1fr) 360px; gap:56px; align-items:center;}
    .hero-grid .hero-inner{padding-bottom:72px;}
    .hero-grid .redes{padding-block:48px;}
  }

  .project{
    display:grid;
    grid-template-columns:1fr;
    gap:24px;
    background:var(--surface);
    border:1px solid var(--line);
    border-left:4px solid var(--accent);
    border-radius:14px;
    padding:28px;
  }
  @media (min-width:760px){ .project{grid-template-columns:1.4fr 1fr; align-items:center;} }
  .project h3{font-size:1.35rem; color:var(--ink); margin-bottom:10px;}
  .project p{margin:0 0 12px; color:var(--ink-soft); line-height:1.6;}
  .project-tags{display:flex; flex-wrap:wrap; gap:8px; margin:6px 0 20px;}
  .project-tags span{font-size:.78rem; font-weight:700; letter-spacing:.04em; color:var(--primary); background:var(--surface-2); border-radius:999px; padding:5px 12px;}
  .project-side{display:flex; flex-direction:column; gap:12px;}
  .project-side .card{padding:18px 20px;}
  .projects-more{margin-top:20px; color:var(--ink-faint); font-size:.95rem;}
</style>
</head>
<body>
<header class="nav">
  <div class="wrap">
    <a class="nav-brand" href="{{ route('inicio') }}" style="text-decoration:none;">
      <img src="{{ asset('logo-nav.webp') }}" alt="Cátedra Ceuta-IATEB" />
      <span>Cátedra Ceuta-IATEB</span>
    </a>
    <nav class="nav-links" aria-label="Secciones">
      <a href="#quienes-somos">Quiénes somos</a>
      <a href="#lineas">Líneas de trabajo</a>
      <a href="#proyectos">Proyectos</a>
      <a class="btn btn-accent btn-sm" href="{{ route('sugerencias.create') }}">Buzón ciudadano</a>
    </nav>
  </div>
</header>

<main>
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

      <article class="project">
        <div>
          <h3>Ceuta Me Preocupa</h3>
          <div class="project-tags">
            <span>Buzón ciudadano</span>
            <span>Participación</span>
            <span>En marcha</span>
          </div>
          <p>Un buzón anónimo para que la ciudadanía cuente, con sus propias palabras, qué preocupaciones, incidencias no urgentes o propuestas de mejora tiene sobre la gestión pública de Ceuta.</p>
          <p>Las aportaciones se seudonimizan, un equipo humano las revisa y clasifica, y los resultados se publican de forma agregada.</p>
          <a class="btn btn-accent" style="margin-top:8px;" href="{{ route('sugerencias.create') }}">Ir al buzón →</a>
        </div>
        <div class="project-side">
          <div class="card">
            <h3>Anónimo por diseño</h3>
            <p>No se piden datos identificativos para participar.</p>
          </div>
          <div class="card">
            <h3>Resultados públicos</h3>
            <p>Recuentos y tendencias por categoría, nunca textos individuales.</p>
          </div>
        </div>
      </article>

      <p class="projects-more">Próximamente iremos incorporando aquí nuevos proyectos de la cátedra.</p>
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
</main>

<footer>
  <div class="wrap">
    <div class="footer-row">
      <div class="footer-brand">
        <img src="{{ asset('logo-footer.webp') }}" alt="Cátedra Ceuta-IATEB" />
        <span class="footer-brand-text">Cátedra Ceuta-IATEB<br>Universidad de Granada · Ciudad Autónoma de Ceuta</span>
      </div>
      <div class="footer-links">
        <a href="{{ route('sugerencias.create') }}">Buzón ciudadano</a>
        <a href="{{ route('privacidad') }}">Privacidad</a>
        <a href="https://catedras.ugr.es/iateb/" target="_blank" rel="noopener">catedras.ugr.es/iateb</a>
        <a href="https://www.ugr.es" target="_blank" rel="noopener">ugr.es</a>
        <a href="https://www.ceuta.es" target="_blank" rel="noopener">ceuta.es</a>
      </div>
    </div>
  </div>
</footer>
</body>
</html>
