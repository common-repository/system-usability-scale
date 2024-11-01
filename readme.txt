=== System Usability Scale ===
Contributors: gparrotta
Credits: Project designed for the Course of Progettazione e Produzione Multimediale del Prof. Alberto Del Bimbo - Università degli studi di firenze; Idea by Andrea Ferracani; Development by Giuseppe Parrotta.
Tags: sus, system, usability, scale
Requires at least: 4.9
Tested up to: 5.8
Stable tag: 1.0
Requires PHP: 5.2.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allow users to submit System Usability Scale's questionnaires into your website.

== Description ==

System Usability Scale is a plugin developed for valutation of Progettazione e Produzione Multimediale subject in Università degli studi di Firenze

This plugin let:

* Admins to create System's page who contain a redirect for system usability scale's questionnaire.
* Admins to create MacroSystems that groups various Systems.
* Admins to check among the submitted sus, and to take a look to the average of all Systems and MacroSystems.
* Admins to set a name for each System and also for MacroSystems.
* Admins to set who can see sus submitted.
* Users to submit sus once clicked on redirect.

== Changelog ==

= 1.0 =
Initial release.

== Frequently Asked Questions ==

= If i click on "Delete" from setting page, that page will be deleted? =
No, but that page will lost its button. You can add it again using the shortcode.

= Who can submit questionnaire? =
It depends on what parameter you pass into [sus_here required_login="PARAMETER"] between true or false: if true, only registered user will be able to see the redirect button.