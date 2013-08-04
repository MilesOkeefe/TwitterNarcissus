Twitter Narcissus
=================

A quick, experimental website for the self-inclined.
Of those who have signed in on this page, the user with the highest follower count is displayed largely across the page.

[Twitter narcissus](http://twitternarciss.us/) is about whoever is tactless enough to login yet has the followers to login.

There really is no point. 


PS: Twitter inc, if you feel this infringes on your trademark, contact me and I'll take it down ASAP.


How to Setup/Run
================

1. Register your application on the Twitter Applications page.

3. Tick the allow login checkbox.

3. Generate an API token.

4. Enter your API details in the config area at the top of the index.php file.

5. That's it.

	There's no database or any dependencies besides PHP and file_put_contents() being enabled, which normally isn't an issue.

How it Works
============
On every page load, the background and favicon are static, while the username and follower count are dynamically retrieved from a local text file which servers as a single key:value database.

When a user logs in, his/her follower count is checked with the Twitter API. If it is higher than the follower count of the last highest record (retrieved from a local text file), then the new highest follower user's avatar url is retrieved from the Twitter API and downloaded. Additionally, the text file which serves as a database is updated.


