GSnapUp
=======

Google Cloud Platform Snapshot Backup System

The [Google Cloud Platform][GCP] (GCP) provides a [Compute Engine][GCP-Compute]
service for creating and maintaining robust, scalable, and high-performance
[virtual machine instances][GCP-Instances].  A provided feature allows for any
instance disk to have a [delta-based snapshot][GCP-Create-Snapshots] generated,
even while the instance they are attached to is actively running.  GCP stores
multiple copies of each snapshot redundantly across multiple locations with
automatic checksums to ensure the integrity of the data, making this a useful
feature for periodic backups.  Even with all this capability, due to its manual
process, it falls short of a proper backup system.

The `GSnapUp` utility provides the missing scheduling overlay for the GCP
snapshot functionality; to produce a rounded feature-set, for a solid backup
system.  Setup is as simple as installing the utility on a system (in or with
access to) the GCP, configuring the instances and disks to maintain, and
configure a system CRON task to keep its hart beating.


Installation
------------


### Prerequisites

Prior to utilizing the `GSnapUp` utility, the system it's running on requires
the [GCP SDK][GCP-SDK] to be [installed][GCP-SDK-Install] and
[initialized][GCP-SDK-Initialize], as well as [PHP 5.6+][PHP] to be available.

After installing the `gcloud` utility, run its `init` command (if you are
running the command on a remote system add the `--console-only` option):
```bash
$ gcloud init --console-only
```


Commands
--------

The `GSnapUp` utility provides several commands for creating and maintaining the
configuration file used to interact with the GCP instance disks, as well as
running the snapshots on said disks.

Command               | Description
--------------------- | ---------------------------------------------
`init`                | Initialize configuration
`instance:add`        | Add instance to configuration
`instance:available`  | List available GCloud instances
`instance:list`       | List configured instances
`instance:update`     | Update instance in configuration
`scheduled`           | Run scheduled GCloud snapshot backups

A detailed output of all the commands can be obtained by running:
```bash
$ gsnapup list
```

Help for each command may be obtained by using the `help` command:
```bash
$ gsnapup help init
```


Configuration
-------------

The configuration consists of the `main`, `instances`, and `disks` **sections**,
each containing a series of **keys**.  The `main` section (being the root of the
configuration) contains an `instances` **key** housing a list of all the
configured GCP *instances*.  Each of the `instance`s in the configuration
contains a `disks` **key** housing a list of all the configured *disks* for that
GCP *instance*.

Each *instance* configured in the `instances` list uses a token **key** which is
used to reference that `instance` when dealing with `GSnapUp`.  The *disks*
configured in the `disks` list also utilizes a token **key**, the same way the
`instances` list does.  These token **keys** make it friendlier to interact with
this utility, providing a saner naming convention than what is common with the
GCP *instance* and *disk* naming.

Example:
```json
{

    ...

    "instances": {
        "instanceToken": {

            ...

            "disks": {
                "diskToken": {

                    ...

                },

                ...

            }
        },

        ...

    }
}
```

Most of the non-section **keys** can be used in any of the sections (providing a
cascading-style configuration) which allows for fine-grain control of the
utility.  The values set in a `disk` sub-section will override the values set in
its parent `instance` sub-section, which override the values set in the main
section.  This style of configuration provides the most flexible implementation,
and allows for a verity of use-cases.

The simplest example would be the `enabled` **key**.  If the `main` sections
`enabled` value is set to '`true`', an entire `instance` may be disabled by
setting that instances `enabled` value to '`false`', or a single disk may be
disabled by setting that disks `enabled` value to '`false`'.  Additionally, if
the `main` sections `enabled` is set to '`true`', an instance has several disks
but its `enabled` is set to '`false`', any of its disks sub-sections could have
their `enabled` set to '`true`' and that disk would be considered enabled.


### Sections

#### Main

Sections | Main      | Instances | Disks
-------- | --------- | --------- | ---------
Used in  | No        | No        | No

This is the root section of the configuration, all other sections and keys
reside inside this section.

#### Instances

Sections | Main      | Instances | Disks
-------- | --------- | --------- | ---------
Used in  | Yes       | No        | No

This section exists directly inside the `main` section and may contain one or
more `instance` sub-sections containing settings representing a GCP instance.

#### Disks

