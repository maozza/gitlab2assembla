if [ -z "${ASSEMBLA_KEY}" ] || [ -z "${ASSEMBLA_SECRET}" ] || [ -z "${ASSEMBLA_SPACE}" ] 
then
    echo "you must set environment variable: ASSEMBLA_KEY,ASSEMBLA_SECRET,ASSEMBLA_SPACE"
    exit 1
fi
mv /var/www/html/config.example.php /var/www/html/config.php
sed "s#replace_with_assembla_key#${ASSEMBLA_KEY}#" -i /var/www/html/config.php
sed "s#replace_with_asembla_secret#${ASSEMBLA_SECRET}#" -i /var/www/html/config.php
sed "s#assembla_space#${ASSEMBLA_SPACE}#" -i /var/www/html/config.php

apache2-foreground

