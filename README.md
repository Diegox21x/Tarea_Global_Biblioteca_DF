# Biblioteca DF (Laravel + JavaScript)

Proyecto base para control de biblioteca con frontend en JS puro y backend estilo Laravel.

## Incluye
- Roles `user` y `admin`.
- CRUD para libros, usuarios, préstamos y penalizaciones con controladores resource (`index`, `create`, `store`, `show`, `edit`, `update`, `destroy`).
- Rutas organizadas en `routes/web.php` con prefijo `/api`.
- Endpoint `/api/bootstrap` para exportar datos JSON al frontend.
- Frontend JS puro que usa `localStorage` y consume JSON.

## Pantallas implementadas
- Login
- Catálogo
- Mis préstamos
- Panel de administración (ajustes + estadísticas)

## Nota
Los datos se guardan en `storage/app/library.json` y también se sincronizan en `localStorage` del navegador.
