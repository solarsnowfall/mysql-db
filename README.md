# MySql Db Automation
Provides static factory creation of datbase connection.
```
// config.php
include 'vender/autoload.php';

user Solar\Db\DbConnection;

DbConnection::initialize([
  'host'      => 'host',
  'user'      => 'user',
  'password'  => '********',
  'database'  => 'database'
]);
```
Then when you need it...
```
include 'config.php';

$db = DbConnection::getInstance();
```
Provides a wrapper which enforces a prepared statement only paradimg.
```
$stmt = $db->execute($sql, $params, $types);

$rows = $stmt->fetchAllAssoc();
```
Map table rows to objects by extending the AbstractRow.
```
use Solar\Db\Table\Row\AbstractRow;

class User extends AbstractRow
{
  const TABLE = 'user';
  
  protected string $email;
  
  protected string $fullName;
  
  protected int $id;
}
```
Map rows to your objects using the table row gateway.
```
use Solar\Db\Table\Row\Gateway;

$gateway = new Gateway(User::TABLE, User::class);

$user = $gateway->fetchRow(['id' => 1]);
```
Access properties with smart magic accessors and mutators.
```
use Solar\Db\Table\Row\AbstractRow;

class User extends AbstractRow
{
  const MAGIC_GETTERS = true;
  
  const MAGIC_SETTERS = true;
  
  const TABLE = 'user';
  ...
}
```
Zend Db insprited query automation. This mostly exists to facilitate the table and row gateways, but most workaday queries can be handled.
```
use Solar\Db\Sql\Sql;

$sql = new Sql();

$insert = $sql->insert();

$index = $insert->columns(['email', 'full_name'])->into('user')->set(['janedoe@gmail.com', 'Jane Doe')->execute();
```
Or just do it from your class.
```
// Returns a fully formed primary key array.
$index = $user->setEmail($email)->setFullName($fullName)->insert();
```
Use the speedy cached schema interface to know a little more about your database.
```
user Solar\Db\Table\Schema;

$table = Schema(User::TABLE);

$paramTypes = $table->getParamTypes($columns);
```
