# selectize-item-color

This is an plugin for [selectize.js] (http://brianreavis.github.io/selectize.js/) which allows you to specify each selected items color (useful for multiselects) by adding an color property
to the available options which contains the color code. The plugin will add this color as CSS ``background-color`` property.

## Usage

- enable the plugin by adding ``item_color`` to the plugin-configuration as described [here](https://github.com/brianreavis/selectize.js/blob/master/docs/plugins.md#plugin-usage)
- add an color-property to your option objects (default name is ``color``, you can change this by setting the plugins ``colorField`` option)

### Example

```js
$('#input-tags').selectize({
    plugins: ['item_color']
});
```
with custom color property:
```js
$('#input-tags').selectize({
    plugins: {'item_color': {colorField: 'myColorField'}}
});
```