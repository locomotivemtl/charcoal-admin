Charcoal Admin Module
=====================

The standard Charcoal Admin Control Panel (Backend Dashboard).

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
		-	[locomotivemtl/charcoal-property](https://github.com/locomotivemtl/charcoal-property)
			-	The building blocks of the Model's definition.
            -   [locomotivemtl/charcoal-image](https://github.com/locomotivemtl/charcoal-image)
                -   Image manipulation.
        -   [locomotivemtl/charcoal-view](https://github.com/locomotivemtl/charcoal-view)
            -   The view / templating engines. Mustache is the default engine.
-   [locomotivemtl/charcoal-object](https://github.com/locomotivemtl/charcoal-object)
    -   Object definition (Content and UserData), behaviors and tools.
-	[locomotivemtl/charcoal-user](https://github.com/locomotivemtl/charcoal-user)
	-	User defintion (as Charcoal Model), authentication and authorization (with Laminas ACL).

> 👉 Development dependencies are described in the _Development_ section of this README file.

Which, in turn, require:

-	`PHP 7.1+`
	+	`ext-fileinfo` File / MIME identification.
	+	`ext-mbstring` Multi-bytes string support.
    +	`ext-pdo` PDO Database driver.
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

The _charcoal admin control panel_ is:

- Additional `admin` metadata on charcoal objects and models, which controls automatically how they can be customized in the backend.
- A user / authentication system, which uses ACL for permissions.
- A customizable 2-level menu, which builds custom backend for every install.
- Dashboards and widgets. With some prebuilt functionalities for:
    - Listing collection of objects (`admin/object/collection`), customizable from the object's _admin metadata_.
    - Creating and editing objects (`admin/object/edit`), customizable from the objects's _admin metadata_.
- Set of _scripts_ to manage objects and the backend from the CLI.

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
	-	Split in `Templates`, `Widgets`, `PropertyDisplay`, and `PropertyInput`
	-	PHP Models in `src/Charcoal/Boilerplate/Template/`
	-	Mustache views (templates) in `templates/boilerplate/`
	-	Optionnally, templates metadata in `metdata/boilerplate/template/`
-	**Actions**
	-	Actions handle input and provide a response to a request
	-   They create the charcoal-admin REST API.
	-	The PHP classes in `src/Charcoal/Boilerplate/Action`
-	**Assets**
	-	Assets are files required to be on the webserver root
	-	Scripts, in `src/scripts/` and compiled in `www/assets/scripts/`
	-	Styles , with SASS in `src/styles/` and compiled CSS in `www/assets/styles/`
	-	Images, in `www/assets/images/`

## Objects

## Users

Authentication is done through the `Charcoal\Admin\User` class. It reuses the authentication, authorization and user model provided by [charcoal-user](https://github.com/locomotivemtl/charcoal-user.

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

Similar to other UI elements, _Inputs_ are specialized widgets that are meant to display a "form element" for a `Property`. Properties models are defined in the [charcoal-property](https://github.com/locomotivemtl/charcoal-property) package.

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
-	`Selectize`
	-	A specialized hybrid between a _Textbox_ and _Select_ jQuery based.
	- Highly customizable.
	-	Requires the `selectize` javascript library.

### Selectize inputs options

<table width="100%">
<tr>
  <th width="120px" align="left">Name</th>
  <th width="30px" align="left">Type</th>
  <th align="left">Description</th>
  <th width="60px" align="left">Default</th>
</tr>
<tr>
  <td valign="top"><strong>choice_obj_map</strong></td>
  <td valign="top"><em>array</em></td>
  <td valign="top">Custom mapping between an object properties or callable and the selectize. It is discouraged to use renderable data. choice_obj_map must be a mapping with existing object properties.
  <table class="table table-bordered table-hover table-condensed">
          </br>
          </br>
          <tbody><tr>
          <td valign="top"><strong>value</strong></td>
          <td>Object property or object callable. Defines the actual value to be registered in the database</td>
          </tr>
          <tr>
          <td valign="top"><strong>label<string></td>
          <td>Object property or object callable. Defines the visible label of the input.</td>
          </tr>
          </tbody>
      </table>
  </td>
  <td valign="top"><pre>{
  &quot;value&quot; : &quot;id&quot;,
  &quot;label&quot;: &quot;name:title:label:id&quot;
}</pre></td>
</tr>
<tr>
  <td valign="top"><strong>form_ident</strong></td>
  <td valign="top"><em>string|array</em></td>
  <td valign="top">Allow to define a specific object form ident when creating or updating an object. You can specify different form idents for create and update by using the &quot;create&quot; and &quot;update&quot; array keys</td>
  <td valign="top"><code>&quot;quick&quot;</code></td>
</tr>
<tr>
  <td valign="top"><strong>selectize_templates</strong></td>
  <td valign="top"><em>string|array</em></td>
  <td valign="top">Allow custom rendering for selectize [item] and [option]. Overrule choice_obj_map[label]. Priotize using this for rendering custom labels instead of choice_obj_map.<br><br>The value can either be a string with render tags, a path to a custom template or even an array mapping to handle "item", "option", "controller" and "data" individually.
  <table class="table table-bordered table-hover table-condensed">
          </br>
          </br>
          <tbody><tr>
          <td valign="top"><strong>item</strong><br>(Can be a renderable string or template path)</td>
          <td>Custom renderable html or mustache template for the selectize item. [Item] is the term used to refer to a selected choice.</td>
          </tr>
          <tr>
          <td valign="top"><strong>option</strong><br>(Can be a renderable string or template path)</td>
          <td>Custom renderable html or mustache template for the selectize option. [Option] is the term used to refer to an available choice.</td>
          </tr>
          <tr>
          <td valign="top"><strong>controller</strong></td>
          <td>Defines a rendering context (path to php controller). (optional) Default context is the object itself.</td>
          </tr>
          <tr>
          <td valign="top"><strong>data</strong>(array)</td>
          <td>Provides additional data to the controller</td>
          </tr>
          </tbody>
      </table>
  </td>
  <td valign="top"><code>{}</code></td>
</tr>
<tr>
  <td valign="top"><strong>allow_create</strong></td>
  <td valign="top"><em>bool</em></td>
  <td valign="top">Display a &#39;create&#39; button which triggers the selectize create functionality.</td>
  <td valign="top"><code>false</code></td>
</tr>
<tr>
  <td valign="top"><strong>allow_update</strong></td>
  <td valign="top"><em>bool</em></td>
  <td valign="top">Display an &#39;update&#39; button which triggers the selectize update functionality. Applies to currently selected element.</td>
  <td valign="top"><code>false</code></td>
</tr>
<tr>
  <td valign="top"><strong>allow_clipboard_copy</strong></td>
  <td valign="top"><em>bool</em></td>
  <td valign="top">Display a &#39;copy&#39; button which allows the user to easilly copy all selected elements at once.</td>
  <td valign="top"><code>false</code></td>
</tr>
<tr>
  <td valign="top"><strong>deferred</strong></td>
  <td valign="top"><em>bool</em></td>
  <td valign="top">Allow the select to load the dropdown &quot;options&quot; with an ajax request instead of on load. This can speed up the page load when there is a lot of &quot;options&quot;. </td>
  <td valign="top"><code>false</code></td>
</tr>
<tr>
  <td valign="top"><strong>selectize_options</strong></td>
  <td valign="top"><em>array</em></td>
  <td valign="top">Defines the selectize js options. See the <a href="https://github.com/selectize/selectize.js/blob/master/docs/usage.md">Selectize.js doc</a>. Some usefull ones are :
  <ul>
  <li>&quot;maxItems&quot;</li>
  <li>&quot;maxOptions&quot;</li>
  <li>&quot;create&quot;</li>
  <li>&quot;placeholder&quot;</li>
  <li>&quot;searchField&quot;</li>
  <li>&quot;plugins&quot;</li>
  </ul>
  Also, two home made plugins are available : &quot;btn_remove&quot; and &quot;btn_update&quot; that are custom buttons for selected items that work well with charcoal objects and doesn&#39;t break styling.</td>
  <td valign="top"><pre>{
   persist: true,
   preload: "focus",
   openOnFocus: true, 
   labelField: "label",
   searchField: [
     "value",
     "label"
   ]
}</pre>
  </td>
</tr>
</table>

Usage example : 

<pre>
"categories": {
    "type": "object",
    "input_type": "charcoal/admin/property/input/selectize",
    "multiple": true,
    "deferred": true,
    "obj_type": "cms/object/news-category",
    "pattern": "title",
    "choice_obj_map": {
        "value": "ident",
        "label": "{{customLabelFunction}} - {{someAdditionalInfo }}"
    },
    "selectize_templates": {
        "item": "project/selectize/custom-item-template",
        "option": "project/selectize/custom-option-template",
        "controller": "project/selectize/custom-template"
    },
    "selectize_options": {
        "plugins": {
            "drag_drop": {},
            "btn_remove": {},
            "btn_update": {}
        }
    },
    "form_ident": {
        "create": "quick.create",
        "update": "quick.update"
    }
}
</pre>

Selectize templates examples : 

<pre>
"selectize_templates": {
    "item": "{{customLabelFunction}} - {{someAdditionalInfo }}",
    "option": "{{customLabelFunction}} - {{someAdditionalInfo }}"
},

---

"selectize_templates": "{{customLabelFunction}} - {{someAdditionalInfo }}",

---

"selectize_templates": "project/selectize/custom-template",

---

"selectize_templates": {
   "item": "project/selectize/custom-item-template",
   "option": "project/selectize/custom-option-template",
   "controller": "project/selectize/custom-template",
   "data": {
        "category": "{{selectedCategory}}"
   }
},
</pre>

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

> Coding style validation / enforcement can be performed with `composer phpcs`. An auto-fixer is also available with `composer phpcbf`.

For Javascript, the following coding style is enforced:

-	**todo**

Every classes, methods and functions should be covered by unit tests. PHP code can be tested with _PHPUnit_ and Javascript code with _QUnit_.

# Authors

-	Mathieu Ducharme <mat@locomotive.ca>
-	Benjamin Roch <benjamin@locomotive.ca>
-	Dominic Lord <dom@locomotive.ca>
-	Chauncey McAskill <chauncey@locomotive.ca>
-	Antoine Boulanger <antoine@locomotive.ca>
-	Joel Alphonso <joel@locomotive.ca>

# License

Charcoal is licensed under the MIT license. See [LICENSE](LICENSE) for details.

