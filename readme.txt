=== Chat Forms ===
Contributors: chatforms
Tags: Chat Forms, Google Forms, Google Docs, Google, Spreadsheet, shortcode, forms
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=FGLJXAATGV2K2&source=url
Requires at least: 4.0
Tested up to: 5.2.3
Requires PHP: 5.2
Stable tag: 1.0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Embeds a Chat Form, in a WordPress post, page, or widget.

== Description ==
Fetches a published Google Form using a WordPress custom post or shortcode, removes the Google wrapper HTML and then renders it as a Chat Form embedded in your blog post or page. When using Chat Form post type, the *wpcform* shortcode accepts one parameter, *id*, which is the post id of the form. There are a number of other options, refer to the documentation for further details.

For example, suppose you want to integrate the form at `https://docs.google.com/spreadsheet/viewform?hl=en_US&pli=1&formkey=ABCDEFGHIJKLMNOPQRSTUVWXYZ12345678#gid=0`, (not a real URL) use the following shortcode in your WordPress post or page:

    [wpgform id=\'861\']

Currently, this plugin only supports Google Forms that are public. Private Google Forms are not supported.

== Installation ==
1. Install using the WordPress Plugin Installer (search for `WordPress Chat Form`) or download `WordPress Chat Form`, extract the `wpcforms` folder and upload `wpcforms` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the \'Plugins\' menu in WordPress.
3. Configure `WP Chat Forms` from the `Settings` menu as appropriate.
4. Recommended:  Create a Chat Form Custom Post Type and then use the `[wpcform id=\'1\']` shortcode wherever you\\\'d like to insert the a Chat Form or simply publish the form and use it\\\'s permalink URL.

== Frequently Asked Questions ==
= What is a Chat Form? =
Chat Form is a Form that uses a chat interface to interact with the user.

= How can I create a Chat Form? =
First you need to create a public Google Form and than paste the Google Form link to the Chat Forms plugin, select a language and click on the \\\'Generate\\\' button.

= How does a Chat Form interacts with Google Forms? =
All replies are being directly send to the Google Form that is behind the google form and feed to the attached spreadsheet.

= How is behind Chat Forms? =
Chat Forms is a free tool created by [inter-act](https://inter-act.io/)

= Can I see more examples? =
For more examples go to [Chat Forms ](https://chat-forms.com/) website.

== Screenshots ==
1. Chat Forms assets list view
2. Chat Form edit screen
3. Chat Form configurations
4. Post edit screen with a Chat Form Shortcode
5. Post view with embedded Chat Form

== Changelog ==
= Version 1.0.3 =
* Remove debug hooks

= Version 1.0.2 =
* Initial submission

== Upgrade Notice ==
No known upgrade issues.