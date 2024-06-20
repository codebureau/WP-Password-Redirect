# WP PW Pages and Posts

This plugin is forked from the excellent and simple [Smart Passworded Pages](https://wordpress.org/plugins/smart-passworded-pages/) by Brian Layman.  All original options of that plugin are still available, with the key difference of the shortcode name.  Find out more in [Usage](usage).

## Purpose
The purpose of this plugin is to act as a 'concierge redirect' service, accepting a password and redirecting the user to the relevant page or post.  This avoids the need to create specific links to pages/posts where you have a number of potential redirect targets.

## Features

The plugin inserts a password input form via a shortcode, in order to search relevant 'child' pages that match that password - and subsequently redirect to that page.  The child pages can in turn link to other pages protected with the same password and the password will not need to be re-entered

The shortcode allows an option to search posts rather than child pages.  The posttype can also be specified, to target posts created by other popular plugins. 

This plugin doesn't add the ability to add passwords to pages.  WordPress has that built in.  On the right hand side of the page editing screen in WordPress, you can change the visibility to Password protected and enter in a password. If you are unfamiliar with using passwords in WordPress, you might want to read this page first:  http://codex.wordpress.org/Using_Password_Protection

This plugin does make the password handling smarter and enhances it so that you can enter one password on a parent page and gain access to all the children pages using that password.  If you don't know what children pages or sub-pages are, you might want to read about it here:  http://codex.wordpress.org/Pages#Creating_Pages


## Contents

- [Installation](installation)
- [Usage](usage)
