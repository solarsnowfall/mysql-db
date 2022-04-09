# MySql Db Automation
Provides static factory creation of datbase connection.
``
// config.php
include 'vender/autoload.php';

use Solar\Db\DbConnection::initialize([
  'host'      => 'host',
  'user'      => 'user',
  'password'  => '********',
  'database'  => 'database'
]);
``
