=== My Content Management ===
Contributors: joedolson
Donate link: http://www.joedolson.com/donate/
Tags: custom post types, post types, faq, testimonials, staff, glossary, sidebars, content management
Requires at least: 4.2
Tested up to: 5.9
License: GPLv2 or later
Text domain: my-content-management
Stable tag: 1.7.0

Creates common custom post types for advanced content management: FAQ, Testimonials, people (staff, contributors, etc.), and others!

== Description ==
My Content Management creates a set of custom post types, each with a custom taxonomy and a set of commonly needed custom fields. A single common interface to create popularly desired content tools. 

In addition to the default post types and field groups, you can create new custom post types, create new groups of custom fields, and edit the post types and custom field groups that are built in.

There's no default styling in My Content Management, so you won't have any problems with conflicts between the plug-in and your theme. There is default HTML, but it can be 100% replaced through the included templating system, or by creating your own theme template documents to display these specific content types. 

= Help Support Future Development! =

[Buy the User's Guide](http://www.joedolson.com/my-content-management/guide/), which offers 35+ pages of detailed information on how to set up, use, and customize My Content Management.

= How to Use My Content Management =

All content can be displayed using the shortcode [my_content type='custom_post_type']. Other supported attributes include:

* type (single or comma-separated list of types)
* display (custom, full, excerpt, or list)
* taxonomy (slug for associated taxonomy: required to get list of terms associated with post; include a term to limit by term)
* term (term within named taxonomy)
* operator (IN, NOT IN, or AND) == how to treat the selected terms. Choose posts with that term, without that term, or using all terms supplied.
* count (number of items to display - default shows all)
* order (order to show items in - default order is "menu_order" )
* direction (whether sort is ascending, "ASC", or descending, "DESC" (default))
* meta_key ( custom field to sort by if 'order' is "meta_value" or "meta_value_num" )
* template ( set to a post type to use a template set by that post type. If "display" equals "custom", write a custom template. )
* custom_wrapper ( only used when custom template in use; wraps all results in this html element with appropriate classes)
* offset (integer: skip a number of posts before display.)
* id ( comma separated list of IDs to show a set of posts; single ID to show a single post.)
* cache (integer: number of hours to cache the results of this shortcode)
* year (integer)
* month (integer, 1-12)
* week (integer, 0-53)
* day (integer, 1-31)

A search form for any custom post type is accessible using the shortcode [custom_search type='custom_post_type']

Create a site map for a specific post type and taxonomy using the [my_archive type='custom_post_type' taxonomy='taxonomy'] shortcode. Other supported attributes include all those above, plus:

* exclude (list of comma-separated taxonomy terms to exclude from the site map)
* include (list of comma-separated taxonomy terms to show on the site map)

The "id" attribute is not supported in the [my_archive] shortcode. (That would be silly.) The [my_archive] shortcode does support a "show_links" attribute which will turn on a navigation list to navigate to each displayed category.

== Changelog ==

= Future =

* Feature: repeatable field groups [todo]
* Feature: Add user and post template tags to pick up data from related posts/users [todo]
* Feature: Add event post type to integrate with My Tickets.

= 1.7.1 =

* Bug fix: Incorrect variable type caused caching to default to enabled.
* Bug fix: Adding and editing post types was broken.
* Change: Save post updates on admin_init instead of view.
* Change: Set default full item wrappers to empty.

= 1.7.0 =

* Update code to meet PHPCS expectations.
* Manage array values in displays.
* Eliminate support form.
* Add no support statement.
* Refine layout.
* Remove detectors for theme-defined front-end scripts & styles.
* Add 3rd taxonomy for all MCM post types.

= 1.6.2 =

* Update to use wp_localize_script correctly.

= 1.6.1 =

* Calling get_the_id() as an argument can pull the wrong ID. (props @jjjoel)
* Add filter to add custom data inside post metaboxes 'mcm_build_custom_box'.
* Add: Post-type specific tags.
* Change: Add custom taxonomies to block editor UI.
* PHP 7.4 compatibility fixes

= 1.6.0 =

* Add: Field support for checkbox groups.
* Add: Field support for single checkboxes.
* Bug fix: Don't produce field attachments twice.
* Bug fix: Unescaped field value on admin side in text field.
* Bug fix: undefined variable $checked.
* Feature: Add option to enable Block Editor for post types.
* Misc: Start to document functions & move towards WordPress code standards.

= 1.5.8 =

* Bug fix: Editing custom fieldsets did not retain post type values previously assigned.
* Improvement: Possible to edit post type assignments to existing fieldsets.

= 1.5.7 =

* Bug fix: PHP Notice with script localization
* Bug fix: create_function deprecated

= 1.5.6 =

* Bug fix: custom icons passed by URL switch to https in SSL
* Updated 'tested to'

= 1.5.5 =

* Bug fix: Could not configure custom fieldsets unless custom post type was public.
* Bug fix: only show extra fields if defined.
* Add action: do_action on post data when saving custom fields.
* Add post type support option for JetPack publicize
* Design change: Move extra fields to bottom of editing field.
* Update headings hierarchy
* Update tested to

= 1.5.4 =

* Bug fix: mis-named variable prevented attaching the same custom fieldset to multiple post types

= 1.5.3 =

* Add support for selective refresh in customizer
* Only show custom template fields if 'custom' set as template model
* Bug fix: widget needs to remember selected custom wrapper
* Bug fix: fix custom widget templates in custom post list (were being ignored)
* Bug fix: fix custom widget template in custom post data panel (translated entities in admin textarea)

= 1.5.2 =

* Bug fix: incorrect capability type comparison
* Bug fix: Support for underscore character in email scrambling
* Bug fix: avoid possible error if invalid ID passed to shortcode

= 1.5.1 =

* Bug fix: PHP Notice if using custom template in custom post data widget
* Bug fix: Fix for file upload fields being reset if no data uploaded.

= 1.5.0 =

* Bug fix: Chooser input types with parameters returned link; returned URL without. Now return link only if 'size' parameter defined.
* Bug fix: Issue with duplicate values on repeatable fields.
* Bug fix: Avoid output if chooser value not set. props <a href="https://wordpress.org/support/topic/avoid-attachment-missing-output-if-attachment-omitted?replies=2#post-7444956">@NotDifficult</a>
* Documentation: Add support for template tag attributes to editor. props <a href="https://wordpress.org/support/topic/add-inline-help-for-field-attributes?replies=2#post-7444974">@NotDifficult</a>
* Feature: Ability to limit custom fieldsets from appearing only on specific posts.
* Feature: Ability to set default location of fieldset
* Feature: Add 'format' attribute to format date input values.
* Feature: Custom post data widget; show field values for a specific post/fieldset in a widget in list, table or custom format.
* UI change: Use checkboxes instead of multiselect to display field options.
* Filter: 'mcm_show_administration_pages' to disable all access to admin pages.

= 1.4.20 =

* Mis-named variable in 1.4.19 broke some templates.

= 1.4.19 =

* Feature: Save post meta in revisions when revisioning enabled for post type.
* Bug fix: fallback template, if no valid template set, should just show post content, not default Full template.
* Bug fix: Update class constructors to PHP 5 syntax.
* Bug fix: Improved textdomain loading.
* Made revisions supported by default for new installations.
* Dropped support for WP 3.7 - 3.9

= 1.4.18 =

* Fix to issues with [my_archive] shortcode.
* Add option to disable post filters in [my_content] and [my_archive] shortcode. (Attribute: post_filters=true).
* Disabled post filters by default in [my_archive] shortcode for major speed increase.

= 1.4.17 =

* Bug fix: broken posts widget in 1.4.16; change in major function arguments not applied in widget.

= 1.4.16 =

* Updated translation: French
* Added size-specific classes to templated images
* Bug fix: properly escape attributes in select dropdowns generated in custom field manager.
* Bug fix: sort post relations by post title.
* Bug fix: Could not set empty value for post relations.
* New filter: 'mcm_post_relations' to alter argument values for list of posts.
* New feature: related user to relate a user ID to a post.
* Rewrite major function arguments to use arrays instead of individual variables.
* Add filter to before arguments passed to WP_Query 'mcm_pre_query_args'.
* Changed related users and related posts to autocomplete fields to better handle large data sets.

= 1.4.15 =

* Deleted obsolete function 
* Fixed various PHP notices
* Feature: related posts input field

= 1.4.14 =

* Bug fix: PHP notice with date formatting
* Bug fix: Fieldsets with only one custom field were deleted without warning on edit.
* Added: German & Punjabi partial translations

= 1.4.13 =

* Bug fix: Date format bug if value not a timestamp
* Bug fix: date format bug if date field is repeatable

= 1.4.12 =

* Filter: filter value of submitted post data with 'mcm_filter_saved_data'
* Filter: filter value of output post data with 'mcm_filter_output_data'
* Feature: when using HTML5 date fields, date is saved as a timestamp, allowing sorting by custom dates.
* Bug fix: use reply-to header in support messages
* Bug fix: When editing fieldsets with only 2 fields, possible to inadvertently delete fieldset.
* Bug fix: Support form textdomains cannot be passed as variables
* Translation: Irish updated.

= 1.4.11 =

* Bug fix: You know that bug "fixed" in 1.4.10? It's <em>actually</em> in this release.

= 1.4.10 =

* Bug fix: variable assignment instead of comparison in widget templating.

= 1.4.9 =

* Added {slug} template tag.
* Added filter to target custom field sets to specific posts or sets of posts.
* Support any custom post type in search form
* Support any custom post type in custom post list widget
* Add custom template in widgets (required for non-MCM custom post types)
* Bug fix: My Content Management conflicted with Advanced Custom Fields.

= 1.4.8 =

* Bug fix: type checking to use current post type's preferred template overrode custom template settings.

= 1.4.7 =

* New filter: use mcm_archive_taxonomies to filter arguments for get_terms in archive shortcode.
* Corrected some textdomain issues
* Bug fix: PHP notice unassigned value in media uploader.
* Bug fix: PHP notice undefined array key
* Bug fix: Not possible to remove media selected in chooser if field is not repeatable.
* Bug fix: URL missing in preview text string.
* Bug fix: When displaying multiple content types in a single shortcode, picks up appropriate templates for each different type of content.
* Bug fix: Incorrect sprintf arguments for scheduled posts notice.
* Bug fix: Resolve outstanding issue with menu position collisions.
* Deprecated support for WP 3.2 and WP 3.3.
* Minor UI change.
* Add French translation.

= 1.4.6 =

* Bug fix: Missing arguments in term list shortcode.
* Bug fix: with repeatable fields, mcm_get_custom_field display incorrectly placed before/after.

= 1.4.5 =

* Bug fix: media chooser erased data when used as a non-repeatable field.

= 1.4.4 =

* Bug fix: Eliminated some PHP notices.
* Bug fix: Fixed non-assignment of custom fieldset bug.
* Bug fix: Some issues with quotes in fieldset names.
* Bug fix: missing stripslashes in form output.

= 1.4.3 =

* 12/16/2013
* Bug fix: Don't return wrapper divs and classes if no content.
* Bug fix: formatting and HTML for default templates of new post types.
* Bug fix: Display of saved information in post meta forms for select fields.
* Bug fix: CSS - display of checkboxes in settings.
* Bug fix: Fixed some miscellaneous odd behaviors when saving or updating custom field sets.
* Bug fix: Some improper handling of arrays for custom fields. May impact custom templating.
* Bug fix: Simple templates returned only attachment ID for attachment fields.
* Bug fix: Widget sort order did not work.

= 1.4.2 =

* Add shortcode to produce list of term category links. [my_terms taxonomy='' show_count='true/false' hide_empty='true/false']
* Bug fix in HTML file uploader (removed uploads on edit.)

= 1.4.1 =

* Bug fix: ran wpautop on plaintext elements.

= 1.4.0 =

* Bug fix: custom post type search widget; could not select post type.
* Bug fix: if all "supports" options were disabled, could not later select options.
* Bug fix: automatically truncate post type names to max 20 characters, as required by WP.
* Minor rewrite of custom meta saving process.
* New template tag attribute: fallback (all template tags; set fallback content if field not supplied.)
* New template tag attribute: size (chooser template tags; image size keyword)
* Revised template tag replacement function.
* Added fallback content parameter to mcm_custom_field function.
* If taxonomy not specified, fall back to MCM created taxonomy when generating terms.
* Add support for post_tag taxonomy on all MCM created post types.
* Added media chooser option for input.
* Added rich text editor option for input.
* Added filter 'mcm_filter_editor_args' to customize arguments for rich text editor
* Automatically refresh permalinks.
* Minor UI Changes.
* Performance improvements.

= 1.3.4 =

* Bug fix: custom field values improperly escaped when editing posts.
* Bug fix: missing mimetype for uploaded files
* New feature: change the display order of custom fields when editing.
* New feature: support for all defined featured image sizes.
* New feature: support for passing GET variables to shortcodes.
* New language: Dutch.

= 1.3.3 =

* New feature: Added file uploads option to custom field options.
* Bug fix: function mcm_custom_field() did not exist.
* Assorted bug fixes, [courtesy Juliette](http://wordpress.org/support/topic/few-small-bugsfixes-undefined-variables-and-such)
* Bug fix: Upgrade bug that could wipe out custom fieldsets. Yikes! 
* Bug fix: Empty custom fields returned template tags instead of blank fields.
* Removed Glossary Filter plug-in so that plug-in can be maintained independently. 

= 1.3.2 =

* Bug fix: hyphens not correctly rendered in email munging.

= 1.3.1 =

* Better exposure for custom field keys with added custom fields.
* Fixed bug in display of custom field keys on MCM settings page with modified storage system.
* Fixed bug: before and after variables should not be required for mcm_custom_field() function.

= 1.3.0 =

* Added shortcode option to disable numbers in Glossary filters [Courtesy Bernhard Reiter]
* Added edit post link template tag [Courtesy Bernhard Reiter]
* Fixed a couple PHP notices
* Major update: Added management for custom field groups (Create, Edit, Assign)
* Languages: Added Irish translation.

= 1.2.8 =

* Bug fix: if no custom post types were enabled, 'full' template was rendered on all singular posts/pages.
* Bug fix: embed filter did not run in custom post type templates

= 1.2.7 =

* Bug fix: Widget category limits did not work.
* Bug fix: Widget saving of template type did not work. 

= 1.2.6 =

* Bug fix: could not enable 'hierarchical' without disabling 'publicly_queryable'
* Bug fix: New post types display default templates instead of blank.
* Change: Edit button indicates what is being edited
* Change: add new form only visible on demand
* Added: support for has_archive in post type.
* Added option to delete custom post types.
* Performance improvement in template interpreter

= 1.2.5 =

* Added options for limiting by year, month, week, and day to shortcode.
* Added ability to edit the URL slug used by each custom post type.
* Added limiting by category to widget.
* Added automatic filtering of custom post types single-post view to use Full template as defined in back-end. 
* Fixed a variety of minor bugs. 

= 1.2.4 =

* Resolved bug with empty custom fields not resulting in replaced template tags.
* Added missing email address filter

= 1.2.3 =

* Released 5/17/2012
* Adjusted glossary filter to only link the first two instances of a glossary term on a given page.
* Added 'include' filter for My Archive shortcode.
* Added 'operator' option for Terms (values: in term, not in term, in all terms)
* Adjusted taxonomy and post-type checks to more easily handle types/taxonomies not created by MCM
* Bug fix: shortcut taxonomy post types not recognized.
* Fixed installation error which did not create default custom post types.
* Fixed bug in glosssary plug-in which filtered out content if Glossary post type not enabled.

= 1.2.2 =

* Released 5/7/2012
* Added option to add navigation links to My Archive view
* Added additional filter: mcm_filter_post.
* Added custom variable attribute to shortcode for use in filters.
* Added custom wrapper attribute for use with custom templates.
* Forces theme support for post thumbnails to avoid some errors in themes without.
* Bug fix in template tag attributes.

= 1.2.1 =

* Released 4/7/2012
* Bug fix: missing argument in widget view function.
* Bug fix: Didn't actually add the Spanish translation.

= 1.2.0 =

* Released 4/2/2012
* Added title as an option for widgets.
* Added 'cache' attribute to shortcodes. 
* Added support for showing lists incorporating multiple post types.
* Added editor for post type settings.
* Added ability to add new custom post type.
* Added save notices.
* Added support for two custom attributes in template tags: "before" and "after".
* Added Spanish translation.
* Bug fix: issue with archive shortcode using term name instead of slug.
* Bug fix: default value for My Content display mode was an invalid value.

= 1.1.2 =

* Released 2/23/2012
* Made arguments for mcm_content_filter more generic for broader use.
* Fixed bug where Glossary Filters threw error if Glossary extension was enabled without the Glossary post type.
* Fixed missing arguments in Custom Post List widget
* Added display type (list, excerpt, full) selection to Custom Post List widget
* Added number to display option to Custom Post List widget
* Added ordering selector to Custom Post List widget
* Added order direction selector to Custom Post List widget
* Fixed bug where sidebar widget picked up title value for currently active Post object.

= 1.1.1 =

* Fixes a bug where the glossary filter always triggered an admin notice, due to file inclusion order.

= 1.1.0 =

* Added supplemental plug-in to provide a glossary filter for content and an alphabet anchor list for glossaries.
* Glossary post type has option to include headings to correspond to alphabet anchor list.
* Added shortcode to display archive of entire custom taxonomy organized by term. 
* Added option to use My Content Management shortcodes with any post type, not just those created by My Content Management
* Added generic additional post type called 'Resources'
* Added ability to use a custom template with a given shortcode. 
* Bug fix: Template manager didn't appear immediately when enabling first custom post type
* Bug fix: Errors if disabling all custom post types
* Bug fix: Template manager sometimes showed custom fields not related to the current custom post type.
* Bug fix: Upgrade routine could delete customized templates.
* Bug fix: Support/donate/plug-in links weren't clickable.

= 1.0.6 =

* Whoops! All apologies for 1.0.5. I made it worse. Too much of a hurry.

= 1.0.5 =

* Variable naming error in 1.0.4 caused problem in list wrapper output.

= 1.0.4 =

* Would you believe that I left out the ability to change the sort direction? Ridiculous.
* List wrapper was wrapped around items instead of lists.
* Setting list or item wrappers to 'none' left empty brackets
* Setting list or item wrappers to 'none' was not remembered in settings.
* fixed fopen error on servers with allow_url_fopen disabled

= 1.0.3 =

* Fixes two bugs with custom taxonomy limits, courtesy @nickd32

= 1.0.2 = 

* Defined custom fields for testimonials and quotes were not appearing.
* Added 'title' as a custom field for testimonials and quotes.
* Corrected a too-generically named constant.

= 1.0.1 =

* Removed a stray variable which was triggering a warning.
* Added an array check before running a foreach loop on a sometimes-absent value

= 1.0.0 =

* Initial release

== Installation ==

1. Upload the `my-content-management` folder to your `/wp-content/plugins/` directory
2. Activate the plugin using the `Plugins` menu in WordPress
3. Visit the settings page at Settings > My Content Management to enable your needed content types.
4. Visit the appropriate custom post types sections to edit and create new content.
5. Use built-in widgets or shortcodes to display content or create custom theme files for display.

== Frequently Asked Questions ==

= What's a custom post type? =

All of the  major WordPress features are types of posts: Posts are the main one, but Pages are just a different type of post. This plug-in gives you a whole bunch of other types of posts, so you can handle discrete types of content in different ways - but using a common interface. 

= I don't really get how to use this plug-in. =

Well, there really isn't just one way to use this plug-in. There are many, many different ways to use it. I'd recommend buying the [User's Guide](http://www.joedolson.com/my-content-management/guide/), which will walk you through many of the ways you can use this plug-in. Also, your purchase will help support me! Bonus!

= OMG! What happened to my Glossary page! =

I removed the Glossary Filter plug-in from the My Content Management package in version 1.3.3. It can now be downloaded separately at <a href="http://wordpress.org/extend/plugins/my-content-glossary/">My Content Glossary</a>.

== Screenshots ==

1. Settings Page
2. Custom fields management
3. Assign custom fields to post types.

== Upgrade Notice ==

* 1.5.4: Minor bug fix