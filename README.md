# Buri/nette-autoload-services
Autoload services to nette container

Requirements
------------

Buri/nette-autoload-services requires PHP 7.0 or higher.

- [Nette DI](https://github.com/nette/di)
- [Nette Finder](https://github.com/nette/finder)
- [PHP tokenizer](http://php.net/manual/en/book.tokenizer.php)


Installation
------------

The best way to install Buri/nette-autoload-services is using  [Composer](http://getcomposer.org/):

```sh
$ composer require buri/nette-autoload-services
```


Documentation
------------

You can enable the extension using your neon config.

```yml
extensions:
	serviceAutoload: Buri\NAS\DI\Extension\AutoloadingExtension
```

You must place this extension BEFORE any other extension that will benefit from autoloading (ie. Kdyby/Console)

Configuration
-------------

This extension creates new configuration section `serviceAutoload` and no minimal configuration is required. Out of the box it provides support for:
- [Kdyby/Console](https://github.com/kdyby/console)
- [Kdyby/Events](https://github.com/kdyby/events)

If you wish to register your own directory for service autoloading, example follows:
```yml
serviceAutoload:
	consoleCommands:                  # Group name must be present, but can be any valid neon identifier
		directory: %appDir%/Console     # Directory to search in, recursively (string or string[])
		mask:                           # Filename mask, only matching files will be scanned for classes (string or string[])
			- *Command.php
		tag: kdyby.console.command      # Optional, can be used to tag services (string or string[])
```
