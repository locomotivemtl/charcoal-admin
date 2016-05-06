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

Charcoal.Admin.Property_Input_Tinymce = function (opts)
{
    this.input_type = 'charcoal/admin/property/input/tinymce';

    // Property_Input_Tinymce properties
    this.input_id = null;
    this.editor_options = null;

    this.set_properties(opts).create_tinymce();
};
Charcoal.Admin.Property_Input_Tinymce.prototype = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_Tinymce.prototype.constructor = Charcoal.Admin.Property_Input_Tinymce;
Charcoal.Admin.Property_Input_Tinymce.prototype.parent = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_Tinymce.prototype.set_properties = function (opts)
{
    this.input_id = opts.id || this.input_id;
    this.editor_options = opts.editor_options || opts.data.editor_options || this.editor_options;

    var default_opts = {
        //language: 'fr_FR',

        // Plugins
        plugins: [
            'advlist',
            'anchor',
            'autolink',
            'autoresize',
            //'autosave',
            //'bbcode',
            'charmap',
            'code',
            'colorpicker',
            'contextmenu',
            //'directionality',
            //'emoticons',
            //'fullpage',
            'fullscreen',
            'hr',
            'image',
            //'imagetools',
            //'insertdatetime',
            //'layer',
            //'legacyoutput',
            'link',
            'lists',
            //'importcss',
            'media',
            'nonbreaking',
            'noneditable',
            //'pagebreak',
            'paste',
            //'preview',
            'print',
            //'save',
            'searchreplace',
            //'spellchecker',
            'tabfocus',
            'table',
            //'template',
            //'textcolor',
            //'textpattern',
            'visualblocks',
            'visualchars',
            'wordcount'
        ],

        // Toolbar
        toolbar: 'undo redo | ' +
        'styleselect | ' +
        'bold italic | ' +
        'forecolor backcolor | ' +
        'alignleft aligncenter alignright alignjustify | ' +
        'bullist numlist outdent indent | ' +
        'link image anchor',

        // General
        browser_spellcheck: true,
        end_container_on_empty_block: true,
        entity_encoding:'raw',

        // Cleanup / Output
        allow_conditional_comments: true,
        convert_fonts_to_spans: true,
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
        document_base_url: '{{base_url}}',
        relative_urls: true,
        remove_script_host: false,

        // Plugins options
        autoresize_min_height: '150px',
        autoresize_max_height: '400px',
        //code_dialog_width: '400px',
        //code_dialog_height: '400px',
        contextmenu: 'link image inserttable | cell row column deletetable',
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
        media_poster: true,
        media_dimensions: true,
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
        visualblocks_default_state: false

    };

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

Charcoal.Admin.Property_Input_Tinymce.prototype.create_tinymce = function ()
{
    if (typeof window.tinyMCE !== 'object') {
        var that = this;
        this.load_assets(function () {
            that.create_tinymce();
        });
        return this;
    }

    window.tinyMCE.init(this.editor_options);
};

Charcoal.Admin.Property_Input_Tinymce.prototype.load_assets = function (cb)
{
    var url = Charcoal.Admin.base_url() + 'assets/admin/scripts/vendors/tinymce/tinymce.min.js';

    $.getScript(url,
    function () {
        if (typeof cb === 'function') {
            cb();
        }
    });
    return this;
};
