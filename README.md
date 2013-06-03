# Monoconf

log4j like configuration for the [monolog](https://github.com/Seldaek/monolog)
logging framework.

## Usage

1. Create a config JSON file:

```
{
	"rules": {
		"Namespace\\*": {
			"debug": ["debug-handler"]
		},
	},
	"handler": {
		"error-handler": {
			"type": "Monolog\Handler\StreamHandler",
			"args": [
				"/my/app/error.log"
			],
			"formatter": "line"
		},
		"debug-handler": {
			"type": "Monolog\Handler\StreamHandler",
			"args": [
				"/my/app/application.log"
			],
			"formatter": "line",
		}
	},
    "formatter": {
        "line": {
			"type": "Monolog\Formatter\LineFormatter",
			"args": [
				"%datetime% %pid% %channel%@%level_name% %message% %context%\n"
			]
        }
	},
    "processor": {
        "pid": {
            "type": "Monolog\Processor\ProcessIdProcessor"
        },
    }
}
```

2. In your application:

``` php

require 'vendor/autoload.php';

use Monoconf\Monoconf;

$config = Monoconf::config(json_decode(file_get_contents('monoconf.json'), true));
```

``` php
namespace MyApp\Controller;

class SomeController {

    protected $Log;

    public function __construct() {
        self::$Log = \Monoconf\Monoconf::getLogger(__CLASS__);
    }

    public function someAction()
    {
        self::$Log->debug(__METHOD__.' called');
    }
}
```

## Tests

```
phpunit --bootstrap tests/bootstrap.php tests/
```
