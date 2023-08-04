=== Ultimate Member - Groups ===
Author URI: https://ultimatemember.com
Plugin URI: https://ultimatemember.com/extensions/groups/
Contributors: ultimatemember, champsupertramp, nsinelnikov
Tags: groups, group tags, user-profile, user-registration
Requires at least: 5.5
Tested up to: 6.2
Stable tag: 2.4.0
License: GNU Version 2 or Any Later Version
License URI: http://www.gnu.org/licenses/gpl-3.0.txt
Requires UM core at least: 2.6.9

Allow users to create and join groups around shared topics, interests etc.

== Description ==

Allow users to create and join groups around shared topics, interests etc.

= Features of the plugin include: =

* Allows users to create groups
* Allow users to join groups
* Group moderation settings
* Users can post in groups
* Users can select group privacy setting (private, hidden, public)
* Users can see their groups from their profile or any page using shortcode
* Categories and tags available for groups
* Decide who can invite members to a group (Group admin, moderators or all members)
* Post moderation available for group posts
* Add group image

Read about all the plugin's features at [Ultimate Member - Groups](https://ultimatemember.com/extensions/groups/)

= Documentation & Support =

Got a problem or need help with Ultimate Member? Head over to our [documentation](https://docs.ultimatemember.com/category/1426-groups) and perform a search of the knowledge base. If you can’t find a solution to your issue then you can create a topic on the [support forum](https://wordpress.org/support/plugin/ultimate-member).

== Installation ==

1. Activate the plugin
2. That's it. Go to Ultimate Member > Settings > Extensions > Groups to customize plugin options
3. For more details, please visit the official [Documentation](https://docs.ultimatemember.com/category/1426-groups) page.

== Changelog ==

= 2.4.0: August 1, 2023 =

* Added: Registration form setting to assign user after registration to the groups
* Fixed: `date_joined` data and convert it to `date_joined_gmt`
* Fixed: Posting hidden group activity to the UM:Social Activity wall
* Tweak: Using `date_joined_gmt` column and data for getting proper joined date. It doesn't depend on WordPress timezone settings

= 2.3.3: June 29, 2023 =

* Fixed: Join/Leave group button
* Fixed: Removed `extract()` function to increase security

= 2.3.2: June 9, 2023 =

* Fixed: PHP warnings
* Fixed: Groups privacy saving in some cases
* Fixed: Groups Activity posting new activity post

= 2.3.1: May 30, 2023 =

* Added: A new filter to support more image types such as WebP
* Fixed: Hide the Join Group button for members who cannot join a group
* Fixed: hashtagit function
* Fixed: Groups links in subtabs
* Fixed: PHP warnings
* Tweak: Template overwrite versioning
* Tweak: changed `input_filter` to WordPress native sanitize

= 2.3.0: August 17, 2022 =

* Fixed: Content replace with Avada theme (Group single page)

= 2.2.9: February 9, 2022 =

* Fixed: Extension settings structure

= 2.2.8: December 20, 2021 =

* Fixed: Applying the category filter and after that click on "Load more" button.

= 2.2.7: September 22, 2021 =

* Added: the filter hook 'um_groups_single_the_title'
* Fixed: The text-type filter fields displaying on the Invite tab
* Fixed: The page title on the single group page
* Fixed: Getting group post via the hash (there could be a match with another post type)
* Fixed: Translations typo

= 2.2.6: July 20, 2021 =

* Fixed: Single group's `the_title` hook
* Tweak: WP5.8 widgets screen compatibility

= 2.2.5: June 24, 2021 =

* Added: Enhancements in the integration of Groups + Profile Completeness
* Added: jQuery v3 compatibility (removed deprecated functions from jQuery.migrate)
* Added: Text to display if user needs to login to see group activity
* Added: Real-time notification 'Groups - New post'
* Added: Real-time notification 'Groups - New comment'
* Added: Hooks for integration with CPT and taxonomies
* Added: The restriction notice for the 3rd-party integration
* Added: Reported posts tab and notice in the group discussion wall
* Added a new shortcode [ultimatemember_group_users_invite_list]. The list of the invitations for current user
* Fixed: Shortcodes' data displaying privacy
* Fixed: Redirects on the profile tabs
* Fixed: [ultimatemember_group_invite_list] shortcode
* Fixed: Pagination in the members template
* Fixed: Email notification placeholders and updated templates
* Fixed: Restored post slug line
* Fixed: Changed default group CPT slug to avoid the conflicts
* Fixed: Privacy checking for "public for role" groups
* Fixed: AJAX actions for the group's members
* Fixed: Invitation filters bar the "clear all" link
* Fixed: Join Administrator as the first member to the group
* Fixed: Group creator name displaying
* Fixed: Administrator capabilities for deleting posts from the group
* Fixed: Groups list pagination and displaying groups for not logged in users
* Fixed: Deleting group avatar after group editing
* Fixed: Displaying hidden group in a user profile
* Fixed: Sending email notifications only after moderation
* Fixed: Avatar displaying on the real-time notifications feed
* Fixed: Displaying groups for the banned users
* Fixed: Displaying page title on the singular group page
* Fixed: Notice about not-reviewed group posts for moderators and admins
* Fixed: Displaying rejected users in the Join Requests tab
* Fixed: Form labels and some typo
* Deprecated: group_id attribute for the [ultimatemember_group_new] shortcode

= 2.2.4: December 16, 2020 =

* Added: Show the image upload error
* Added: Improvements for single/list groups templates (Show author, categories, tags. Upload avatar.)
* Fixed: User suggestions
* Fixed: Settings have to be applied after plugins loaded
* Fixed: Enqueue scripts
* Fixed: PHP notices
* Fixed: Show only friends to the user invites directory
* Fixed: Filters names
* Fixed: Typo bug fix and add action hooks
* Fixed: Remove email notifications after post updates
* Fixed: Don't display the form 'Write Post' if the user is not a member of a group
* Tweak: A new icon for the profile tab 'Groups'

= 2.2.3: August 24, 2020 =

* Fixed: Using a deprecated variable $.browser in auto-resize library
* Fixed: Count notifier of groups on the someone else's profile
* Fixed: Invites actions on the someone else's profile
* Fixed: Action buttons in a groups tab on the someone else's profile

= 2.2.2: August 11, 2020 =

* Added: Settings for CPT & taxonomies slugs
* Added: Role setting for 'Disable groups creation'
* Added: Email placeholders {group_url_postid} {group_url_commentid} {post_url} {comment_url}
* Added: Scroll to the hashed post or comment
* Added: Groups sorting by last activity
* Added: *.pot translations file
* Added: Sorting by last activity in groups shortcode "activity_asc"/"activity_desc"
* Changed: Email notification templates
* Fixed: Invite tabs filter
* Fixed: Resetting user after fetch
* Fixed: Email notification "Groups - New post" and "Groups - New comment"
* Fixed: Account settings 'Notify me when someone posts on group' and 'Notify me when someone comments on group'
* Fixed: PHP notice on the group discussion wall
* Fixed: fatal error with namespace declaration
* Fixed: category filter
* Fixed: correct template structure for overriding
* Fixed: new comment and post notification email issue
* Fixed: a reply after load more on the group wall
* Fixed: broken SQL statement
* Fixed: invite link in the email for member if a group is hidden
* Fixed: JS conflict with Ultimate Member - Social Activity extension
* Fixed: The Real-time Notifications if a group is hidden
* Tweak: apply_shortcodes() function support

= 2.2.1: April 1, 2020 =

* Added: GDPR complicity, groups' discussions posts export
* Fixed: PHP notice for the "public for role" group when not logged in user see it
* Fixed: Shortcode arguments and search bar at groups list
* Fixed: Tags validation on create group
* Fixed: Editing group-wall posts
* Fixed: Group-wall posting and comments
* Tweak: Optimized webnotifications integration

= 2.2.0: January 23, 2020 =

* Added: Ability to sort filters on Invites tab
* Added: Text-type filters for Invites tab
* Added: Performance fixes to decrease the number of mySQL queries
* Fixed: Join to group button from another user Profile
* Fixed: CSS issues
* Fixed: User action links
* Changed: Account notifications layout

= 2.1.9: November 18, 2019 =

* Fixed: Groups table creation
* Fixed: Redirect from "Join Requests" to "Login" page for logged out users

= 2.1.8: November 13, 2019 =

* Fixed: Group members list
* Fixed: Group invites list

= 2.1.7: November 11, 2019 =

* Added: Rewritable templates
* Added: Invites notifier in Profile menu
* Added: Shortcode [ultimatemember_group_comments]. This shortcode returns block, that contains posts with the latest comments.
* Added: Shortcode [ultimatemember_group_members]. This shortcode returns block, that contains group members list.
* Fixed: Database column `date_joined`
* Fixed: Load more comments
* Fixed: Shortcode [ultimatemember_group_discussion_activity] can be used on any custom page
* Fixed: Shortcode [ultimatemember_group_discussion_wall] can be used on any custom page
* Tweak: Changed all user groups tabs to Member Directory class

= 2.1.6: August 19, 2019 =

* Added: Email templates for groups's comment/post
* Fixed: Email templates placeholders and default content

= 2.1.5: July 16, 2019 =

* Fixed: Profiles Tab Privacy
* Fixed: Uninstall process
* Fixed: Approve members to groups

= 2.1.4: April 19, 2019 =

* Added: Invites groups list

= 2.1.3: April 4, 2019 =

* Fixed: Wordpress.com compatibility

= 2.1.2: March 29, 2019 =

* Added: Filter to Invites tab
* Added: Danish translation
* Fixed: Some responsive styles
* Fixed: Loading textdomain

= 2.1.1: February 8, 2019 =

* Fixed: Upload Dir handler
* Fixed: German translates
* Fixed: Default value for group selectbox

= 2.1.0: January 21, 2019 =

* Fixed: Show more group members
* Fixed: Small PHP notices
* Fixed: CSS for templates

= 2.0.9: January 7, 2019 =

* Fixed: Approve group users via front end
* Fixed: JS enqueue

= 2.0.8: December 5, 2018 =

* Fixed: JS issues on Add/Edit group activity post and like

= 2.0.7: December 4, 2018 =

* Fixed: Fatal Error on some installs

= 2.0.6: December 3, 2018 =

* Fixed: Group description publishing
* Fixed: Group discussion likes

= 2.0.5: November 26, 2018 =

* Fixed: Displaying hidden groups at the groups list

= 2.0.4: November 15, 2018 =

* Fixed: Hide group discussions and members for non-logged in users
* Fixed: See more button not working issue
* Fixed: Delete group member on user delete action
* Fixed: Remove deprecated functions

= 2.0.3: November 12, 2018 =

* Fixed: is image functions
* Fixed: style for small devices
* Fixed: avatars in add member to the group

= 2.0.2: November 3, 2018 =

* Fixed: Scripts Enqueue
* Fixed: PHP notice on Invites tab

= 2.0.1: October 12, 2018 =

* Fixed: Yoast SEO compatibility with UM:Groups templates

= 2.0: October 1, 2018 =

* Initial Release
