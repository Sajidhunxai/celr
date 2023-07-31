=== LoftLoader ===
Contributors: loftocean
Tags: loader, load, preloader, page preloader, prelader spinner, preloader with custom logo, animated preloader, CSS3 preloader, customize
Donate link:
Requires at least: 5.0
Tested up to: 6.1
Requires PHP: 5.6
Stable tag: 2.4.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

An easy to use plugin to add an animated preloader to your website with fully customisations.

== Description ==

Small and powerful. LoftLoader helps you to add a beautiful animated preloader to your website, and have full control of its look, without writing any code.

[youtube https://www.youtube.com/watch?v=zxiF5hHU1kc]

= Features & Options =
* Enhanced settings panel with WordPress Customizer.
* Pure CSS3 animation. Works in any modern browser.
* 5 ending animation effects.
* 7 loading animation effects. 2 of them support custom image.
* Unlimited background color. Also can change the opacity.
* Unlimited loader color.
* Can add different loaders for different pages on one site (New feature since v2.0. Only works for “Pages”.).
* Responsive & retina ready.
* Option to enable/disable the preloader.
* Option to enable preloader on site homepage only.
* Close button to quit the loading screen. Users can set when to show the close button (range: 5 to 20 seconds), and customize the close button text.
* Ready for translation (.pot file included).
* Light weight, fast. Coded with SASS. CSS & JS minified.

= Online Documentation =
To know how to use the plugin, please click the link to check [online documentation](http://loftocean.com/doc/loftloader/). It’s the documentation of Pro version. But you can still find Lite version features details in it.

= How to contact us =
If you have any questions or encounter a problem while using LoftLoader, please [create a topic in the support forum](https://wordpress.org/support/plugin/loftloader). If you contact us via email, please indicate that your question corresponds to LoftLoader Lite.

= To speed along the support request, please follow the steps below =
* Update to the latest versions of your WordPress, themes and plugins.
* Check [online documentation](http://loftocean.com/doc/loftloader/) and [FAQ](https://wordpress.org/plugins/loftloader/#faq) first.
* Test by [following this guide](https://wordpress.org/support/topic/how-to-check-for-conflicts/) to check if it is a conflict with other plugins or themes.
* If you still cannot find a solution, please contact us.
* When submit your support request, please [follow the guide](https://wordpress.org/support/topic/please-read-before-posting-63/).


= Pro Version =
LoftLoader Pro is available. [Upgrade to pro version](https://codecanyon.net/item/loftloader-pro-preloader-plugin-for-wordpress/17339671?ref=LoftOcean) and get more stunning features!

* Handpick - Turn the preloader on for specific pages or posts.
* Hide the preloader for picked post types - products, portfolios, etc.
* Option to display the preloader once per visitor session.
* Support gradient background color, full background image, and repeating background pattern.
* More ending animations.
* Loader - 19 types of animations to choose. 6 of them support custom images.
* Looping - Animation can be set to loop forever, and some animations can be set to run just once (ends after page has loaded).
* Progress - Progress bar, counting percentage, or both. Any color. Position, size & font control.
* Message - Enter your custom welcome or loading message to display. Control color, position & font. And supports Random Message feature.
* Load Time - Set a minimum load time to prevent the pre-loader from disappearing too fast.
* Device Control - Choose to show the loading screen on all devices, or hide on mobile, or show on mobile only.
* Option to enable smooth page transition effect. [Check the example](http://demo.loftocean.com/loftloader-transition/).
* Choose to save customize styles as inline styles in &lt;head&gt; or as an external .css file.
* Option to disable page scrolling while loading.
* Can add different loaders for different Pages, Posts and custom post types (such as portfolios, products, etc.) on one site.
* Users can choose which elements to detect during the loading process (All elements / Images / Videos / Images & Videos).
* Purchase once, free lifetime update with new features.

Check some [examples](http://www.loftocean.com/loftloader/example-13/) created by pro version.

**Please note**: Although you may find that LoftLoader Lite (the free version) can run on older versions of WordPress (below v4.7), **LoftLoader Pro only supports WordPress v4.7 or higher**. Because the Pro version has more features than the Lite version, including some new features introduced after WordPress 4.7. We always recommend using the latest versions of WordPress to ensure compatibility with your theme and other plugins.

== Installation ==

1. Install the plugin either via the WordPress.org plugin directory, or by uploading the files to your server (in the /wp-content/plugins/ directory).
2. Activate the LoftLoader plugin through the 'Plugins' menu in WordPress.
3. In the WordPress administration screen, go to Settings > LoftLoader Lite, check the settings there.

== Frequently Asked Questions ==

= No changes on activation/deactivation =
- If you have installed any caching plugins then clear the cache.

= LoftLoader doesn’t work properly =
- If your site is using a cache/performance optimization plugin, it might move all JavaScript on your site into the footer of your page. But the JavaScript of LoftLoader needs to be put in the < head > of a page so it can be executed as soon as possible. Many cache/performance optimization plugins have the “exclude” feature which allows to skip some code/files from being aggregated and minimized. To solve this kind of problem, please exclude files of LoftLoader.

== Screenshots ==

1. LoftLoader settings panel
2. LoftLoader background settings
3. LoftLoader loader settings
4. Pro version: Settings Panel
5. Pro version: Custom Image Loading
6. Pro version: Gradient Background
7. Pro version: Background Image
8. Pro version: Load Time, Device Control & Smooth Page Transition

== Changelog ==
= 2.4.0 =
* New: New ending animation - "No Animation"
* Fixed: Compatibility with third party plugin "Disable Blog"
* Improved: Compatibility with WordPress 6.1

= 2.3.8 =
* New: Option to remove plugin data after deactivating the plugin
* Improved: Compatibility with WordPress 5.9

= 2.3.7 =
* Improved: Compatibility with WordPress 5.8

= 2.3.6 =
* Improved: Added width and height attributes to image elements
* Improved: Removed most of non-composited animations

= 2.3.5 =
* Improved: Compatibility with WordPress 5.6

= 2.3.4 =
* Fixed: Potential compatibility issues with some themes in rare cases

= 2.3.3 =
* Improved: Potential compatibility issues in some rare cases

= 2.3.2 =
* Improved: Compatibility with WordPress 5.5

= 2.3.1 =
* Fixed: The preview area is not displayed when setting the loader

= 2.3 =
* Improved: Hide loader when opening theme customizer
* Improved: Hide loader when editing content with a third-party page builder plugin
* Improved: Added a data attribute when the loader is created with Any Page Extension feature (for better debugging)
* Fixed: Potential conflicts with Gutenberg when Any Page Extension feature is enabled
* Fixed: Minor CSS issues

= 2.2.2 =
* Improved: Compatibility with lazy loading feature
* Improved: The same JS code is loaded no matter how to load LoftLoader JavaScript code (External or Inline)
* Fixed: Any page shortcode inherits the global settings as default settings

= 2.2.1=
* Fixed: Max Load Time did not work when choosing to load LoftLoader JavaScript as inline JavaScript

= 2.2.0
* New: Option to set a Maximum Load Time
* Improved: Rearranged options in More section
* Fixed: Any Page Extension - shortcode did not work on WooCommerce Shop Page

= 2.1.12
* Fixed: Minor style issues in LoftLoader Settings panel for WordPress 5.3
* Fixed: Minor CSS issues of Settings Panel UI for RTL sites

= 2.1.11 =
* Fixed: Compatibility issue with ConveyThis Translate plugin
* Fixed: Media Library doesn't show on LoftLoader Settings panel

= 2.1.10 =
* Fixed: Image moves while loading with Custom Image Loading loader
* Changed: Pro version link on settings page

= 2.1.9 =
* Fixed: Compatibility issue with the pro version of Loftloader when both plugins are activated

= 2.1.8 =
* Fixed: Compatibility issue with maintenance/coming soon plugins

= 2.1.7 =
* New: Option to choose how to load LoftLoader’s JavaScript (External or Inline)
* Fixed: Gutenberg “Update” button was not clickable after making changes only to Any Page Extension options
* Fixed: Minor CSS issues

= 2.1.6 =
* Improved: Any Page Extension meta box redesigned to fit in Gutenberg Sidebar

= 2.1.5 =
* Fixed: Description for Close Button doesn’t work for Any Page Extension
* Fixed: Minor CSS issues

= 2.1.4 =
* Fixed: Added CSS for screen reader text in this plugin (the text “close” of the close button is only for screen readers and should not be visible on screens)

= 2.1.3 =
* New: Add close button and related settings

= 2.1.2 =
* Fixed: Compatibility issue which affected Customizer > Menu panel.
* Fixed: Minor CSS issues of Settings Panel UI for RTL sites

= 2.1.1 =
* Fixed: Settings Panel UI compatibility issues with WordPress v4.9

= 2.1.0 =
* Improved: LoftLoader Customizer panel independence (so it won’t be affected by theme or other plugins’ code)
* New: Loader Section - New Loader Animation: Beating
* Fixed: Minor CSS issues

= 2.0.0 =
* Changed: Rewrite the framework, move all settings to wp customize
* Added: Any page extension

= 1.0.2 =
* Fixed: Load before anything bug
* Fixed: Wave animation minor bug
* Added: Pro version link on settings page

= 1.0.1 =
* Add support for site homepage only

= 1.0.0 =
* Initial Public Release

== Upgrade Notice ==

= 1.0.0 =
* Initial Public Release
