/* eslint-disable consistent-this */
/**
 * TinyMCE implementation for WYSIWYG inputs
 * charcoal/admin/property/input/tinymce
 *
 * Require:
 * - jQuery
 * - tinyMCE
 *
 * @param  {Object}  opts Options for input property
 */

// This prevents bootstrap dialog from blocking focusin with tinymce's dialogs
// Such as link and image edition dialogs
// Stolen here: https://github.com/tinymce/tinymce/issues/5169
$(document).on('focusin', function (e) {
    if ($(e.target).closest('.tox-tinymce-aux, .moxman-window, .tam-assetmanager-root').length) {
        e.stopImmediatePropagation();
    }
});

Charcoal.Admin.Property_Input_Tinymce = function (opts) {
    Charcoal.Admin.Property.call(this, opts);
    this.input_type = 'charcoal/admin/property/input/tinymce';

    // Property_Input_Tinymce properties
    this.input_id = null;
    this.data = opts.data;

    this.editor_options = null;
    this._editor = null;

    if (!window.elFinderCallback) {
        window.elFinderCallback = {};
    }

    this.set_properties(opts);
    this.init();
};
Charcoal.Admin.Property_Input_Tinymce.prototype = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_Tinymce.prototype.constructor = Charcoal.Admin.Property_Input_Tinymce;
Charcoal.Admin.Property_Input_Tinymce.prototype.parent = Charcoal.Admin.Property.prototype;

/**
 * Init plugin
 * @return {thisArg} Chainable.
 */
Charcoal.Admin.Property_Input_Tinymce.prototype.init = function () {
    this.create_tinymce();
};

/**
 * Init plugin
 * @return {thisArg} Chainable.
 */
Charcoal.Admin.Property_Input_Tinymce.prototype.base_url = function () {
    return Charcoal.Admin.base_url() + 'assets/admin/tinymce';
};

