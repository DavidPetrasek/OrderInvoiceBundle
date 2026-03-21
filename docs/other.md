[Back to index](index.md)

Other
=====

### Categories
Generate enum to specify custom categories for orders or order items:
``` bash
symfony console make:oib:category
```


### Reseting sequential numbers
Either create a ready-to-use cron controller:
``` bash
symfony console make:oib:cron_controller
```

or reset them by:
``` php
use Psys\OrderInvoiceBundle\Service\InvoiceManager\InvoiceManager;
...
$invoiceManager->resetSequentialNumbers();
```