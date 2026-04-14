@echo off
if exist parsedown\Parsedown.php (
    docker run -it --rm --name mail-test -v "%cd%":/usr/src/mail-test -w /usr/src/mail-test php:8.2-cli php mail.php
) else (
    echo clone recursive or init submodules ;-)
)
set /p DUMMY=Hit ENTER to continue...
