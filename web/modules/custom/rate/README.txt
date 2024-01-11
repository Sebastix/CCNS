INTRODUCTION
------------
This module provides flexible voting widgets for nodes and comments.
Administrators can add multiple widgets and define an unlimited number of
buttons.

Current functionality
 - Single rate widget on multiple entities possible
 - Multiple rate widgets on a single entity possible
 - Result functions per widget/entity combination 
   and entity summary (multiple widgets)
 - Up/down (number up/down, thumbs up/down, yes/no) voting
 - Emotion rate widget
 - Fivestar voting
 - CSS, JS and result templates
 - Voting Results tab for nodes
 - Voting via AJAX only
 - Undo votes
 - Voting on comments and other entity types
 - Widget label, description and result summary
 - Position of label, description, result summary (relative to widget or hidden)
 - Disable widget (Read-only widget) based on permission, rollover, user setting
 - Vote rollover for registered and anonymous users
 - Voting deadline field in the voted entity
 - Rate widgets in views (selectable ID of entity,customizable display)
 - Rate API with hooks for deeper programmatical customization

Limitations:
 - No migration for switching from one widget type to another
 - No migration from 8.x-1.x - manual updates on database level necessary

CONTENTS
--------
1. Installation
2. Global configuration (bot detection)
3. Permissions
4. Widget configuration
4.1. Widget entity configuration
4.2. Options
5. Voting results
6. Views integration
7. Rate deadline (close voting on a specified date)
8. Rate API Hooks
9. Migration from previous versions

1. Installation
--------------------------------------------------------------------------------
Before installing Rate, you need VotingAPI. If not already installed, download
the latest stable version at http://drupal.org/project/votingapi
Please follow the readme file provided by VotingAPI on how to install.

Use composer to install Rate - see the instructions on the Rate release download
page. Enable Rate afterwards (on admin/modules).

