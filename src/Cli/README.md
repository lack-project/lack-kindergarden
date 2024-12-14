# Cli Usage

```php
class Coder
{



    #[CliCommand('coder:prepare', 'Prepare the coder')]
    #[CliArgument('name', 'Name of the argument', true)]
    public function coder_prepare(
        #[CliParamDescription('The name of the coder')]
        string $name,

        #[CliParamDescription('The name of the coder')]
        string $wurstbrot,
        array $argv

    ) {
        echo "Preparing coder $name\n";
        print_r ($argv);
    }

}


```


## Registering Classes:

```php
$app = \Lack\Kindergarden\Cli\CliApplication::getInstance();
$app->node()->group("coder", "Coder commands");

$app->registerClass(\Lack\Kindergarden\Coder\Coder::class);
```