Charcoal.Admin.Property_Input_Tinymce.prototype.set_properties = function (opts) {
    this.input_id = opts.input_id || this.input_id;
    this.editor_options = opts.editor_options || opts.data.editor_options || this.editor_options;

    window.elFinderCallback[this.input_id] = this.elfinder_callback.bind(this);

    var locale = Charcoal.Admin.locale().match(/([a-zA-Z]{2})(_|-)([a-zA-Z]{2})/)[0] || 'en';
    locale = locale.replace('-', '_');

    if (locale.match(/en_/)) {
        locale = 'en';
    }

    var default_opts = {
        language: locale,

        /**
         * Plugins
         *
         * By default, TinyMCE does not load any plugins.
         *
         * @link https://www.tiny.cloud/docs/configure/integration-and-setup/#plugins
         *
         * Custom Charcoal plugins:
         *
         * - charcoal
         * - placeholder
         */
        plugins: [
            'advlist',
            // 'anchor',
            'autolink',
            'autoresize',
            // 'autosave',
            // 'bbcode',
            'charcoal',
            'charmap',
            'code',
            // 'codesample',
            // 'directionality',
            // 'emoticons',
            // 'fullpage',
            'fullscreen',
            // 'help',
            'hr',
            'image',
            // 'imagetools',
            // 'importcss',
            // 'insertdatetime',
            // 'legacyoutput',
            'link',
            'lists',
            'media',
            'nonbreaking',
            'noneditable',
            // 'pagebreak',
            'paste',
            'placeholder',
            // 'preview',
            // 'print',
            'quickbars',
            // 'save',
            'searchreplace',
            'tabfocus',
            'table',
            // 'template',
            // 'textpattern',
            // 'toc',
            'visualblocks',
            'visualchars',
            'wordcount'
        ],
        /**
         * Toolbars
         *
         * @link https://www.tiny.cloud/docs/configure/editor-appearance/#toolbar
         * @link https://github.com/tinymce/tinymce/blob/5.8.2/modules/tinymce/src/themes/silver/main/ts/ui/toolbar/Integration.ts#L38-L60
         *
         * Default TinyMCE toolbar:
         *
         * - `undo redo`
         * - `styleselect`
         * - `bold italic`
         * - `alignleft aligncenter alignright alignjustify`
         * - `outdent indent`
         * - `permanentpen`
         * - `addcomment`
         *
         * Default Charcoal toolbar:
         *
         * - `undo redo`
         * - `formatselect`
         * - `bold italic`
         * - `alignleft aligncenter alignright alignjustify`
         * - `bullist numlist`
         * - `outdent indent`
         * - `link image`
         */
        toolbar: [
            'undo redo',
            'styleselect',
            'bold italic',
            'alignleft aligncenter alignright alignjustify',
            'bullist numlist',
            'outdent indent',
            'link image'
        ].join(' | '),
        /**
         * Context Menu
         *
         * @link https://www.tiny.cloud/docs/configure/editor-appearance/#contextmenu
         * @link https://github.com/tinymce/tinymce/blob/5.8.2/modules/tinymce/src/themes/silver/main/ts/ui/menus/contextmenu/Settings.ts#L31
         *
         * Default TinyMCE contextmenu
         *
         * - `link linkchecker image imagetools table spellchecker configurepermanentpen`
         *
         * Default Charcoal toolbar:
         *
         * - `link linkchecker image imagetools table`
         */
        contextmenu: [
            'link linkchecker image imagetools table'
        ].join(' | '),

        // General
        browser_spellcheck: true,
        end_container_on_empty_block: true,
        entity_encoding: 'raw',

        // Cleanup / Output
        allow_conditional_comments: true,
        forced_root_block: 'p',
        //forced_root_block_attrs: {},
        // remove_trailing_brs: true

        // Content style
        //body_id: "",
        //body_class: "",
        //content_css:"",
        //content_style:"",

        // URL
        allow_script_urls: false,
        document_base_url: Charcoal.Admin.base_url(),
        relative_urls: true,
        remove_script_host: false,

        // Plugins options
        min_height: '150px',
        max_height: '400px',

        file_picker_callback: $.proxy(this.elfinder_browser, null, this),
        //image_list: [],
        image_advtab: true,
        //image_class_list: [],
        //imagetools_toolbar: 'rotateleft rotateright | flipv fliph | editimage imageoptions',
        //link_list: [],
        //target_list: [],
        //rel_list: [],
        //link_class_list: [],
        importcss_append: true,
        //importcss_file_filter: "",
        //importcss_selector_filter: ".my-prefix-",
        //importcss_groups: [],
        // importcss_merge_classes: false,
        media_alt_source: false,
        //media_filter_html: false,
        nonbreaking_force_tab: false,
        //pagebreak_separator: ""
        paste_data_images: true,
        paste_as_text: true,
        //paste_preprocess: function(plugin, args) { },
        //paste_postprocess: function(plugin, args) { },
        //paste_word_valid_elements: "",
        //paste_webkit_styles: "",
        //paste_retain_style_properties: "",
        paste_merge_formats: true,
        //save_enablewhendirty: true,
        //save_onsavecallback: function() { },
        //save_oncancelcallback: function() { },
        root_lang_attr: $('#' + this.input_id).closest('[data-lang]').data('lang'),
        //table_clone_elements: "",
        table_grid: true,
        table_tab_navigation: true,
        //table_default_attributes: {},
        //table_default_styles: {},
        //table_class_list: [],
        //table_cell_class_list: []
        //table_row_class_list: [],
        //templates: [].
        //textpattern_patterns: [],
        visualblocks_default_state: false,
        automatic_uploads: true,
        images_upload_url: 'tinymce/upload/image'
    };

    if (('plugins' in default_opts) && ('plugins' in this.editor_options)) {
        if ($.type(this.editor_options.plugins) === 'string') {
            this.editor_options.plugins = this.editor_options.plugins.split(' ');
        }

        $.each(this.editor_options.plugins, function (i, pattern) {
            // If the first character is ! it should be omitted
            var exclusion = pattern.indexOf('!') === 0;
            var index;

            // If the pattern is an exclusion, remove the !
            if (exclusion) {
                pattern = pattern.slice(1);
            }

            if (exclusion) {
                // If an exclusion, remove matching plugins.
                while ((index = default_opts.plugins.indexOf(pattern)) > -1) {
                    delete default_opts.plugins[index];
                }
            } else {
                // Otherwise add matching plugins.
                if (default_opts.plugins.indexOf(pattern) === -1) {
                    default_opts.plugins.push(pattern);
                }
            }
        });
        delete this.editor_options.plugins;
    }

    this.editor_options = $.extend({}, default_opts, this.editor_options);
    this.editor_options.selector = '#' + this.input_id;

    // Ensures the hidden input is always up-to-date (can be saved via ajax at any time)
    this.editor_options.setup = function (editor) {
        editor.on('change', function () {
            window.tinymce.triggerSave();
        });
    };

    return this;
};

