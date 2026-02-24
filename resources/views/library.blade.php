<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca JS</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f5f7fb; }
        header { background: #1f2937; color: #fff; padding: 1rem; }
        main { max-width: 1100px; margin: 1rem auto; padding: 1rem; }
        .card { background: #fff; border-radius: 8px; padding: 1rem; margin-bottom: 1rem; box-shadow: 0 2px 6px rgba(0,0,0,.1); }
        .hidden { display: none; }
        button { border: 0; background: #2563eb; color: #fff; padding: .5rem .8rem; border-radius: 6px; cursor: pointer; }
        input, select { padding: .45rem; margin: .2rem 0; }
        .row { display: flex; gap: .5rem; flex-wrap: wrap; }
        .book { border: 1px solid #ddd; padding: .6rem; border-radius: 6px; }
    </style>
</head>
<body>
<header>
    <h1>Control Biblioteca (Laravel + JS)</h1>
</header>
<main>
    <section id="screen-login" class="card">
        <h2>Login</h2>
        <input id="login-email" type="email" placeholder="email" value="ana@biblioteca.local">
        <button id="btn-login">Entrar</button>
    </section>

    <section id="screen-catalog" class="card hidden">
        <h2>Catálogo</h2>
        <div id="catalog"></div>
    </section>

    <section id="screen-loans" class="card hidden">
        <h2>Mis préstamos</h2>
        <div id="my-loans"></div>
    </section>

    <section id="screen-admin" class="card hidden">
        <h2>Panel de administración</h2>
        <div class="row">
            <input id="max-books" type="number" min="1" placeholder="Máx libros">
            <input id="loan-days" type="number" min="1" placeholder="Días préstamo">
            <button id="save-settings">Guardar ajustes</button>
        </div>
        <h3>Estadísticas</h3>
        <pre id="stats"></pre>
    </section>
</main>
<script src="/js/library-app.js"></script>
</body>
</html>
