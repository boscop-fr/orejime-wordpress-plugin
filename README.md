# Orejime for WordPress

This plugin integrates [Orejime](https://orejime.boscop.fr/), a lightweight and accessible consent manager, to WordPress.

## Features

### Integrations

Besides letting you manually configure purposes, the plugin provides built-in integrations to core WordPress functionalities and major analytics plugins.

* [Embed blocks](https://wordpress.com/support/wordpress-editor/blocks/embed-block)
* [GA Google Analytics plugin](https://wordpress.org/plugins/ga-google-analytics/)
* [Google Site Kit plugin](https://wordpress.org/plugins/google-site-kit)
* [Jetpack plugin](https://wordpress.org/plugins/jetpack)
* [Matomo plugin](https://wordpress.org/plugins/matomo)
* [Monster Insights plugin](https://wordpress.org/plugins/google-analytics-for-wordpress)

### Contextual consent

A custom editor block allows one to block any content until the user gives their explicit consent.
This would display a placeholder allowing the user to do so on the spot.
When they does, the placeholder is replaced by the intended content.

This block can be used around any other block or list of blocks, as the core `group` block.

The contextual consent placeholder can be added automatically to any `embed` blocks.

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

## Architecture

The plugin revolves around the concept of "integrations".
An integration is a bridge between Orejime and a WordPress plugin or native feature.

Every integration derives from the base [`Integration`](./includes/class-integration.php) class.
They must each have unique identifiers and names, and should provide a way to tell if their target integration is currently active.
They hook into their target to alter their output, typically as to modify scripts so they can be handled by Orejime.

For every active integration, Orejime would register an associated purpose, which allows for customizing the info that is shown to the end user.

## Legal disclaimer

Orejime is developed and updated by the Boscop teams in accordance with regulatory developments and European and French recommendations (particularly those of the EDPB and the CNIL).

However, the compliance of a cookie manager largely depends on the solution's configuration (from the handling of each cookie, to color choices, and the information provided to users).

It is your responsibility to verify your site's compliance, and Boscop cannot be held liable for any compliance issues arising from users of the Orejime plugin.
