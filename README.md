Charcoal Admin Module
=====================

The standard Charcoal Admin Panel (Backend Dashboard).

# How to install

Except for development purpose, this module should never be run by itself or as standalone. Therefore, the preferred way to install this module is to require it as a `composer` dependency in a project.

`composer require locomotivemtl/charcoal-admin`

## Charcoal dependencies

- [locomotivemtl/charcoal-core](https://github.com/locomotivemtl/charcoal-core)
  - The framework classes. (Cache, Model, Metadata, View, Property, source, etc.)
- [locomotivemtl/charcoal-base](https://github.com/locomotivemtl/charcoal-base)
  - Base project classes: Assets, Objects, Properties, Templates and Widgets

> 👉 As of now, those packages are not distributed by Packagist but easily installable by Composer by specifiying the repository. Read the [composer.json](composer.json) file for details.

Which, in turn, require:
- PHP 5.5+
- MySQL (with PDO drivers for PHP)
  - Other databases are currently not supported
- Apache with mod_rewrite
- `slim` for the routing engine and HTTP handling
- `mustache` for the template engine
- `phpmailer` to send emails
- `climate` for CLI utilities
- `monolog` for (_PSR-3_) logging

## Build system(s)

`composer` is the preferred way of installing Charcoal modules and projects.

`grunt` is used to build the assets from source and also run the various scripts (linters, unit tests) automatically. The CSS is generated with `sass`. Running `grunt watch` while developping ensures that all assets are generated properly.

The external javascript dependencies are managed with `bower`.

# Core concepts
**todo**

# What's inside this module?

Like all Charcoal projects / modules, the main components are:
- **Autoloader**
  - _PSR-4_, Provided by Composer.
- **Config**
  - As JSON or PHP files in the [config/](config/) directory.
- **Front Controller**
  - The admin front controller is handled in the `\Charcoal\Admin\Module` class.
- **Objects**
  - Typically  into `\Charcoal\Object\Content` and `\Charcoal\Object\UserData`
  - Extends `\Charcoal\Model\AbstractModel`, which implements the following interface:
      - `\Charcoal\Model\ModelInterface`
      - `\Charcoal\Core\IndexableInterface`
      - `\Charcoal\Metadata\DescribableInterface`
      - `\Charcoal\Source\StorableInterface`
      - `\Charcoal\Validator\ValidatableInterface`
      - `\Charcaol\View\ViewableInterface`
  - PHP Models in `src/Charcoal/Boilerplate/`
  - JSON metadata in `metadata/charcoal/boilerplate/`
- **Templates**
  - Templates are specialized Model which acts as View / Controller
  - Split in `Templates`, `Widgets` and `PropertyInput`
    - All defined in the `charcoal-base` module
    - All those classes extend `\Charcoal\Model\AbstractModel`
  - PHP Models in `src/Charcoal/Boilerplate/Template/`
  - Mustache views (templates) in `templates/boilerplate/`
  - Optionnally, templates metadata in `metdata/boilerplate/template/`
- **Actions**
  - Actions handle input and provide a response to a request
  - The PHP classes in `src/Charcoal/Boilerplate/Action`
- **Assets**
  - Assets are files required to be on the webserver root
  - Scripts, in `src/scripts/` and compiled in `www/assets/scripts/`
  - Styles , with SASS in `src/styles/` and compiled CSS in `www/assets/styles/`
  - Images, in `www/assets/images/`

## Objects

## Users
Authentication is done through the `Charcoal\Admin\User` class.

# UI Elements
User-Interface Elements, in charcoal-admin (or any other Charcoal modules, in fact), are composed of:
- A PHP Controller, in _src/Charcoal/Admin/{{type}}/{{ident}}_
- A mustache templates, in _templates/charcoal/admin/{{type}}/{{ident}}_
- Optional additional metadata, in _metadata/charcoal/admin/{{type}}/{{ident}}_

There are 3 main types of UI Elements: _Templates_, _Widgets_ and _Property Inputs_.

## Templates
See the [src/Charcoal/Admin/Templates](src/Charcoal/Admin/Template) directory for the list of available Templates in this module. Note that the template views themselves (the mustache templates) are located in [templates/charcoal/admin/template/](templates/charcoal/admin/template/) directory.

In addition to being standard Template Models (controllers), all _Template_ of the admin module also implements the `\Charcoal\Admin\Template` class.

This class provides additional controls to all templates:
- `has_feedbacks` and `feedbacks`
- `title`, `subtitle`, `show_title` and `show_subtitle`
- `auth_required`
  - Protected, true by default. Set to false for templates that do not require an authenticated admin user.

## Widgets
The following base widgets are available to build the various _admin_ templates:
- Dashboard
- Feedbacks
- Form
- FormGroup
- FormProperty
- Layout
- Table
- TableProperty

## Property Inputs
Similar to other UI elements, _Inputs_ are specialized widgets that are meant to display a "form element" for a `Property`.

The following property inputs are available  to build forms in the _admin_ module:
- `Audio`
  - A special HTML5 widget to record an audio file from the microphone.
- `Checkbox`
- `File`
  - A default `<input type="file">` that can be used as a base for all _File_ properties.
- `Number`
- `Radio`
- `Readonly`
- `Switch`
  - A specialized _Checkbox_ meant to be displayed as an on/off switch.
- `Text`
  - A default `<input type="text">` that can be used with most property types.
- `Textarea`
- `Tinymce`
  - A specialized _Textarea_ augmented with the _tinymce_ library.

# Actions
See the [src/Charcoal/Admin/Action/](src/Charcoal/Admin/Action/) directory for the list of availables Actions in this module.

In addition to being standard Action Models (controllers), all _Action_ of the admin module also implements the `\Charcoal\Admin\Action` class.

## Post Actions

## Cli Actions
See the [src/Charcoal/Admin/Action/Cli/](src/Charcoal/Admin/Action/Cli/) directory for the list of all available Cli Actions in this module.

_Cli Actions_ are specialized action meant to be run, interactively, from the Command Line Interface. With the Cli Actions in this module, it becomes quick and easy to manage a Charcoal project directly from a Terminal.

> 👉 The [charcoal-cli](https://github.com/locomotivemtl/charcoal-project-boilerplate/blob/master/charcoal-cli.php) tool, available from `charcoal-project-boilerplate`, is the perfect tool to call the CLI Actions. Make sure it stays outside the document root!

- `admin/objects`
  - List the object of a certain `obj-type`.
- `admin/object/create`
  - Create a new object (and save it to storage) of a certain `obj-type` according to its metadata's properties.
- `admin/object/metadata/edit`
  - Edit an existing object (and update storage) of a certain `obj-type` according to its metadata's properties.
- `admin/object/metadata/admin/dashboards`
  - List the available object's dashboards and set the default ones.
- `admin/object/metadata/admin/forms`
  - List the available object's forms and set the default ones.
- `admin/object/metadata/admin/dashboard/create`
  - Create a new admin dashboard.
- `admin/object/metadata/admin/dashboard/edit`
  - Edit an existing admin dashboard.
- `admin/object/metadata/admin/form/create`
  - Create a new admin form.
- `admin/object/metadata/admin/form/edit`
  - Edit an existing admin form.
- `admin/object/table/alter`
  - Alter the existing database table of `obj-type` according to its metadata's properties.
- `admin/object/table/create`
  - Create the database table for `obj-type` according to its metadata's properties.


# Development

## Coding style

Like `charcoal-core` and other Charcoal modules, the admin module use the following coding style for PHP:
- _PSR-1_, except for the _CamelCaps_ method name requirement
- _PSR-2_
- array should be written in short notation (`[]` instead of `array()`)
- Docblocks for _phpdocumentor_

Coding styles are  enforced with `grunt phpcs` (_PHP Code Sniffer_). The actual ruleset can be found in [phpcs.xml][phpcs.xml].

> 👉 To fix minor coding style problems, run `grunt phpcbf` (_PHP Code Beautifier and Fixer_). This tool use the same ruleset to try and fix what can be don automatically.

For Javascript, the following coding style is enforced:
- **todo**

## Git Hooks

## Continuous Integration

## Unit tests

Every classes, methods and functions should be covered by unit tests. PHP code can be tested with _PHPUnit_ and Javascript code with _QUnit_.
