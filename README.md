# poc-plugin

A wordpress plugin to interact with the [UA Libraries Theme](https://github.com/ualibweb/roots-ualib)

## Features

- Counter
    - Can increment or decrement from the showcase page
    - Is stored in a basic SQL table
    - Displayed through a shortcode
    - Registered REST API routes to increment or decrement via SQL query and AJAX scripts
- Admin Menu
    - Here you can update count of the count manully
- Book Post Type
    - A custom post type that is registered with the theme
    - Has a custom shortcode to display the books


## Images

### Showcase Page

![Admin Menu](https://github.com/danetsao/poc-plugin/blob/main/images/poc-plugin-showcase-page.jpg)

![Admin Menu](https://github.com/danetsao/poc-plugin/blob/main/images/poc-plugin-showcase-page2.jpg)

### Admin Menu

![Admin Menu](https://github.com/danetsao/poc-plugin/blob/main/images/poc-plugin-admin-page.jpg)

### Custom Post Type

![Admin Menu](https://github.com/danetsao/poc-plugin/blob/main/images/poc-plugin-custom-post-type.jpg)


## Installation

Install this plugin.

This assumes you have wordpress running locally. If not see [here](https://www.hostinger.com/tutorials/install-wordpress-locally).

Go into you local wordpress folder and go into the plugin folder.

```bash
    git clone https://github.com/danetsao/poc-plugin.git
```
Then start up wordpress with your server of choice and you can activate it via plugins.