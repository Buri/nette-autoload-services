<?php

namespace Buri\NAS\DI\Extension;

use Nette\DI\CompilerExtension;
use Nette\Utils\Finder;

class AutoloadingExtension extends CompilerExtension
{
	public $defaults = [
		'kdybyCommands' => [
			'directory' => '%appDir%/Console',
			'mask'      => '*Command.php',
			'tag'       => 'kdyby.console.command',
		],
		'kdybyEvents'   => [
			'directory' => '%appDir%/EventListeners',
			'mask'      => ['*Listener.php', '*Subscriber.php'],
			'tag'       => 'kdyby.subscriber',
		],
	];

	/** @var string */
	private $mask;

	/** @var string[] */
	private $tags;

	public function beforeCompile()
	{
		$config = $this->getConfig($this->defaults);
		foreach ($config as $group) {
			$this->processGroup($group);
		}
	}

	private function processGroup($group) {
		$directories = $group['directory'];
		if (is_string($directories)) $directories = [$directories];
		$this->mask = $group['mask'];
		if (empty($group['tag'])) $group['tag'] = [];
		$this->tags = array_filter(is_array($group['tag']) ? $group['tag'] : [$group['tag']]);
		foreach ($directories as $dir) {
			if (is_dir($dir)) {
				$this->registerClassesFromDirectory($dir);
			}
		}
	}

	protected function registerClassesFromDirectory(string $directory)
	{
		foreach (Finder::findFiles($this->mask)->from($directory) as $path => $info) {
			$class = $this->getClassesFromFile($path);
			if (null === $class) continue; // No class found or abstract
			$this->compileClass($class);
		}
	}

	protected function compileClass($class)
	{
		$builder = $this->getContainerBuilder();
		$kebab = preg_replace('/\\\\/', '_', $class);
		$definition = $builder->getByType($class);
		if (null === $definition) {
			$definition = $builder->addDefinition($this->prefix($kebab))->setClass($class);
		}
		foreach ($this->tags as $tag) {
			$definition->addTag($tag);
		}
	}

	protected function getClassesFromFile($file)
	{
		$fp = fopen($file, 'r');
		$class = $namespace = $buffer = '';
		$i = 0;
		while (!$class) {
			if (feof($fp)) break;

			$buffer .= fread($fp, 512);
			$tokens = token_get_all($buffer);

			if (strpos($buffer, '{') === false) continue;

			for (; $i < count($tokens); $i++) {
				if ($tokens[$i][0] === T_ABSTRACT) {
					return null;
				}
				if ($tokens[$i][0] === T_NAMESPACE) {
					for ($j = $i + 1; $j < count($tokens); $j++) {
						if ($tokens[$j][0] === T_STRING) {
							$namespace .= '\\' . $tokens[$j][1];
						} else if ($tokens[$j] === '{' || $tokens[$j] === ';') {
							break;
						}
					}
				}

				if ($tokens[$i][0] === T_CLASS) {
					for ($j = $i + 1; $j < count($tokens); $j++) {
						if ($tokens[$j] === '{') {
							$class = $tokens[$i + 2][1];
						}
					}
				}
			}
		}

		if ($namespace[0] === '\\') $namespace = substr($namespace, 1);

		return $namespace . '\\' . $class;
	}
}