Required modules:
* Voting API (https://www.drupal.org/project/votingapi)
  Rate uses VotingAPI contributed module to store and retrieve rate votes.
* Comments (https://www.drupal.org/docs/8/core/modules/comment)
  The Comments core module is required if you intend to enable comments rating.
* Datetime (https://www.drupal.org/docs/8/core/modules/datetime)
  The Datetime core module is a requirement for the Rate deadline field.

Optional modules:
* Chart (https://www.drupal.org/project/charts)
  To view the charts in the vote results tab, you also need to install the
  "charts" module. Refer to the Charts documentation to set up result charts.

2. Global configuration (bot detection)
--------------------------------------------------------------------------------
After installation, the global rate settings configuration page will be 
available at /admin/config/search/votingapi/rate. 
This page displays the botdetection settings.

The Rate module is able to detect bots in three ways:

* Based on user agent string
* Using an threshold. The IP-address is blocked when there are more votes from
  the same IP within a given time than the threshold. There are thresholds for
  1 minute and 1 hour.
* Lookup the IP-address in the BotScout.com database. This requires you to
  obtain an API-key.

The thresholds and API-key can be configured at the settings form found on
admin/structure/rate/settings. The default thresholds are 25 and 250. They are
too high for many sites, but you should make sure that no real users get
blocked. On the other hand, lower thresholds will identify more bots and will
identify them faster. A value of 10 / 50 is a better setting for most sites.

Bad user agents cannot be configured via the admin at this moment. You can add
bad strings in the 'rate_bot_agent' table. Percent signs ("%") can be used as
wildcards. A string can be for example "%GoogleBot%" or just "%bot%". Patterns
are case insensitive. The id field is for identification and has no meaning.

3. Permissions
--------------------------------------------------------------------------------
You need to set permissions for the Rate module at /admin/people/permissions.

For administering Rate - creating, updating or deleting widgets, a user will
need the "Administer Rate options" permission. CAUTION: assign this permission 
to administrator roles only.

For each entity bundle, which has a rate widget attached, there will be a
separate record, such as "Can vote on ENTITY_TYPE of ENTITY_BUNDLE", for 
example "Can vote on node type of article". Set the permissions accordingly.

If you want users other than admin to access the voting results page, you need
to give them the "View rate result page" permission on admin/user/permissions.

4. Widget configuration
--------------------------------------------------------------------------------
Since Rate version 8.2. all rate widgets are stored separately in the database
as configuration entities. To configure the rate widgets, go to
/admin/structure/rate_widgets.

To add a new rate widget, click on the button "+Add rate widget".

A list of existing rate widgets is shown at /admin/structure/rate_widgets.
To modify an existing rate widget click on the button "Edit" next to the
table entry you want to modify.

4.1. Widget entity configuration
--------------------------------
The elements on the widget configuration form are explained in this paragraph.
Note that some elements may not be available depending on the widget type you
use, these are "Value type", "Options" and "Translate options".

* Name
  The title is only used in the admin section. Use a recognizable name.

* Machine name
  Name used for technical purposes. You may only contain alphanumeric characters
  and underscores (generated automatically).

* Template
  Select the type of widget to create. The following widget types are provided
  by the rate module:
  * Custom
  * Fivestar
  * Number Up / Down
  * Emotion
  * Thumbs Up
  * Thumbs Up / Down
  * Yes / No
  This will impact the look of the widget.

* Value type
  This determines how vote results are totaled. VotingAPI supports three value
  types by default: 'Percentage' votes are averaged, 'Points' votes are summed
  up, and 'Options' votes get a count of votes cast for each specific option.
  Typical usages are:
  * Thumbs up / down: use points
  * Bad / mediocre / good: use percentage
  * Makes me funny / boring / mad / angry: use options

* Options (see §4.2 for more information).
  These are the options displayed to the user. Each option has a value, a label
  and a class.

* Entities
  Check the entity types on which a rate widget should be available. There are
  separate columns for nodes and comments in this table.

* Voting settings
  * Use a vote deadline
    Enables a deadline date field on the respective node. 
    If deadline is set and date passed, the widget will be shown as disabled.

  * Anonymous (Registered user) vote rollover
    The amount of time that must pass before two votes from the same computer 
    are considered unique. Set the rollover to a time period. Setting this to
    'Never' will allow only one vote. Setting this to 'Immediately' will allow
    the user to cast votes with every click. 'Votingapi setting' will inherit 
    the settings which are configured at /admin/config/search/votingapi. 

* Display settings
  Configures the content, position and class of a rate widget label and
  description filds. Here a widget can be set to be 'read-only'.

* Results
  Configures the content and position of the result summary.

  To customize the results summary template you need to copy the file
  rate\templates\rate-widgets-summary.html.twig to your subtheme and
  modify it accordingly.

4.2. Options
------------
Options are the "buttons" displayed in the widget. These can be visually
different, depending on the theming. Options are generated as radio form 
elements by default.

Each option has a value, a label and a class. Only the label is visible for the
user, but the value is what he actually votes for when clicking the button.

Values have to be configured according to the following rules:
* Values must be integers (may be negative). Thus '1', '2', '0', '-3' are all
  right, but '2.3' is wrong.
* Values must be unique across all options within the same widget.

Which value you should use depends on the value type setting. When using points,
these are the points which will be added when clicking that button. So "thumbs
up" must have the value '1', "thumbs down" the value '-1' and "neutral" '0'. For
'Percentage' you have to use whole numbers between 0 and 100. When using
'Options', you may use any number as long as they are unique. It doesn't have to
make sense as they are only used for storage.

To add an option, click on the button "Add another option". 
To delete an option - delete its values in the fields value, label and class and
save the rate widget.

5. Voting results
--------------------------------------------------------------------------------
Voting results are available on the voting results page. You can get there by
clicking the "Node votes" tab on the node page. Note that this tab is hidden
if the node does not have any rate widgets or if you do not have the
"view rate results" permission.

The voting results page is only available for nodes.
In order to enable the voting results for other entity types, e.g. users, groups
etc., you will need to create YOURMODULE.links.task.yml, YOURMODULE.routing.yml
and a custom controller in your custom module. See the node implementation in 
rate as a starting point (rate.node_results_page).

6. Views integration
--------------------------------------------------------------------------------
Add a field of the type "Rate widget" to your view. If your view has 
relationships defined, select the correct one to attach the field to.
In the field configuration form, you have the following possibilities:

* Which field column holds the entity ID?
  Select the ID of the entity you would like to vote on. This entity has to have
  the rate widget widget attached to it.
  You can attach the entity id to your and then hide teh field.
  Through this you can vote on any entity, as long as you can show it in your
  view.

* Some entities have multiple widgets attached...
  If the entity has multiple widgets attached - select the one to be shown in
  this field.
  If you want several widgets to be shown - add another Rate widget field to
  your view.

* Show widget
  * Full - will show it as configured.
  * Summary - will show only the widget summary (enable it in the configuration)
  * Read only - show the widget as configured, but block the user from voting.

* Override rate widget display options
  With this setting you can hide the label, description and/or the result
  summary.
  
CAUTION: filtering or sorting based on the Rate widget field is not possible.
In order to filter or sort on the voting results, add a relation to the
Voting API results and sort/filter on them.

7. Rate deadline (close voting on a specified date)
--------------------------------------------------------------------------------
This option allows you to close voting on a specified date. 
When adding or editing a rate widget, checking 'Use vote deadline' in 'Voting 
settings' will add a date field to each entity this rate widget is attached to.

The deadline can be then set on each entity individually (e.g. Article 1 and 
Article 2). To do so, open the entity in question, go to its edit form and set
the deaedline. By default, the deadline field is not showing on the view display
of the entity, but you can change this.

If the deadline is set for an entity and the date is already passed, the rate
widget will be disabled. Additional variables - disabled and deadline_disabled
are passed to rate-widgets-summary.html.twig, so you can customize the result
summary accordingly.

8. Rate API Hooks
--------------------------------------------------------------------------------
Hooks for use in custom or contrib modules are documented in rate.api.php.

* hook_rate_vote_data_alter
  Alter the vote data before a vote is created in the database.

* hook_rate_widget_options_alter
  Override the options (value, label, class) of a rate widget. It is called 
  before the rate widget is being rendered.

* hook_rate_templates
  Define additional or modify existing templates for rate widgets.

* hook_rate_can_vote
  Provide a way for custom checks of the user's ability to vote.


9. Migration from previous versions
--------------------------------------------------------------------------------
Migration of Rate from preoviou

* Upgrading from 7.x
  Migration from Rate 7.x. is supported, see the Migration API documentation for
  further details: https://www.drupal.org/node/2127611.

* Upgrading from 8.x-1.x
  There is no direct upgrade path from 8.x-1.x to 8.x-2.x, as both versions use 
  completely different concepts to create and store the rate widgets. 
  You will have to recreate your custom rate widgets manually in 8.x-2.x.
  Assuming your 'votingapi_vote' table already stores your votes, you will also
  have to manually adjust the the column 'rate_widget' with the machine name of
  the rate widget you assigned to the corresponding entity bundle.
