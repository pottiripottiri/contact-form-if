=== Contact Form IF ===
Contributors: skplus
Tags: contact, form, contact form
Requires at least: 5.8
Tested up to: 5.8
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires PHP: 7.0

It is a plugin that adds conditional branching to the required check of the Conact form 7.

== Description ==

* Contact Form 7 must be installed to run this plugin.
* In the form created with Contact Form 7, you can add a condition to the required check of the item.
* You can specify various conditions such as when an item has been entered or when it is equal to the specified value.

[Docs](https://sk-plus.github.io/product/contact-form-if/)

= Available conditions =

* is null
* not null
* equal
* not equal
* greater than(>)
* greater equal(>=)
* less than(<)
* less equal(<=)
* in
* not in

= Example =

This setting is required only when a certain item is 2 or more.
See the developer's site for other use cases.
[Docs](https://sk-plus.github.io/product/contact-form-if/)

Form
```
<label>Text1</label>[text eq-1]
<label>Text2</label>[text eq-2]
<br>
[submit "Submit"]
```

Additional Settings
```
requireif-eq-2: eq-1,greater_equal,2,This item is required when "text 1" is 2 or more.
```

== Installation ==

1. From the WP admin panel, click “Plugins” -> “Add new”.
2. In the browser input box, type “Contact Form IF”.
3. Select the “Contact Form IF” plugin and click “Install”.
4. Activate the plugin.

OR…

1. Download the plugin from this page.
2. Save the .zip file to a location on your computer.
3. Open the WP admin panel, and click “Plugins” -> “Add new”.
4. Click “upload”.. then browse to the .zip file downloaded from this page.
5. Click “Install”.. and then “Activate plugin”.

== Frequently Asked Questions ==

== Screenshots ==

1. screenshot-1.png

== Changelog ==

== Upgrade Notice ==