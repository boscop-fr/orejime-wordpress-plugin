# Orejime for WordPress

This plugin integrates [Orejime](https://orejime.boscop.fr/), a lightweight and accessible consent manager, to WordPress.

## Features

### Integrations

Besides letting you manually configure purposes, the plugin provides built-in integrations to core WordPress functionalities and major analytics plugins.

* [Embed blocks](https://wordpress.com/support/wordpress-editor/blocks/embed-block)
* [GA Google Analytics plugin](https://wordpress.org/plugins/ga-google-analytics/)
* [Google Site Kit plugin](https://wordpress.org/plugins/google-site-kit)
* [Matomo plugin](https://wordpress.org/plugins/matomo)
* [Monster Insights plugin](https://wordpress.org/plugins/google-analytics-for-wordpress)

## Development

### Initial setup

```sh
npm install # installs build & run environment
npm run up # starts docker containers
npm run composer install # installs backend dev environment
```

### Build

```sh
npm start # builds assets and watches for changes
```

### Quality

```sh
npm run lint # lints JS and PHP code
npm run format  # formats JS and PHP code
```
