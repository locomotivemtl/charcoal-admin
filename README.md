Charcoal Object
===============

Object definition (Content and UserData), behaviors and tools.


# Table of content
-   [How to install](#how-to-install)
    -   [Dependencies](#dependencies)
-   [The Charcoal Object](#the-charcoal-object)
    -   [Basic classes](#basic-classes)
        -   [Content](#content)
        -   [UserData](#userdata)
    -   [Object behaviors](#object-behaviors)
        -   [Archivable](#archivable)
        -   [Categorizable](#categorizable)
        -   [Category](#category)
        -   [Hierarchical](#hierarchical)
        -   [Publishable](#publishable)
        -   [Revisionable](#revisionable)
        -   [Routable](#routable)
    -   [Helpers](#helpers)
        -   [ObjectDraft](#objectdraft)
        -   [ObjectRevision](#objectrevision)
        -   [ObjectSchedule](#objectschedule)
-   [Development](#development)
    -   [Development dependencies](#development-dependencies)
    -   [Continuous Integration](#continuous-integration)
    -   [Coding Style](#coding-style)
    -   [Authors](#authors)
    -   [Changelog](#changelog)

# How to install

The preferred (and only supported) way of installing _charcoal-user_ is with **composer**:

```shell
★ composer require locomotivemtl/charcoal-user
```

## Dependencies

- PHP 5.6+
    -   This is the last supported version of PHP.
    -   `PHP 7` is also supported (meaning _green on travis_…).

# The Charcoal Object

The `\Charcoal\Object` namespace provides a bunch of basic classes, helpers as well as object behaviors (interfaces + traits).

## Basic classes

All charcoal project object classes should extend one of the 2 base classes, [`\Charcoal\Object\Content`](#content), for data created and managed by administrators or [`\Charcoal\Object\UserData`](#userdata), for data created from clients / users.

### Content

The **Content** base class should be used for all objects which can be "managed". Typically by an administrator, via the `charcoal-admin` module. It adds the "active" flag to objects as well as creation and modification informations.

**API**

-   ` setActive($active)`
-   `active()`
-   `setPosition($position)`
-   `position()`
-   `setCreated($created)`
-   `created()`
-   `setCreatedBy($createdBy)`
-   `createdBy()`
-   `setLastModified($lastModified)`
-   `lastModified()`
-   `setLastModifiedBy($lastModifiedBy)`
-   `lastModifiedBy()`

> The `Content` class extends `\Charcoal\Model\AbstractModel` from the `charcoal-core` module, which means that it also inherits its API as well as the `DescribableInterface` (`metadata()`, `setMetadata()` and `loadMetadata()`, amongst others) and the `StorableInterface` (`id()`, `key()`, `save()`, `update()`,  `delete()`, `load()`, `loadFrom()`, `loadFromQuery()`, `source()` and `setSource()`, amongst others).
>
> The `AbstractModel` class extends `\Charcoal\Config\AbstractEntity` which also defines basic data-access methods (`setData()`, `data()`, `keys()`, `has()`, `get()`, `set()`, plus the `ArrayAccess`, `JsonSerializable` and `Serializable` interfaces).

**Properties (metadata)**

| Property               | Type        | Default     | Description |
| ---------------------- | ----------- | ----------- | ----------- |
| **active**             | `boolean`   | `true`      | …           |
| **position**           | `number`    | `null`      | …           |
| **created**            | `date-time` | `null` [1]  | …           |
| **created_by**         | `string`    | `''` [1]    | …           |
| **last_modified**      | `date-time` | `null` [2]  | …           |
| **last\_modified\_by** | `string`    | `''` [2]    | …           |

<small>[1] Auto-generated upon `save()`</small><br>
<small>[2] Auto-generated upon `update()`</small><br>

> Default metadata is defined in `metadata/charcoal/object/content.json`

### UserData

The **UserData** class should be used for all objects that are expected to be entered from the project's "client" or "end user".

**API**

-   `setIp($ip)`
-   `ip()`
-   `setTs($ts)`
-   `ts()`
-   `setLang($lang)`
-   `lang()`

> The `Content` class extends `\Charcoal\Model\AbstractModel` from the `charcoal-core` module, which means that it also inherits its API as well as the `DescribableInterface` (`metadata()`, `setMetadata()` and `loadMetadata()`, amongst others) and the `StorableInterface` (`id()`, `key()`, `save()`, `update()`,  `delete()`, `load()`, `loadFrom()`, `loadFromQuery()`, `source()` and `setSource()`, amongst others).
>
> The `AbstractModel` class extends `\Charcoal\Config\AbstractEntity` which also defines basic data-access methods (`setData()`, `data()`, `keys()`, `has()`, `get()`, `set()`, plus the `ArrayAccess`, `JsonSerializable` and `Serializable` interfaces).

**Properties (metadata)**

| Property  | Type        | Default     | Description |
| --------- | ----------- | ----------- | ----------- |
| **ip**    | `ip`        | `null` [1]  | …           |
| **ts**    | `date-time` | `null` [1]  | …           |
| **lang**  | `lang`      | `null` [1]  | …           |

<small>[1] Auto-generated upon `save()` and `update()`</small><br>

> Default metadata is defined in `metadata/charcoal/object/user-data.json`

## Object behaviors

-   [Archivable](#archivable)
-   [Categorizable](#categorizable)
-   [Category](#category)
-   [Hierarchical](#hierarchical)
-   [Publishable](#publishable)
-   [Revisionable](#revisionable)
-   [Routable](#routable)

### Archivable

_The archivable behavior is not yet documented. It is still under heavy development._

### Categorizable

**API**

-   `setCategory($category)`
-   `category()`
-   `setCategoryType($type)`
-   `categoryType()`

**Properties (metadata)**

| Property        | Type       | Default     | Description |
| --------------- | ---------- | ----------- | ----------- |
| **category**    | `object`   | `null`      | The object's category.[1] |

<small>[1] The category `obj_type` must be explicitely set in implementation's metadata.</small>

> Default metadata is defined in `metadata/charcoal/object/catgorizable-interface.json`

### Category

**API**

-   `setCategoryItemType($type)`
-   `categoryItemType()`
-   `numCategoryItems()`
-   `hasCategoryItems()`
-   `categoryItems()`

**Properties (metadata)**

| Property          | Type       | Default     | Description |
| ----------------- | ---------- | ----------- | ----------- |
| **category_item** | `string`   | `null`      | …           |

> Default metadata is defined in `metadata/charcoal/object/catgory-interface.json`

### Hierarchical

**API**

-   `hasMaster()`
-   `isTopLevel()`
-   `isLastLevel()`
-   `hierarchyLevel()`
-   `master()`
-   `toplevelMaster()`
-   `hierarchy()`
-   `invertedHierarchy()`
-   `isMasterOf($child)`
-   `recursiveIsMasterOf($child)`
-   `hasChildren()`
-   `numChildren()`
-   `recursiveNumChildren()`
-   `children()`
-   `isChildOf($master)`
-   `recursiveIsChildOf($master)`
-   `hasSiblings()`
-   `numSiblings()`
-   `siblings()`
-   `isSiblingOf($sibling)`

**Properties (metadata)**

| Property      | Type       | Default     | Description |
| ------------- | ---------- | ----------- | ----------- |
| **master**    | `object`   | `null`      | The master object (parent in hierarchy). |

> Default metadata is defined in `metadata/charcoal/object/hierarchical-interface.json`.

### Publishable

-   `setPublishDate($publishDate)`
-   `publishDate()`
-   `setExpiryDate($expiryDate)`
-   `expiryDate()`
-   `setPublishStatus($status)`
-   `publishStatus()`
-   `isPublished()`

**Properties (metadata)**

| Property           | Type         | Default    | Description |
| ------------------ | ------------ | ---------- | ----------- |
| **publish_date**   | `date-time`  | `null`     | …           |
| **expiry_date**    | `date-time`  | `null`     | …           |
| **publish_status** | `string` [1] | `'draft'`  | …           |

> Default metadata is defined in `metadata/charcoal/object/publishable-interface.json`.

### Revisionable

Revisionable objects implement `\Charcoal\Object\Revision\RevisionableInterface`, which can be easily implemented by using `\Charcoal\Object\Revision\RevisionableTrait`.

Revisionable objects create _revisions_ which logs the changes between an object's versions, as _diffs_.

**API**

-   `setRevisionEnabled(bool$enabled)`
-   `revisionEnabled()`
-   `revisionObject()`
-   `generateRevision()`
-   `latestRevision()`
-   `revisionNum(integer $revNum)`
-   `allRevisions(callable $callback = null)`
-   `revertToRevision(integer $revNum)`

**Properties (metadata)**

_The revisionable behavior does not implement any properties as all logic & data is self-contained in the revisions._

### Routable

_The routable behavior is not yet documented. It is still under heavy development._

## Helpers

### ObjectDraft

…

### ObjectRevision

Upon every `update` in _storage_, a revisionable object creates a new *revision* (a `\Charcoal\Object\ObjectRevision` instance) which holds logs the changes (_diff_) between versions of an object:

**Revision properties**

| Property           | Type         | Default    | Description |
| ------------------ | ------------ | ---------- | ----------- |
| **target_type**    | `string`     | `null`     | The object type of the target object.
| **target_id**      | `string`     | `null`     | The object idenfiier of the target object.
| **rev_num**        | `integer`    | `null`     | Revision number, (auto-generated).
| **ref_ts**         | `date-time`  |            |
| **rev_user**       | `string`     | `null`     |
| **data_prev**      | `structure`  |            |
| **data_obj**       | `structure`  |            |
| **data_diff**      | `structure`  |            |

**Revision methods**

-   `createFromObject(RevisionableInterface $obj)`
-   `createDiff(array $dataPrev, array $dataObj)`
-   `lastObjectRevision(RevisionableInterface $obj)`
-   `objectRevisionNum(RevisionableInterface $obj, integer $revNum)`

### ObjetSchedule

It is possible, (typically from the charcoal admin backend), to create *schedule* (a `\Charcaol\Object\ObjectSchedule` instance) which associate a set of changes to be applied automatically to an object:

**Schedule properties**

| Property           | Type         | Default    | Description |
| ------------------ | ------------ | ---------- | ----------- |
| **target_type**    | `string`     | `null`     | The object type of the target object.
| **target_id**      | `string`     | `null`     | The object idenfiier of the target object.
| **scheduled_date** | `date-time`  | `null`     |
| **data_diff**      | `structure`  | `[]`       |
| **processed**      | `boolean`    | `false`    |
| **processed_date** |

**Schedule methods (API)**

-   `process([callable $callback, callable $successCallback,callable $failureCallback])`

> Scheduled actions should be run with a timely cron job. The [charcoal-admin](https://github.com/locomotivemtl/charcoal-admin) module contains a script to run schedules automatically:
>
> ```shell
> ★ ./vendor/bin/charcoal admin/object/process-schedules`
> ```


# Development

To install the development environment:

```shell
★ composer install --prefer-source
```

To run the scripts (phplint, phpcs and phpunit):

```shell
★ composer test
```

## API documentation

-   The auto-generated `phpDocumentor` API documentation is available at [https://locomotivemtl.github.io/charcoal-object/docs/master/](https://locomotivemtl.github.io/charcoal-object/docs/master/)
-   The auto-generated `apigen` API documentation is available at [https://codedoc.pub/locomotivemtl/charcoal-object/master/](https://codedoc.pub/locomotivemtl/charcoal-object/master/index.html)

## Development dependencies

-   `phpunit/phpunit`
-   `squizlabs/php_codesniffer`
-   `satooshi/php-coveralls`

## Continuous Integration

| Service | Badge | Description |
| ------- | ----- | ----------- |
| [Travis](https://travis-ci.org/locomotivemtl/charcoal-object) | [![Build Status](https://travis-ci.org/locomotivemtl/charcoal-object.svg?branch=master)](https://travis-ci.org/locomotivemtl/charcoal-object) | Runs code sniff check and unit tests. Auto-generates API documentation. |
| [Scrutinizer](https://scrutinizer-ci.com/g/locomotivemtl/charcoal-object/) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/locomotivemtl/charcoal-object/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/locomotivemtl/charcoal-object/?branch=master) | Code quality checker. Also validates API documentation quality. |
| [Coveralls](https://coveralls.io/github/locomotivemtl/charcoal-object) | [![Coverage Status](https://coveralls.io/repos/github/locomotivemtl/charcoal-object/badge.svg?branch=master)](https://coveralls.io/github/locomotivemtl/charcoal-object?branch=master) | Unit Tests code coverage. |
| [Sensiolabs](https://insight.sensiolabs.com/projects/533b5796-7e69-42a7-a046-71342146308a) | [![SensioLabsInsight](https://insight.sensiolabs.com/projects/533b5796-7e69-42a7-a046-71342146308a/mini.png)](https://insight.sensiolabs.com/projects/533b5796-7e69-42a7-a046-71342146308a) | Another code quality checker, focused on PHP. |

## Coding Style

The charcoal-object module follows the Charcoal coding-style:

-   [_PSR-1_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)
-   [_PSR-2_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
-   [_PSR-4_](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md), autoloading is therefore provided by _Composer_.
-   [_phpDocumentor_](http://phpdoc.org/) comments.
-   Read the [phpcs.xml](phpcs.xml) file for all the details on code style.

> Coding style validation / enforcement can be performed with `composer phpcs`. An auto-fixer is also available with `composer phpcbf`.

# Authors

-   Mathieu Ducharme, mat@locomotive.ca
-   Chauncey McAskill
-   Locomotive Inc.

# Changelog

_Unreleased_

# License

**The MIT License (MIT)**

_Copyright © 2016 Locomotive inc._
> See [Authors](#authors).

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
