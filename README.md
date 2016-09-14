Charcoal Admin Module
=====================

The standard Charcoal Admin Panel (Backend Dashboard).

# How to install

The preferred (and only supported) way of installing charcoal-admin is with **composer**:

```shell
$ composer require locomotivemtl/charcoal-admin
```

> Note that charcoal-admin is intended to be run along a `charcoal-app` based project. To start from a boilerplate:
>
> ```shell
> $ composer create-project locomotivemtl/charcoal-project-boilerplate

## Dependencies

-	[locomotivemtl/charcoal-core](https://github.com/locomotivemtl/charcoal-core)
	-	The framework classes. (Cache, Model, Metadata, View, Property, source, etc.)
	-	It brings the following dependencies:
		-	[locomotivemtl/charcoal-config](https://github.com/locomotivemtl/charcoal-config)
			-	The configuration container for all things Charcoal.
		-	[locomotivemtl/charcoal-view](https://github.com/locomotivemtl/charcoal-view)
			-	The view / templating engines. Mustache is the default engine.
-	[locomotivemtl/charcoal-base](https://github.com/locomotivemtl/charcoal-base)
	-	Base project classes: Assets, Objects, Properties, Templates and Widgets
	-	It brings the additional charcoal dependencies:
		-	[locomotivemtl/charcoal-image](https://github.com/locomotivemtl/charcoal-image)
			-	Image manipulation.

> 👉 Development dependencies are described in the _Development_ section of this README file.

Which, in turn, require:

-	`PHP 5.5+`
	-	Older versions of PHP are deprecated, therefore not supported.
	-	`ext-fileinfo` File / MIME identification.
	-	`ext-mbstring` Multi-bytes string support.
	-	`ext-pdo` PDO Database driver.
-	MySQL
	-	Other databases (_postgresql_, _sqlite_) should work but are not supported.
-	Apache with `mod_rewrite`
	-	Other HTTP servers (_IIS_, _nginx) should work but are not supported.
-	`pimple/pimple` for dependency injection container.
-	`slim/slim` for the routing engine and HTTP handling.
-	`mustache/mustache` for the template engine.
-	`phpmailer` to send emails.
-	`league/climate` for CLI utilities.
-	`monolog/monolog` for (_PSR-3_) logging.

# Core concepts

**todo**

# What's inside this module?

Like all Charcoal projects / modules, the main components are:

-	**Autoloader**
	-	_PSR-4_, Provided by Composer.
-	**Config**
	-	As JSON or PHP files in the [config/](config/) directory.
-	**Front Controller**
	-	The admin front controller is handled in the `\Charcoal\Admin\Module` class.
-	**Objects**
	-	Typically  into `\Charcoal\Object\Content` and `\Charcoal\Object\UserData`
	-	Extends `\Charcoal\Model\AbstractModel`, which implements the following interface:
		-	`\Charcoal\Model\ModelInterface`
		-	`\Charcoal\Core\IndexableInterface`
		-	`\Charcoal\Metadata\DescribableInterface`
		-	`\Charcoal\Source\StorableInterface`
		-	`\Charcoal\Validator\ValidatableInterface`
		-	`\Charcaol\View\ViewableInterface`
	-	PHP Models in `src/Charcoal/Boilerplate/`
	-	JSON metadata in `metadata/charcoal/boilerplate/`
-	**Templates**
	-	Templates are specialized Model which acts as View / Controller
	-	Split in `Templates`, `Widgets` and `PropertyInput`
		-	All defined in the `charcoal-base` module
		-	All those classes extend `\Charcoal\Model\AbstractModel`
	-	PHP Models in `src/Charcoal/Boilerplate/Template/`
	-	Mustache views (templates) in `templates/boilerplate/`
	-	Optionnally, templates metadata in `metdata/boilerplate/template/`
-	**Actions**
	-	Actions handle input and provide a response to a request
	-	The PHP classes in `src/Charcoal/Boilerplate/Action`
-	**Assets**
	-	Assets are files required to be on the webserver root
	-	Scripts, in `src/scripts/` and compiled in `www/assets/scripts/`
	-	Styles , with SASS in `src/styles/` and compiled CSS in `www/assets/styles/`
	-	Images, in `www/assets/images/`

## Objects

## Users

Authentication is done through the `Charcoal\Admin\User` class.

# UI Elements

User-Interface Elements, in charcoal-admin (or any other Charcoal modules, in fact), are composed of:

-	A PHP Controller, in _src/Charcoal/Admin/{{type}}/{{ident}}_
-	A mustache templates, in _templates/charcoal/admin/{{type}}/{{ident}}_
-	Optional additional metadata, in _metadata/charcoal/admin/{{type}}/{{ident}}_

There are 3 main types of UI Elements: _Templates_, _Widgets_ and _Property Inputs_.

## Templates

See the [src/Charcoal/Admin/Templates](src/Charcoal/Admin/Template) directory for the list of available Templates in this module. Note that the template views themselves (the mustache templates) are located in [templates/charcoal/admin/template/](templates/charcoal/admin/template/) directory.

In addition to being standard Template Models (controllers), all _Template_ of the admin module also implements the `\Charcoal\Admin\Template` class.

This class provides additional controls to all templates:

-	`has_feedbacks` and `feedbacks`
-	`title`, `subtitle`, `show_title` and `show_subtitle`
-	`auth_required`
	-	Protected, true by default. Set to false for templates that do not require an authenticated admin user.

## Widgets

The following base widgets are available to build the various _admin_ templates:

-	`Dashboard`
-	`Feedbacks`
-	`Form`
-	`FormGroup`
-	`FormProperty`
-	`Graph/Bar`
-	`Graph/Line`
-	`Graph/Pie`
-	Layout
-	MapWidget
-	Table
-	TableProperty

## Property Inputs

Similar to other UI elements, _Inputs_ are specialized widgets that are meant to display a "form element" for a `Property`.

The following property inputs are available  to build forms in the _admin_ module:

-	`Audio`
	-	A special HTML5 widget to record an audio file from the microphone.
-	`Checkbox`
-	`DateTimePicker`
	-	A date-time picker widget.
	-	Requires the ``
-	`File`
	-	A default `<input type="file">` that can be used as a base for all _File_ properties.
-	`Image`
	-	A specialized file input meant for uploading / previewing images.
-	`MapWidget`
	-	A specialized widget to edit a point on a map.
	-	Requires google-map.
-	`Number`
-	`Radio`
-	`Readonly`
-	`Select`
-	`Switch`
	-	A specialized _Checkbox_ meant to be displayed as an on/off switch.
-	`Text`
	-	A default `<input type="text">` that can be used with most property types.
-	`Textarea`
	-	A default `<textarea>` editor that can be used with most textual property types.
-	`Tinymce`
	-	A specialized _Textarea_ wysiwyg editor.
	-	Requires the `tinymce` javascript library.

# Actions

See the [src/Charcoal/Admin/Action/](src/Charcoal/Admin/Action/) directory for the list of availables Actions in this module.

In addition to being standard Action Models (controllers), all _Action_ of the admin module also implements the `\Charcoal\Admin\Action` class.

## Post Actions

-	`admin/login`
-	`admin/object/delete`
-	`admin/object/save`
-	`admin/object/update`
-	`admin/widget/load`
-	`admin/widget/table/inline`
-	`admin/widget/table/inlinue-multi`

## Cli Actions

See the [src/Charcoal/Admin/Action/Cli/](src/Charcoal/Admin/Action/Cli/) directory for the list of all available Cli Actions in this module.

_Cli Actions_ are specialized action meant to be run, interactively, from the Command Line Interface. With the Cli Actions in this module, it becomes quick and easy to manage a Charcoal project directly from a Terminal.

> 👉 The [charcoal-cli](https://github.com/locomotivemtl/charcoal-project-boilerplate/blob/master/charcoal-cli.php) tool, available from `charcoal-project-boilerplate`, is the perfect tool to call the CLI Actions. Make sure it stays outside the document root!

-	`admin/objects`
	-	List the object of a certain `obj-type`.
-	`admin/object/create`
	-	Create a new object (and save it to storage) of a certain `obj-type` according to its metadata's properties.
-	`admin/object/table/alter`
	-	Alter the existing database table of `obj-type` according to its metadata's properties.
-	`admin/object/table/create`
	-	Create the database table for `obj-type` according to its metadata's properties.
-	`admin/user/create`


# Development

To install the development environment:

```shell
$ composer install --prefer-source
```

To run the tests:

```shell
$ composer test
```

## API documentation

-	The auto-generated `phpDocumentor` API documentation is available at [https://locomotivemtl.github.io/charcoal-admin/docs/master/](https://locomotivemtl.github.io/charcoal-admin/docs/master/)
-	The auto-generated `apigen` API documentation is available at [https://codedoc.pub/locomotivemtl/charcoal-admin/master/](https://codedoc.pub/locomotivemtl/charcoal-admin/master/index.html)


## Coding style

The Charcoal-Admin module follows the Charcoal coding-style:

-	[_PSR-1_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md), except for
	-	Method names MUST be declared in `snake_case`.
-	[_PSR-2_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md), except for the PSR-1 requirement.q
-	[_PSR-4_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md), autoloading is therefore provided by _Composer_
-	[_phpDocumentor_](http://phpdoc.org/)
	-	Add DocBlocks for all classes, methods, and functions;
	-	For type-hinting, use `boolean` (instead of `bool`), `integer` (instead of `int`), `float` (instead of `double` or `real`);
	-	Omit the `@return` tag if the method does not return anything.
-	Naming conventions
	-	Read the [phpcs.xml](phpcs.xml) file for all the details.

> Coding style validation / enforcement can be performed with `grunt phpcs`. An auto-fixer is also available with `grunt phpcbf`.

For Javascript, the following coding style is enforced:

-	**todo**

Every classes, methods and functions should be covered by unit tests. PHP code can be tested with _PHPUnit_ and Javascript code with _QUnit_.

## Authors

-	Mathieu Ducharme <mat@locomotive.ca>
-	Benjamin Roch <benjamin@locomotive.ca>
-	Dominic Lord <dom@locomotive.ca>
-	Chauncey McAskill <chauncey@locomotive.ca>
-	Antoine Boulanger <antoine@locomotive.ca>

## Changelog

### 0.1

_Unreleased_
-	Initial release

## TODOs

-	Unit test coverage

# License

**The MIT License (MIT)**

_Copyright © 2016 Locomotive inc._

> See [Authors](#authors).

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
