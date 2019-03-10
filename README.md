# LaravelTools
Tools for Laravel applications development

* FindTranslations.php
    * This is a Laravel console commands that searchs app and view directories and finds usages of trans() __() {{@lang()}} and extracts translation keys. This file accepts one option --create-file which its allowed values are "yes" or "no". If used with --create-file=yes translation files will be created and if they are already existed, they will be updated with new keys. Also there is another option named --use-keys-as-values which its allowed values are "yes" or "no". When it is set to yes, in english version key will be used as value. Ex: "this_is_a_sample_key" will become "This is a sample key"
    
    * Requirements: Laravel 5.7
    * Installation: Copy this file to yor app/Console/Commands directory
    * Usage: php artisan app:find-translations --create-file=yes --use-keys-as-values=yes
