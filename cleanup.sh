# chmod 700 cleanup.sh
#!/bin/bash

rm -f app/control.log
rm -rf app/__pycache__
rm -rf app/gameQuery/__pycache__
rm -rf app/static/.webassets-cache
rm -rf app/openid_store