===  WP Notification Bell ===
Contributors: wpdever, freemius
Tags: notifications, woocommerce, bbpress, buddypress , dokan, alert, live, message, comments, ajax, dokan, woocommerce notifications
Requires at least: 4.0
Tested up to: 6.2
Stable tag: 1.3.27
Requires PHP: 5.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

On-site bell notifications. Display notifications custom or triggered (new posts/cpts, WooCommerce order updates, new comment replies, bbPress...)
== Description ==
Bell notifications for your website users. Display an on-site notification feed with notifications created manually or through triggers (new posts/cpts, WooCommerce, new comment replies, bbPress...).
WP Notification Bell is a custom notification and bell alert plugin for WordPress. (not push notifications and no emails)
This plugin lets you show real-time notifications to either logged-in users, or guests, or both.

Send on-site notification campaigns and let users know about news, promotions, events, product launches...
Or automatically notify users when you publish a new post or cpt (or when frontend users create a custom post type), or about comment replies, WooCommerce order updates...

= Features =
* **Flexible notifications :** Create any type of notification with as many fields as you need. 
* **Target notifications :** Broadcast notifications to everyone or send to a specific user role or specific usernames.
* **New posts notifications :** Send notifications to users every time you publish a new post. **Custom post types** included.
* **New comments notifications :** Facebook-like notifications to let logged-in users receive notifications for approved comment replies to their own comment, and let post authors receive notifications for approved comments on their post.
* **bbPress notifications :** New reply notification in subscribed topics for bbPress.
* **BuddyPress notifications :** Display BuddyPress notifications to logged-in users.
* **WooCommerce notifications :** Notify customers about each order status update.
* **Unseen notification count :** Display the count of unseen notifications.

