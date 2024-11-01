=== Sign-up List ===
Contributors: RobinLopulalan
Tags: signup, sign up, signups, volunteer, volunteers, participant, participants, guest, guests, event, events
Requires at least: 5.8
Tested up to: 6.5.2
Stable tag: 1.0.0
Requires PHP: 7.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Publish a sign-up list to rally up volunteers, event guests, participants and the likes. Show people who's on the list and let them sign up.

== Description ==
This plugin let's you add a sign-up list (sign up sheet) to your web site. It supports one concurrent list which can be published on as many posts and pages as you like.

You can choose how many people can sign up (max 200). The list will show how many spaces are left and will automatically close when full.

Choose who can sign up:
- anyone (CAPTCHA protected)
- people who have been provided a special link
- people who are on the invitation list (email address)

Choose what is publicly on display of people that signed up:
- First name
- Full name
- Nothing

When someone signs up, the plugin will store name, email address and one custom text field.
The list can be exported to CSV so you can follow up to the people on the list by email.
List entries can be managed in the WordPress admin interface.

If applicable, the invitation list can also be managed via the admin interface. 
Invitees can be added one by one or in bulk as a long list of email addresses.

Choose the styling:
- Minimal styling, leaving it up to the theme to take care of the looks.
- Neutral styling, with some borders, numbering and dotted lines.

The plugin provides two blocks for the blocks editor that can be found in the Widgets section.
- Sign-up List Entries - displays the current entries.
- Sign-up List Form - displays the sign-up form for new entries.

There are corresponding shortcodes for web sites that do not use the block editor (yet). 
The shortcodes are:
- [sul_entries ] - displays the current entries.
- [sul_sign_up ] - displays the sign-up form for new entries.

The plugin supports the built-in privacy tools of WordPress:
- Export Personal Data
- Erase Personal Data

== Frequently Asked Questions ==

= Where can I find the admin screens ? =

There is a seperate menu item called *Sign-up List* in the main admin menu on the left.

= Why is there a maximum of 200 entries?  =

This is driven by usability, because there is no pagination or search function in the front-end yet.
If people start using this plugin, I will add these features and increase the limit.

= Why does the plugin support only one list? =

First of all, I wanted to keep it simple. There are other plugins that offer far more complex features for sign-up lists. However, if people start using this plugin and need multiple concurrent lists, I will add support for that.

= How can I re-use the list once the sign-up has been completed? =

You can reset the list from the admin interface to start from scratch. You may want to export the current list entries first before you do that.

= Does the plugin send out emails automatically =

No, not yet. You will have to follow-up manually on people that signed-up. You can export the entries to a spreadsheet to make this easier. I will add emails features if there is demand for it.

= Are duplicate email addresses allowed? =

That is an option in the admin interface, so you can choose to allow or disallow duplicates.

= Are there translations available? =

I am Dutch, so I have created a Dutch translation. Please contact me if you can provide support for additional language translations.

= Any credits due? =
Yes, there are credits due.
Credits for the header Photo by [Kelly Sikkema](https://unsplash.com/@kellysikkema?utm_source=unsplash&utm_medium=referral&utm_content=creditCopyText) on [Unsplash](https://unsplash.com/backgrounds/art/paper?utm_source=unsplash&utm_medium=referral&utm_content=creditCopyText).
Checklist by Icons Field from [Noun Project](https://thenounproject.com/browse/icons/term/checklist/)
  

== Screenshots ==

1. Front-end display of an open sign-up list and form in default theme Twenty Twenty-Three. 
2. Block editor with the blocks Sign-up List Entries and Sign-up List Form.
3. Admin interface for list entries, with CSV export and reset feature.
4. Sign-up options.
5. Display options.

== Changelog ==

= 1.0.0 =
* Initial release.
