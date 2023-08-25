These scripts use the <?= expression ?> syntax.

This works in PHP version 5.4 and later. For earlier versions, the PHP configuration variable 'short_open_tag' must be on. This variable cannot be set by ini_set, it must be done through a .htaccess file with this content:

php_flag short_open_tag on 

2012-04-10
