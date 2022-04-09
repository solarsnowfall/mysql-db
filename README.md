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
Provides a wrapper which enforces a prepared statement only paradimg.
```
$stmt = $db->execute($sql, $params, $types);
```
Map table rows to objects by extending the AbstractRow.
```
class User extends AbstractRow
{
  const TABLE = 'user';
  
  protected string $email;
  
  protected string $fullName;
  
  protected int $id;
}
```
Map rows to your objects using the table gateway.
```
$gateway = new Solar\Db\Table\Gateway(User::TABLE, User::class);

$user = $gateway->fetchRow(['id' => 1]);
```
Access properties with smart magic accessors and mutators.
```
class User
{
  const MAGIC_GETTERS = true;
  
  const MAGIC_SETTERS = true;
  
  const TABLE = 'user';
  ...
}
```
