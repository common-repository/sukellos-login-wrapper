=== Sukellos Login Wrapper ===
Contributors: Sukellos
Donate link: https://wp-adminbuilder.com/
Tags: admin, admin builder, custom admin panel, custom admin pages, option, user meta, post meta, login, logout, login wrapper
Requires at least: 5.2
Tested up to: 6.6.2
Stable tag: 1.1.8
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
URI: https://wp-adminbuilder.com/login-wrapper/

== Description ==

Sukellos Login Wrapper enrich the WordPress login with basic features (redirection, front end profile shortcode...)

This plugin integrates with other Sukellos Tools WordPress plugins to group their settings in a convenient and centralized administration panel.

[Learn more about Sukellos plugins here.](https://sukellos.com/ "Sukellos plugins")

== Admin Builder Basic ==

Each Sukellos plugin integrates Sukellos Framework, and the Basic version of a feature called Admin Builder.

= WP Plugin Development In A Minute =

The Sukellos framework was designed to **speed up the development of WordPress plugins**.

Once installed in WordPress, its use is immediate and intuitive thanks to its object-oriented structure, by using inclusion and inheritance. It allows you to overcome all the constraints of integration with WordPress, and to focus on the essentials of your functional logic.

[Get the basic plugin offered, and consult our documentation](https://sukellos.com/tutorial/basic-plugin-installation/ "Sukellos Basic Plugin") to know how to use the Sukellos Framework to make your work easier.

= Easy Admin Pages. Magnify Options =

The Admin Builder is the main features embedded in Sukellos Framework. It allows to **easily build powerful and beautiful custom admin pages** in WordPress Dashboard.

The Admin Builder can be included very simply in your own plugin to create an administration page in a few lines of code. This takes the hassle out of your hands, making high-level designs possible with very little development skill. No need to worry about implementing the form, handling writing fields, just focus on your configuration logic.

Admin Builder is a powerful way to create configuration pages to **manage WordPress options**.

Example:
`
// Admin page.
 $admin_page = Admin_Builder::instance()->create_admin_page(
     array(
         'name' => 'My admin page',
         'id' => 'my_admin_page',
         'desc' => __( 'My admin page description', 'text_domain' ),
     )
 );

 // Create a text option field
 $admin_page->create_option(
     array(
         'type' => Item_type::TEXT,
         'id' => 'text_option',
         'name' => __( 'Text', 'text_domain' ),
         'desc' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
     )
 );`


A lot of standard fields are available:

* Checkbox
* Multiple choices (checkbox and select) on custom data
* Content
* Header
* Radio
* Text
* Textarea
* Upload

Admin Builder is designed to be used by developers. **Many hooks are available** to allow full style and behavior customization.

[Get the Admin Builder Examples plugin, and consult our documentation,](https://sukellos.com/tutorial/admin-builder-examples-installation/ "Sukellos Admin Builder Examples") to get many examples to copy / paste.

== Upgrade to Admin Builder Pro ==

[Learn more about Sukellos Admin Builder Pro here.](https://sukellos.com/wordpress-sukellos-fw-admin-builder/ "Sukellos Fw & Admin Builder")

= More field and features =

Upgrade to pro to get more field types...

* AJAX button (and feature)
* Code (JS, CSS, SCSS)
* Color picker
* Date picker
* EDD Licensing (Easy Digital Downloads)
* Enable
* File upload
* Gallery
* Note
* Number slider
* Select and multiple choices on predefined WordPress data (users, posts, terms, fonts...)
* Sortable
* WYSIWYG editor

... and allows tabs creation in admin pages.

**Creating an AJAX request becomes child’s play.**

= Enrich Post Types And User Profiles =

Take control of custom fields in any type of posts. **The creation of Metabox becomes very simple.** All the standard fields can also be used, but this time by associating them with any post type, stored as post_meta. Admin Builder allows disabling classical custom fields display. **Users can be enriched** by adding fields that are directly visible and modifiable in their own profiles. The management of this user_meta is also possible directly in administration pages.

In the same way as for the options, the management of the post_meta and the user_meta is simplified as much as possible. **Just a few lines of code are enough.**

= Automatic CSS Generation. SCSS Support. =

In the administration pages, **the options can be taken automatically into account in CSS.** Each field value can be dynamically associated with a CSS. More complex styles can also be generated from an administration page thanks to **the magic method create_css**, in a very simple way. Admin Builder allows the use of a code-like configuration field, thanks to the inclusion of the Ace project. This control offers an input area that supports the CSS / SCSS format. This field can be **automatically generated and included in the WordPress front end.**

== Frequently Asked Questions ==

= Is Sukellos Fw & Admin Builder intended for developers? =

It is intended for WordPress developers of all levels, but wishing to design beautiful administration pages very easily.

== Screenshots ==

1. Admin Page with content: screenshot-1.png
2. Menu edition: screenshot-2.png
3. Front-end menu: screenshot-3.png

== Changelog ==

= 1.1.8 =
* New icon, banner, screenshots

= 1.1.7 =
* WordPress 6.2.2 compatibility
* Basic Sukellos Fw evolutions 1.3.0

= 1.1.6 =
* New colors

= 1.1.5 =
* WordPress 6.3.0 compatibility
* New banners

= 1.1.4 =
* WordPress 6.2.2 compatibility
* Basic Sukellos Fw evolutions 1.2.0

= 1.1.3 =
* WordPress 6.0.3 compatibility

= 1.1.2 =
* WordPress 6.0.2 compatibility
* Project URL moved

= 1.1.1 =
* Basic Sukellos Fw evolutions 1.1.1

= 1.1.0 =
* WordPress.org SVN repo integration of Enable Classic Editor
* WordPress.org SVN repo integration of Login Wrapper

= 1.0.8 =
* WordPress.org SVN repo integration of Login Style

= 1.0.7 =
* Fix license page display for Admin Builder Basic

= 1.0.6 =
* WordPress.org SVN repo integration of Image Formats
* Renaming Disable Gutenberg in Enable Classic Editor

= 1.0.5 =
* Removing wp- prefix

= 1.0.4 =
* WordPress.org SVN repo integration of Dashboard Bar

= 1.0.3 =
* No update URI
* Correct Stable tag
* Improve security with includes
* Sukellos Fw evolutions

= 1.0.2 =
* Licensed plugin

= 1.0.1 =
* Sukellos Fw evolutions

= 1.0.0 =
* First release