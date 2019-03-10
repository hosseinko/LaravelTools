# LaravelTools
Tools for Laravel applications development

* FindTranslations.php
- This is a Laravel console commands that searchs app and view directories and finds usages of trans() __() {{@lang()}} and extracts translation keys. This file accepts one option --create-file which its allowed values are "yes" or "no". If used with --create-file=yes translation files will be created and if they are already existed, they will be updated with new keys