= Documentation =
For guides and tutorials, start from [WP Notification Bell documentation](https://wpsimpleplugins.wordpress.com/documentation/).

= Pro Features Available =
* New notification sound
* New comments notifications for guest users
* Display date on comment, buddypress and bbpress notifications
* Custom edit WooCommerce notifications (modify content, add date...)
* Restrict the bell display to specific user roles
* Target notifications to logged-out guests only
* Multiple custom post types new posts notifications
* Move post or cpt notifications to trash automatically 
* Notification page redirection instead of drop-down box option
* AJAX powered recent notification feed widget in real-time
* Advanced Custom Fields placeholders (add ACF fields as default value)
* Polylang integration
* Display all notifications shortcode

= Add-ons Available =
* **WooCommerce back in stock notifications :** Let your customers subscribe for wishlist to know when products are back in stock.
* **Dokan Multivendor Marketplace integration:** Notifications for Dokan vendors (new orders, order status completed, new product reviews).
* **Asgaros Forum integration :** Let users be notified on-site when they're mentioned in a post, or when there's a new reply in a topic they're subscribed to.
* **WooCommerce new order notification :** New order notifications for administrators and shop managers on the admin bar menu.

== Changelog ==
= 1.0 = 
* First version of the plugin.
= 1.0.6 = 
* Fixed deleting notification items
* Added seen/unseen notification data
* Added date of publishing
= 1.0.8 =
* Added custom post type trigger
* Added loading spinner and fixed closing speed
* Changed database query 
* Added filter hook when saving new post for the trigger
= 1.1.0 =
* Added notification bell for logged out users
* Added time placeholder
= 1.1.1 =
* Changed type checking order when adding custom post types
* Styling fixed for visitors and added specific notification item names for visitors
* Added a filter to possibly change the link for cpt
* Fixed links for cpt
= 1.1.2 =
* Added recipient role selection for new post and new cpt
* Fixed ajax calls
= 1.1.3 =
* Reduced server calls for logged out users
* Fixed recipient role selected in editor
= 1.1.4 =
* Fixed styling options for shortcode 
* Removed count increment on post update
* Added post title placeholder
* Added freemius
= 1.1.5 =
* Notifications published after multiple post revisions fixed
* Fixed date timezone for logged out users
* Notifications only after first publish for logged out users
* Removed woocommerce notifications if option disabled
= 1.1.6 =
* Recent notifications widget for premium
* Changed title of All notifications table to post title
= 1.1.7 =
* Added nonce security checks
* Sanitized logged out user cookies
= 1.1.8 =
* Removed wnbell_notifications from custom post types to select
* Added possibility to add notifications for imported posts
= 1.1.9 =
* Added function to check in js script for wnbell_test_ls in pro
* Added option to restrict the bell display for some user roles in pro
* Fixed visited notification for woocommerce
* Added a floating bell option
* Added a full screen notification box for small devices
= 1.1.10 =
* Changed z-index for mobile devices
* Moved mediaquery js variable from js file to php file
= 1.1.11 =
* Removed a permission check for custom post types
= 1.2.0 =
* Changed how the count gets updated
* Added a new table to improve performance
* Stored server call interval as a global js variable
= 1.2.1 =
* Delete custom table row on post deleting
* Count for new logged in users is 1 instead of counting all existing notifications
* Added ACF placeholders for pro
* Joining with new role table for visitors
* Fixed infinite loop if cpt option not set
= 1.2.2 =
* Improved visitor count query
* Added Polylang integration in pro
* Changed woocommerce order display so it displays status label instead of slug
* Updated pot file
* Added table for usernames
* Fixed post date format to correctly compare with user notifications
= 1.2.3 =
* Now updates links on post update
* Fixed new notification for cpt from frontend
= 1.2.4 = 
* Fixed seen notifications for woocommerce order updates
* Fixed user notifications order with custom notifications
* Added back in stock add-on
= 1.2.5 = 
* Fixed notification count display and notification sort for user notifications only use case
= 1.2.6 =
* Removed default notification for new posts without meta box
* Added logged out role for premium
* Added page redirection for premium
* Added filter hooks for query conditions
* Added server call interval for widget
= 1.2.7 = 
* Replaced link a href tags with divs 
* Added a variable set check
= 1.2.8 =
* Removed admin check in new user submitted cpt notifications 
* Adding custom fields for woocommerce order updates
* Added css class to style the bell
= 1.2.9 =
* Save 'enable new post notification' option from post meta box no matter its initial state
* Added back the default notification function for new posts (type 'post')
* Fixed admin css
* Added multiple notification sound options in premium
* Added hooks to add seen/unseen functionality to custom triggers
= 1.2.11 = 
* Constant bug fix
* Fixed Polylang notifications
= 1.2.12 =
* Restyled the dropdown box
* Added comment notification for post authors
* Fixed comment links for approved comments
* Delete post or cpt notification automatically on post or cpt deletion in pro
= 1.3.0 =
* Updated bbpress get subscribers function
* Gave bbpress option, engagement or subscription notifications
* Changed bell icon css in menu
* Added guests comment notifications in pro
= 1.3.1 =
* Changed woocommerce modified date access, order properties no longer accessed directly
* Fixed box position and added padding in empty box
* Fixed Polylang language for imported posts
* Added time to trigger array
= 1.3.2 =
* Fixed unseen notification style id for user notifications
* Fixed Polylang language for imported custom post type posts
* Added hooks to user notifications
* Fixed sorting user notifications
= 1.3.3 =
* Added display option
* Fixed sql query for logged out user comment notifications
* Convert first letter to uppercase in comment and bbp notifications
= 1.3.5 = 
* Added buddypress notifications
= 1.3.7 =
* Added check for new comments by logged out users
* Fixed buddypress bbpress notifications
= 1.3.8 =
* Added title in list of notifications
* Fixed the count for logged in first time users 
= 1.3.9 =
* Fixed comment notifications
= 1.3.10 =
* Fixed date format to sort notifications
= 1.3.14 =
* Fixed undefined variable for visitor shortcode
= 1.3.19 =
* Allowed spaces in recipient username


== Frequently Asked Questions ==

== Screenshots ==
1. Step 1 is to enable 'Add bell icon to menu' either for logged-in users or visitors or both in General->Settings, or add one of the shortcodes.
2. Step 2 is to add fields in 'Notification item' tab in the settings.
3. Step 3 is to add a new notification.
4. Step 4 is to add an image (optional).
5. Notification bell for logged-in users added to a menu.
6. Notifications in a drop-down box with the comment reply trigger enabled, and manual notifications.
7. Notification bell for visitors (logged out users).
8. Empty notification box.