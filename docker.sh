#!/bin/bash
if [ -e parsedown/Parsedown.php ]; then
  docker run -it --rm --name mail-test -v "$PWD":/usr/src/mail-test -w /usr/src/mail-test php:8.2-cli php mail.php
else
  echo "clone recursive or init submodules ;-)"
fi
