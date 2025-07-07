Jmonitor
=========

Easy monitoring for PHP web server. 

Getting Started
---------------
To get started, require the project using [Composer](https://getcomposer.org/).  
You'll also need a Psr ClientInterface http client to send metrics to Jmonitor.

```bash
composer require jmonitoring/jmonitor

# with symfony http client : 
composer require symfony/http-client nyholm/psr7

# or with guzzle 
composer require guzzlehttp/guzzle http-interop/http-factory-guzzle
```

```php
use Jmonitor\Jmonitor;
use Jmonitor\Collector\Apache\ApacheCollector;

$jmonitor = new Jmonitor('apiKey');

// Add some collectors (see the documentation below for more collectors)
$jmonitor->addCollector(new ApacheCollector('https://example.com/server-status'));
$jmonitor->addCollector(new SystemCollector();

// send metrics periodically to jmonitor (ex. every 15 seconds)
$jmonitor->collect();
```

You can use a custom HttpClient 



```php
use Psr\Http\Client\ClientInterface;

$client = new \GuzzleHttp\Client(); // or any PSR-18 compliant client
$jmonitor = new Jmonitor('apiKey', $client);
```

Collectors
-----------
- ### System
  Collects system metrics like CPU usage, memory usage, disk usage, etc.  
  Only Linux is supported for now, feel free to open an issue if you need support for another OS.

  ```php
  use Jmonitor\Collector\System\SystemCollector;
    
  $collector = new SystemCollector();
  ```

- ### Apache 
  Collects Apache server metrics from a server status URL.  
  You'll need to enable the `mod_status` module in Apache and set up a server status URL.
  There are some resources to help you with that:
  - Apache doc :https://httpd.apache.org/docs/current/mod/mod_status.html.
  - Blogpost in English : https://statuslist.app/apache/apache-status-page-simple-setup-guide/
  - Blogpost in French : https://www.blog.florian-bogey.fr/activer-et-configurer-le-server-status-apache-mod_status.html  

  Then you'll be able to use the `ApacheCollector` class to collect metrics from the server status URL.

  ```php
  use Jmonitor\Collector\Apache\ApacheCollector;
  
  $collector = new ApacheCollector('https://example.com/server-status');
  ```

- ### Mysql 
    Collects MySQL server variables and status.  
    You'll need to use PDO or Doctrine to connect to your MySQL database. If you need support for other drivers, like Mysqli, please open an issue.
    
  ```php
  use Jmonitor\Collector\Mysql\MysqlCollector;
  use Jmonitor\Collector\Mysql\Adapter\PdoAdapter;
  use Jmonitor\Collector\Mysql\Adapter\DoctrineAdapter;
  use Jmonitor\Collector\Mysql\MysqlStatusCollector;
  use Jmonitor\Collector\Mysql\MysqlVariablesCollector;
  
  // with PDO
  $adapter = new PdoAdapter($pdo); // retrieve your \PDO connection
  
  // or Doctrine DBAL
  $adapter = new DoctrineAdapter($connection) /* retrieve your Doctrine\DBAL\Connection connection*/ );
  
  // Mysql has multiple collectors, use the same adapter for all of them
  $collector = new MysqlStatusCollector($adapter);
  $collector = new MysqlVariablesCollector($adapter);
  ```

- ### Php
  Collects PHP metrics like loaded extensions, some ini settings, opcache status, etc.  
  Php FPM status URL support is coming soon.

  ```php
  use Jmonitor\Collector\Php\PhpCollector
  
  $collector = new PhpCollector($adapter);
  ```

- ### Redis
  Collects Redis metrics from info command.
  
  ```php
  use Jmonitor\Collector\Redis\RedisCollector;
  
  // You can use any Redis client that supports the info command, like Predis or PhpRedis.
  $redisClient = new \Redis([...]);
  $redisClient = new Predis\Client();
  // also support \RedisArray, \RedisCluster, Relay... feel free to open an issue if you need support for another client.
  
  $collector = new RedisCollector($redis);
  ```
