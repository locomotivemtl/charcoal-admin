Charcoal Object
===============

Objects are specialized `Model` that also implements the `Charcoal\Core\IndexableInterface`. It's usage is therefore really similar to the Model.

The `Charcoal\Object` namespace also defines [many extra interfaces](#available-interfaces) that can be used to build complex objects, such as category, hierarchy, etc.


# What is a Charcoal Model?

The base domain model objects are defined in charcoal-core's `\Charcoal\Model` namespace. A Model implements:

- `\Charcoal\Metadata\DescribableInterface` for handling the metadata configuration (typically, a json file)
- `\Charcoal\Source\StorableInterface` for handing the source (typically, database) storage and loading
- `\Charcoal\Validator\ValidatableInterface` for handling the validation of its data
- `\Charcoal\View\ViewableInterface` to render the object, optionnaly with templates.

The `AbstractObject` class add one implementation:

- `\Charcoal\Core\IndexableInterface` to ensure the models can be loaded with an identifier.

> Refer to the `charcoal-core` documentation on `Model`s for more details.

# Type of objects

There are 2 different types of objects available in this module:

- `Content`
- `UserData`

Both types implements the `ObjectInterface` interface by extending the `AbstractObject` class.

## Content Objects

Content objects are standard content object. Example of _Content_ objects would be _sections or pages_, _news_, _blog entries_, _media gallery_, _survey_, _promobox_, _faq_, etc.

They can be hidden / disabled with an `active` switch and ordered with the `position` value.

The following methods / properties are handled automatically:

- `created` _DateTime_
- `created_by` _string_
- `last_modified` _DateTime_
- `last_modified_by`

## UserData Objects

UserData objects, on the other hand, are objects that are typically entered by the end-user of the website. Example of _UserData_ objects would be _subscriptions to newsletter_, _blog comment_, _answers to a survey_, _contact form data_, etc.

The following methods / properties are handled automatically:

- `ip` _string_
- `ts` __DateTime_
- `lang` _string_

# Available interfaces

- `CategorizableInterface`
- `CategoryInterface`
- `HierarchicalInterface`
- `RoutableInterface`

## Interface `CategorizableInterface`

For objects that can be added in a category or multiple categories.
Objects that implements this interface are therefore "category item".

- `set_categorizable_data()`
- `category()`

> 👉 This Interface should be implemented with its accompanying trait `CategorizableTrait`, which fully implements it.

## Interface `CategoryInterface`

For objects that can contain multiple items.

## Interface `HierarchycalInterface`

For objects that can be linked together in a hierarchy. This interface provides many methods, most importantly:

- `master()`
- `children()`
- `siblings()`

As well as many helper methods such as `has_master()`, `has_children()`, `has_siblings()` and `num_children()`, `recursive_num_children()`, `num_siblings()` and `is_sibling_of()`, `is_master_of()`, `is_child_of()`.

> 👉 This Interface should be implemented with its accompanying trait `CategorizableTrait`, which fully implements it but add one abstract protected method: `load_children()`.

> A proper example of a class implementing this interface is `Charcoal\Cms\Section`, found in the `charcoal-cms` module.

## Interface `RoutableInterface`

For objects that can be reached from a URL.
