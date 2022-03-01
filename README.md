## Craft Tablecloth

![tablecloth)](https://user-images.githubusercontent.com/1510460/156232875-0b8700f0-6299-4425-9d47-b3d82915e298.png)

Tablecloth is a powerful and flexible data tables solution built specifically for CraftCMS. It allows for building a front facing table or index page for any of Craft's native elements, as well as Products and Variants for Craft Commerce.

The plugin comes with a default table powered by Tailwind and AlpineJS. The user can override any component or the entire table, and even change the table layout to a card layout (e.g for displaying products). All table functionality can be triggered programmatically, including filtering, sorting, pagination and row selection, which allows for a complete separation of UI and behavior. The package supports creating "presets" of reusable tables that can be used across data sources. 

The basic process of building a table/index usually consists of two steps:

1. Defining the table source (e.g Entries or Products), columns and options on Craft's backend.
2. (Optional, depending on the table) Creating twig templates for the table and/or specific columns or column types, with simple and declarative AlpineJS syntax.

The table can then be rendered anywhere in the page with a simple twig function.

## Documentation

The full documentation can be found [here](https://matanya.gitbook.io/craft-tablecloth/).


## License

You can try Tablecloth in a development environment for as long as you like. Once your site goes live, you are required to
purchase a license for the plugin. License is purchasable through
the [Craft Plugin Store](https://plugins.craftcms.com/tablecloth).

For more information, see
Craft's [Commercial Plugin Licensing](https://craftcms.com/docs/3.x/plugins.html#commercial-plugin-licensing).

## Requirements

This plugin requires Craft CMS 3.7.0 or later.

## Issues and Discussions Guidelines

*Please only open a new issue for bug reports.*
For feature requests and questions open a new [Discussion](https://github.com/matfish2/craft-tablecloth/discussions) instead.
When discussing a feature request please precede [FR] to the title.