Sections | Main      | Instances | Disks
-------- | --------- | --------- | ---------
Used in  | No        | Yes       | No

This section exists directly inside an `instances` sub-section and may contain
one or more `disk` sub-sections containing settings representing a GCP disk.


### Keys

The **keys** that aren't specifically for sections or sub-sections (as stated
previously) can be used in any section, unless stated otherwise.  More details
about each **key** is detailed further down below, but for a quick reference the
following is a table of the different **key** names and the sections they are
used in:

Key               | Main      | Instances | Disks
----------------- | --------- | --------- | ---------
`cron`            | Yes       | Yes       | Yes
`datePattern`     | Yes       | Yes       | Yes
`deviceName`      | No        | No        | Yes
`enabled`         | Yes       | Yes       | Yes
`instanceName`    | No        | Yes       | No
`snapshotPattern` | Yes       | Yes       | Yes
`timePattern`     | Yes       | Yes       | Yes
`timezone`        | Yes       | Yes       | Yes
`zone`            | No        | Yes       | No


#### Key Definitions

The keys used in the configuration file provide the flexibility of the utility,
below are the specifics on what they are used for and how to set them.


##### cron

Sections | Main      | Instances | Disks
-------- | --------- | --------- | ---------
Used in  | Yes       | Yes       | Yes

Provide a `cron expression` for the `scheduled` command scheduler to apply when
determining if a disk should be snapshot.  The expression consists of space
separated values representing *minute*, *hour*, *day of month*, *month*, and
*day of week* as follow:
```
┌────────── minute       (0 - 59)
│ ┌──────── hour         (0 - 23)
│ │ ┌────── day of month (1 - 31)
│ │ │ ┌──── month        (1 - 12)
│ │ │ │ ┌── day of week  (0 - 6) (Sunday to Saturday)
* * * * *
```

Asterisks are used to represent "any" value, dashes (ex: 1-4) are ues to defin
ranges, commas (ex: 3,6,9) are used to separated items in a list, and slashes
(ex: */3) are used to define steps.  More information on using [CRON][Wiki-CRON]
in it's Wiki page.

Example:
```json
  "cron": "0 */6 * * *"
```


##### datePattern

Sections | Main      | Instances | Disks
-------- | --------- | --------- | ---------
Used in  | Yes       | Yes       | Yes

The format of the date pattern to use in the **snapshotPattern** as `%date%`.  A
full list of characters to use can be found in the PHP [date function][PHP-DATE]
documentation.

Example:
```json
  "datePattern": "Y-m-d"
```


##### deviceName

Sections | Main      | Instances | Disks
-------- | --------- | --------- | ---------
Used in  | No        | No        | Yes

Provides the name of the disk as it is specified on GCP.

Example:
```json
  "deviceName": "vol-60108108585--dev-sda-f8482e1f"
```


##### enabled

Sections | Main      | Instances | Disks
-------- | --------- | --------- | ---------
Used in  | Yes       | Yes       | Yes

Set the state of disk snapshot for when calling the `scheduled` command.

Example:
```json
  "enabled": false
```


##### instanceName

Sections | Main      | Instances | Disks
-------- | --------- | --------- | ---------
Used in  | No        | Yes       | No

Provides the name of the instance as it is specified on GCP.

Example:
```json
  "instanceName": "cust01-lg49js3g"
```


##### snapshotPattern

Sections | Main      | Instances | Disks
-------- | --------- | --------- | ---------
Used in  | Yes       | Yes       | Yes

Pattern to use when naming snapshots.  This value may consist of arbitrary text
and one or more of the following placeholders:

Placeholder | Key Value
----------- | ---------------
`%vm%`      | `instanceToken`
`%disk%`    | `diskToken`
`%date%`    | `datePattern`
`%time%`    | `timePattern`

Each placeholder is replaced with the value of its associated **key** value,
with exception to the `%date%` and `%time%` placeholders which are processed
first.

Example:
```json
  "snapshotPattern": "%vm%-%disk%-%date%-%time%"
```


##### timePattern

Sections | Main      | Instances | Disks
-------- | --------- | --------- | ---------
Used in  | Yes       | Yes       | Yes

Format of the time pattern to use in the **snapshotPattern** as `%time%`.  A
full list of characters to use can be found in the PHP [date function][PHP-DATE]
documentation.

