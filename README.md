# MySql Db Automation
Provides static factory creation of datbase connection.
```
// config.php
include 'vender/autoload.php';

use Solar\Db\DbConnection::initialize([
  'host'      => 'host',
  'user'      => 'user',
  'password'  => '********',
  'database'  => 'database'
]);
```
Then when you need it...
```
include 'config.php';

$db = Solar\Db\DbConnection::getInstance();
```
