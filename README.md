# photobank-bridge

This is a syncing bridge among photobanks.

It queues images for remote download, synchronizes meta.

Install with:
```
$ git clone git@github.com:volcanolog/photobank-bridge.git
$ cd photobank-bridge
$ composer install
```

To fill download queue run:
```
$ php charge_download_queue.php
```
