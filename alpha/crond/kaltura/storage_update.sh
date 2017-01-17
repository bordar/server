#!/bin/bash

WHEN=$(date -d "yesterday" +%Y-%m-%d)

#php /opt/borhan/app/scripts/findEntriesSizes.php $WHEN >> /var/log/`hostname`-findEntriesSizes.log
php /opt/borhan/app/scripts/batch/validatePartnerUsage.php >> /var/log/`hostname`-BatchPartnerUsage_upgradeProcess.log.${WHEN} 2>&1
tail /var/log/`hostname`-BatchPartnerUsage_upgradeProcess.log.${WHEN} | mail -s "batchPartnerUsage on `hostname`" it.prod@borhan.com,records@borhan.com
