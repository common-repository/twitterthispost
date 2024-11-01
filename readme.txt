=== TwitterThisPost ===
Contributors: naden
Donate link: http://www.naden.de/blog/donate/
Tags: twitter, wordpress, blog, widget, widgets, plugin, twitthis, twitthat, button, post, tweet, tweets
Requires at least: 2.0
Tested up to: 2.71
Stable tag: 0.3

TwitterThisPost display a post to twitter link below you post and comes w/ very fine graned configuration options.

== Description ==

TwitterThisPost display a post to twitter link below you post and comes w/ very fine graned configuration options.

As as clue, TwitterThisPost does not need to make external api calls to shrink your permalink. It's all done on the fly thanks to the great i2h.de [shorturl](http://i2h.de "Shorturl") Service. Check out the [api](http://i2h.de/api "Api") and don't forget to follow me on [Twitter](http://twitter.com/nadende "Twitter").

== Installation ==

1. Unpack the zipfile twitterthispost-X.y.zip
1. Upload folder `twitterthispost` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. configure the plugin via the admin page to fit your needs
1. Place `<?php if( function_exists( 'twitter_this_post' ) ) twitter_this_post(); ?>` in your template.
== Frequently Asked Questions ==

= Does the plugin supports itenti.ca =

Nope, not atm.

== Change Log ==
* v0.3 27.04.2010  minor xhtml fix
* v0.2 26.06.2009  removed www. from www.i2h.de to be even shorter ;)
* v0.1 18.07.2008  initial release

== Short Example ==

* Place `<?php if( function_exists( 'twitter_this_post' ) ) twitter_this_post(); ?>` in your template.

Check out the plugin page at [TwitterThisPost](http://www.naden.de/blog/twitter-wordpress "TwitterThisPost")

