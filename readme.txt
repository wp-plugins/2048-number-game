=== 2048 ===
Contributors: envigeek
Tags: 2048, game, number, number game,
Requires at least: 3.5
Tested up to: 3.9.1
Stable tag: 0.3.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

2048 is a number combination game with the aim to achieve 2048 tile.

== Description ==
Join the numbers and get to the 2048 tile! This is a number combination game with the aim to achieve 2048 tile. Display the game based on the original 2048 design or customized the game appearance to your choice of colors, tile's text or numbers, font sizes or even use image as tiles. The highest score ever encountered on your site are stored and displayed in widget to encourage playing the game. The plugin also saves each logged-in user's high score if scoreboard is enabled. Read the FAQ to understand more about the plugin.

**How to play:** Use your arrow keys to move the tiles. When two tiles with the same number touch, they merge into one!

Based on the open-source game created by [Gabriele Cirulli](http://gabrielecirulli.github.io/2048/) which is a clone of [1024](https://play.google.com/store/apps/details?id=com.veewo.a1024) Android game.

== Installation ==
1. Install from within WordPress plugin installer, or get from WordPress plugin repository
1. Activate the plugin through the "Plugins" menu in WordPress.
1. Create or edit any page (or post) and insert the game shortcode using the selector besides Add Media button.

To customize the game appearances, go to **Settings > 2048 Number Game > Custom Features** to change the colors, text or number appeared on tiles, the font sizes and also to use image as the tile.

A widget is available to show site's high score or your top 5 scores, and can be linked to your choice of page that contains the game board.

Use shortcode [scoreboard2048] to display a list of your logged-in users high score.

== Frequently Asked Questions ==
= How the high score works? =
Since version 0.3, the plugin can store each logged-in users high score is enabled in the settings. Previous version only keep a single high score for the site.

When someone ended a game (either won or game over) and if their high score more than your site\'s high score or their personal high score record, the game will save the new score. It is automatically saved for any logged-in users. If you enable to allow guest users to submit high score, an email field will appear to them before the score submission. The email address is required to notify them of new high score updates.

= Tips on using custom image for tiles =
Minimum image dimension is 107px width and height. The game tiles resizes to 58px on smaller screens. Recommended to use a perfect square image. The plugin will force your image to appear in perfect square anyway. Do not use transparent PNGs as you will see overlapping image tiles when merging tiles.

= How the use the custom shortcode generator? =
On the *Custom Features* tab of plugin settings page, make the changes you wanted to customized colors, tiles text and font size, or using image as tiles. Check or uncheck the *Enabled Features* as required. Without saving the changes, click the **Generate** button and a textarea will be shown with the custom shortcode. Copy the shortcode and paste it on your page or post. Publish/save it to see the game in custom appearances.

= Will it work if I insert the shortcode into text widgets? =
Yes, technically it will work. Depends on your theme, your widget width may not fit the game board thus may cause unexpected result. It is always recommended to have the game board on its own page or post.

== Screenshots ==
1. Submit high score when game ended.
1. Easily add the game shortcode on post editor.
1. The plugin settings page.
1. Various customizations to the game appearance.
1. Generate customized shortcode easily.
1. Widget that shows current highest score.

== Changelog ==
= 0.3.1 =
* Fix: Scoreboard shows 0 score when no scores saved yet
* Fix: Plugin data removal and uninstall not working
* Several minor bug fixes
= 0.3 =
* New: Stores high scores on each logged-in users
* New: Score board listing site's users scores
* New: Play game in Full Screen mode (Beta feature)
* Enhancements: Upgrade image tile selector to use WordPress new Media Modal
* Fix: Image tile not appearing
* Fix: Widget page option not saving
= 0.2 =
* New: Now stores the highest score into WordPress database.
* New: Notify user when their score was beaten by someone else.
* New: Option to allow or block guest users to submit high score.
* New: High score Widget linked to game page.
* New: Display the game based on original design or customized the colors, text, and use image as tiles.
* New: Add shortcode button in post editor.
* New: Generate custom shortcodes for unlimited game board designs.
* New: Added customizable meta viewport into header.
* New: Added meta mobile friendly into header.
* New: Settings page for default options and compatibility mode
* Fix: CSS that caused various WordPress theme to be broken
* Now requires at least WordPress 3.5 and PHP 5.3
= 0.1.1 =
* Fix CSS Container
= 0.1 =
* Initial release.

== Upgrade Notice ==
= 0.3 =
* Now store each users high score, scoreboard, play in full screen and several bug fixes plus enhancements