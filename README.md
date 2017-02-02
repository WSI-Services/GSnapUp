GSnapUp
=======

Google Cloud Platform Snapshot Backup System



Prerequisites
-------------

Before you can start using `GSnapUp` you must setup Google Cloud SDK:
 * [Install](https://cloud.google.com/sdk/downloads)
 * [Initialize](https://cloud.google.com/sdk/docs/initializing)


Commands
--------

init                Initialize configuration


Configuration
-------------

```
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
