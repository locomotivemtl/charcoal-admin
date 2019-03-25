Charcoal Admin Grunt Tasks
==========================

In development, assets should be copied or compiled into [`assets/dist/`](assets/dist).
In production, assets should be copied from [`assets/dist/`](assets/dist) into your project's public root (e.g., `../../../www/assets/admin`).

## Tasks

*   `grunt default`
    1.  Compile, copy, and minify Admin and third-party assets (`grunt build`)
    2.  Copy freshly-processed assets to the public directory (`grunt deploy`)
*   `grunt build`
    1.  Copy third-party assets from packages in `node_modules/` (NPM) and `vendor/` (Composer)
    2.  Compile scripts, styles, and SVG images from `assets/src/`
    3.  Minify all assets
*   `grunt sync`
    1.  Watch for changes in `assets/src/` and compile assets
    2.  Watch for changes in `assets/dist/` and copy to the public directory
*   `grunt deploy`
    1.  Copy previously-processed assets to the public directory
