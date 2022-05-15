# Schedule_XH

Schedule_XH facilitates the coordination of meetings for members
(registered through [Register_XH](https://github.com/cmb69/register_xh)
or [Memberpages](https://github.com/cmsimple-xh/memberpages)
of your CMSimple_XH site. It is similar to the Doodle service, but as the
appointment dates have no semantic meaning to Schedule_XH, it can be used for
other votings as well.

- [Requirements](#requirements)
- [Download](#download)
- [Installation](#installation)
- [Settings](#settings)
- [Usage](#usage)
- [Troubleshooting](#troubleshooting)
- [License](#license)
- [Credits](#credits)

## Requirements

Schedule_XH is a plugin for CMSimple_XH.
It requires CMSimple_XH ≥ 1.7.0 and PHP ≥ 7.1.0.

## Download

The [lastest release](https://github.com/cmb69/schedule_xh/releases/latest)
is available for download on Github.

## Installation

The installation is done as with many other CMSimple\_XH plugins. See the
[CMSimple\_XH wiki](https://wiki.cmsimple-xh.org/doku.php/installation#plugins)
for further details.

1. **Backup the data on your server.**
1. Unzip the distribution on your computer.
1. Upload the whole directory `schedule/` to your server into the `plugins/` directory of CMSimple_XH.
1. Set write permissions to the subdirectories `config/`, `css/`, `languages/`
   and the data directory of the plugin.
1. Switch to `Schedule` in the back-end to check if all requirements are
   fulfilled.

Note that the data files of Schedule_XH could be accessed directly by calling
their URL. A `.htaccess` file to prohibit this is already contained in the default
data folder. For other servers or for custom data folders you have to take care
for yourself, that the data are protected from non authorized access.

## Settings

The configuration of the plugin is done as with many other CMSimple_XH plugins in
the back-end of the website. Select `Plugins` → `Schedule`.

You can change the default settings of Schedule_XH under `Config`. Hints for
the options will be displayed when hovering over the help icons with your
mouse.

Localization is done under `Language`. You can translate the character
strings to your own language if there is no appropriate language file available,
or customize them according to your needs.

The look of Schedule_XH can be customized under `Stylesheet`.

## Usage

To set up a planning/voting on a CMSimple_XH page, use the following plugin
call:

    {{{schedule('%NAME%', %SHOW_TOTALS%, %READ_ONLY%, %MULTI%, '%OPTION_1%', '%OPTION_2%', '%OPTION_N%')}}}

The parameters have the following meaning:

- `%NAME%`:
  The name of the event or other voting. This is used as basename of the CSV
  file in which the results are stored, so it may contain lowercase letters (`a`-`z`),
  digits (`0`-`9`) and dashes (`-`) only.
- `%SHOW_TOTALS%`:
  Whether the totals should be displayed: `true` (means yes) or
  `false` (means no). It is important to write this parameter without
  quotes. If you want to use the default value from the plugin configuration, you
  can omit this parameter, if you also omit the `%READ_ONLY%` and the `%MULTI%`
  parameter.
- `%READ_ONLY%`:
  Whether the planner should be read only: `true` (means yes) or
  `false` (means no). It is important to write this parameter without
  quotes. If you want to use the default value from the plugin configuration, you
  can omit this parameter, if you also omit the `%MULTI%` parameter.
- `%MULTI%`:
  Whether multiple options may be chosen for a single planner: `true`
  (means yes) or `false` (means no). It is important to write this parameter
  without quotes. If you want to use the default value from the plugin
  configuration, simply omit this parameter.
- `%OPTION_X%`:
  The name of the option, which will be displayed as heading of the respective
  table column. This is typically a date, but as it has no semantic meaning to
  Schedule_XH, it can actually be any string. Basically you can have as many
  options as you like.

It is not necessary to restrict access to this page to members; if visitors
of your site are not logged in, they will just see the current results, without
being able to vote. So you can even place the planner in the template:

    <?=schedule('%NAME%', %SHOW_TOTALS%, %READ_ONLY%, %MULTI%, '%OPTION_1%', '%OPTION_2%', '%OPTION_N%');?>

However, if you want to restrict the voting to a certain user group or
access level, you have to place the planner on a page, which can only be
accessed by members with the respective authorization.

### Examples

To schedule a Christmas staff party:

    {{{schedule('christmas', 'Dec. 18th', 'Dec. 19th', 'Dec. 22nd')}}}

![Screenshot of the voting widget](https://raw.githubusercontent.com/cmb69/schedule_xh/master/help/schedule.jpg)

After the voting for the party date, you want to keep the planer, so
everybody can see the results. In this case you want to set it read only. To
use the `%READ_ONLY%` parameter, you also have to specify the `%SHOW_TOTALS%`
parameter:

    {{{schedule('christmas', false, true, 'Dec. 18th', 'Dec. 19th', 'Dec. 22nd')}}}

To set up a voting about the color of the new team shirts, that displays the
voting totals below the table regardless of the respective configuration
option:

    {{{schedule('shirts', true, 'red', 'green', 'blue')}}}

### Notes

- You can have as many planners as you like; just give them different
  names.
- You can place the same planner on different pages; just use the same
  name.
- Your members can change their votes as often as they like.
- You can change the options even after some members have already voted. As
  the votes are assigned to the name of the option (not by position), this should
  work reasonably well.

## Troubleshooting

Report bugs and ask for support either on [Github](https://github.com/cmb69/schedule_xh/issues)
or in the [CMSimple_XH Forum](https://cmsimpleforum.com/).

## License

Schedule_XH is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Schedule_XH is distributed in the hope that it will be useful,
but *without any warranty*; without even the implied warranty of
*merchantibility* or *fitness for a particular purpose*. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Schedule_XH.  If not, see <https://www.gnu.org/licenses/>.

Copyright 2012-2022 Christoph M. Becker

## Credits

The plugin was inspired by *Roymcavoy*.

The plugin icon is designed by [schollidesign](https://www.deviantart.com/schollidesign).
Many thanks for publishing this icon under GPL.

Many thanks to the community at the [CMSimple_XH Forum](https://www.cmsimpleforum.com/)
for tips, suggestions and testing.
Particularly I want to thank *Ele* for beta testing and suggesting improvements.

And last but not least many thanks to [Peter Harteg](https://www.harteg.dk/),
the “father” of CMSimple, and all developers of [CMSimple_XH](https://www.cmsimple-xh.org/)
without whom this amazing CMS would not exist.