Charcoal.Admin.Property_Input_Tinymce.prototype.create_tinymce = function () {
    // Scope
    var that = this;

    if (typeof window.tinyMCE !== 'object') {
        var url = this.base_url() + '/tinymce.min.js';
        Charcoal.Admin.loadScript(url, this.create_tinymce.bind(this));

        return this;
    }

    window.tinyMCE.dom.Event.domLoaded = true;
    window.tinyMCE.baseURI = new window.tinyMCE.util.URI(this.base_url());
    window.tinyMCE.baseURL = this.base_url();
    window.tinyMCE.suffix  = '.min';

    // This would allow us to have custom features to each tinyMCEs instances
    //
    if (!window.tinyMCE.PluginManager.get(this.input_id)) {
        // Means we need to instanciate the self plugin now.
        window.tinyMCE.PluginManager.add(this.input_id, function (editor) {
            that.set_editor(editor);
        });

        if ($.type(this.editor_options.plugins) !== 'array') {
            this.editor_options.plugins = [];
        }

        this.editor_options.plugins.push(this.input_id);
    }

    window.tinyMCE.init(this.editor_options);
};

Charcoal.Admin.Property_Input_Tinymce.prototype.elfinder_callback = function (file, elf) {
    if (this.elfinder_dialog) {
        this.elfinder_dialog.onInsert(file, elf);
        this.elfinder_dialog.close();
    } else {
        window.parent.alert('Something went wrong. Could not insert file.');
        window.parent.postMessage({
            mceAction: 'close'
        });
    }
};

Charcoal.Admin.Property_Input_Tinymce.prototype.elfinder_browser = function (control, callback, value, meta) {
    var editor = this;

    control.elfinder_dialog = window.tinyMCE.activeEditor.windowManager.openUrl({
        url:    control.data.elfinder_url + '&' + $.param(meta),
        title:  control.data.dialog_title || '',
        width:  900,
        height: 450
    });

    control.elfinder_dialog.onInsert = function (file, elf) {
        var url, regex, alias, selected;

        // URL normalization
        url = file.url;
        regex = /\/[^/]+?\/\.\.\//;
        while (url.match(regex)) {
            url = url.replace(regex, '/');
        }

        selected = editor.selection.getContent();

        if (selected.length === 0 && editor.selection.getNode().nodeName === 'A') {
            selected = editor.selection.getNode().textContent;
        }

        // Generate a nice file info
        alias = file.name + ' (' + elf.formatSize(file.size) + ')';

        // Provide file and text for the link dialog
        if (meta.filetype === 'file') {
            callback(url, { text: (selected || alias), title: alias });
        }

        // Provide image and alt text for the image dialog
        if (meta.filetype === 'image') {
            callback(url, { alt: alias });
        }

        // Provide alternative source and posted for the media dialog
        if (meta.filetype === 'media') {
            callback(url);
        }
    }

    console.log('Dialog:', control.elfinder_dialog);
    console.groupEnd();

    return false;
};

/**
 * Sets the editor into the current object
 * Might be usefull.
 * @param {TinyMCE Editor} editor The tinymce object.
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_Tinymce.prototype.set_editor = function (editor) {
    this._editor = editor;
    return this;
};

/**
 * Returns the editor object
 * @return {TinyMCE Editor} editor The tinymce object.
 */
Charcoal.Admin.Property_Input_Tinymce.prototype.editor = function () {
    return this._editor;
};

/**
 * Destroy what needs to be destroyed
 * @return {TinyMCE Editor} editor The tinymce object.
 */
Charcoal.Admin.Property_Input_Tinymce.prototype.destroy = function () {
    var editor = this.editor();

    if (editor) {
        editor.remove();
    }
};