Example:
```json
  "timePattern": "H-i-s"
```


##### timezone

Sections | Main      | Instances | Disks
-------- | --------- | --------- | ---------
Used in  | Yes       | Yes       | Yes

Timezone of the instance should be considered to be in when running the
scheduled command.  The value is used to compare against the **cron** expression
for each `disk`.  A list of valid values can be found in the PHP
[valid timezones][PHP-TIMEZONE] documentation.

Example:
```json
  "timezone": "America\/Los_Angeles"
```


##### zone

Sections | Main      | Instances | Disks
-------- | --------- | --------- | ---------
Used in  | No        | Yes       | No

Provides the zone of the instance as it is specified on GCP.

Example:
```json
  "zone": "us-east3-a"
```


### Full Configuration Example

Example:
--------

```json
{
    "enabled": true,
    "timezone": "America\/New_York",
    "cron": "0 5 * * *",
    "datePattern": "Y-m-d",
    "timePattern": "H-i-s",
    "snapshotPattern": "%vm%-%disk%-%date%",
    "instances": {
        "internalA": {
            "instanceName": "int01-f3j9kn41",
            "zone": "us-east3-a",
            "disks": {
                "os": {
                    "deviceName": "vol-60108108585--dev-sda-f8482e1f"
                }
            }
        },
        "customerA": {
            "instanceName": "cust01-lg49js3g",
            "zone": "us-east3-b",
            "snapshotPattern": "%vm%-%disk%-disk-%date%-%time%",
            "disks": {
                "os": {
                    "enabled": false,
                    "deviceName": "vol-80868051015--dev-sda-281ff84e",
                    "cron": "1 30 * * *"
                },
                "data": {
                    "deviceName": "vol-80868051015--dev-sdc-c39caab0",
                    "cron": "0 6,18 * * *"
                }
            }
        },
        "customerB": {
            "instanceName": "cust02-0b527a33",
            "zone": "us-west1-d",
            "enabled": false,
            "timezone": "America\/Los_Angeles",
            "cron": "0 *\/4 * * *",
            "disks": {
                "os": {
                    "deviceName": "vol-91927545157--dev-sda-5e423448",
                    "cron": "0 4,16 * * *",
                    "snapshotPattern": "%vm%-%disk%-disk-%date%-%time%"
                },
                "data1": {
                    "deviceName": "vol-91927545157--dev-sdb-8f824fe1",
                    "snapshotPattern": "%vm%-%disk%-disk-%date%-%time%"
                },
                "data2": {
                    "enabled": true,
                    "deviceName": "vol-91927545157--dev-sdc-e2926e47",
                    "snapshotPattern": "%vm%-%disk%-disk-%date%-%time%"
                }
            }
        }
    }
}
```




Reference
---------

[Scripting `gcloud` commands](https://cloud.google.com/sdk/docs/scripting-gcloud)
[Google Cloud Platform `gcloud` reference](https://cloud.google.com/sdk/gcloud/reference/)


[GCP]: https://cloud.google.com
    "Google Cloud Platform"
[GCP-Compute]: https://cloud.google.com/compute
    "Google Cloud Platform - Compute Engine"
[GCP-Instances]: https://cloud.google.com/compute/docs/instances
    "Google Cloud Platform - Virtual Machine Instances"
[GCP-Create-Snapshots]: https://cloud.google.com/compute/docs/disks/create-snapshots
    "Google Cloud Platform - Create Persistent Disk Snapshots"
[GCP-SDK]: https://cloud.google.com/sdk
    "Google Cloud Platform - Command-line Interface"
[GCP-SDK-Install]: https://cloud.google.com/sdk/downloads
    "Google Cloud Platform - Install SDK Utility"
[GCP-SDK-Initialize]: https://cloud.google.com/sdk/docs/initializing
    "Google Cloud Platform - Initialize SDK Utility"
[PHP]: http://php.net
    "PHP: Hypertext Preprocessor"
[Wiki-CRON]: https://en.wikipedia.org/wiki/Cron
    "CRON - Wikipedia"
[PHP-DATE]: http://php.net/manual/function.date.php
    "PHP: date - Manual"
[PHP-TIMEZONE]: http://php.net/manual/timezones.php
    "PHP: List of Supported Timezones - Manual"
