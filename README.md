# WP Password Redirect

A Wordpress plugin to redirect users based on an entered password.

Forked from https://thecodecave.com/plugins/smart-passworded-pages/ due to inactivity, and my need to expand the options for target pages.

The original plugin looks for 'descendent pages' from the page where the [smartwppages] shortcode is entered.  

I needed this to be more flexible, and actually search through custom post types - e.g. WP Download manager 'download packages', as I need to provide a concierge service whereby....

A central 'download page' will receive a user password, and then redirect to the first 'download package' if finds with the custom post type 'wpdmpro' that has the entered password.  The easiest way to implement this, is to continue the existing style where shortcode attributes are used as data input.

Look in the attached documentation for current details of the plugin.

If you're interested in contributing, then look [here](CONTRIBUTING).   
