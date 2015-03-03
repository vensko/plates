<?php

namespace League\Plates\Template;

use Closure, LogicException;

/**
 * Preassigned template data.
 */
class Data
{
	/**
	 * Variables shared by all templates.
	 * @var array
	 */
	protected $sharedVariables = [];

	/**
	 * Specific template variables.
	 * @var array
	 */
	protected $templateVariables = [];

	/**
	 * Callbacks shared by all templates.
	 * @var array
	 */
	protected $sharedCallbacks = [];

	/**
	 * Specific template callbacks
	 * @var array
	 */
	protected $templateCallbacks = [];

	/**
	 * Add template data.
	 * @param  array|\Closure $data ;
	 * @param  null|string|array $templates ;
	 * @return Data
	 */
	public function add($data, $templates = null)
	{
		if (is_null($templates)) {
			return $this->shareWithAll($data);
		} elseif (is_array($templates)) {
			return $this->shareWithSome($data, $templates);
		} elseif (is_string($templates)) {
			return $this->shareWithSome($data, [$templates]);
		} else {
			throw new LogicException(
				'The templates variable must be null, an array or a string, '.gettype($templates).' given.'
			);
		}
	}

	/**
	 * Add data shared with all templates.
	 * @param  array|\Closure $data
	 * @return Data
	 */
	public function shareWithAll($data)
	{
		if ($data instanceof Closure) {
			$this->sharedCallbacks[] = $data;
		} else if (is_array($data)) {
			$this->sharedVariables = array_merge($this->sharedVariables, $data);
		}

		return $this;
	}

	/**
	 * Add data shared with some templates.
	 * @param  array|\Closure $data
	 * @param  array $templates
	 * @return Data
	 */
	public function shareWithSome($data, array $templates)
	{
		foreach ($templates as $template) {
			if ($data instanceof Closure) {
				if (!isset($this->templateCallbacks[$template])) {
					$this->templateCallbacks[$template] = [];
				}
				$this->templateCallbacks[$template][] = $data;
			} else if (is_array($data)) {
				if (isset($this->templateVariables[$template])) {
					$this->templateVariables[$template] = array_merge($this->templateVariables[$template], $data);
				} else {
					$this->templateVariables[$template] = $data;
				}
			}
		}

		return $this;
	}

	/**
	 * Get template data.
	 * @param  null|string $template ;
	 * @return array
	 */
	public function get($template = null)
	{
		$data = $this->sharedVariables;

		foreach ($this->sharedCallbacks as $callback) {
			$result = $callback();
			if (is_array($result)) {
				$data = array_merge($data, $result);
			}
		}

		if (!is_null($template)) {
			if (isset($this->templateVariables[$template])) {
				$data = array_merge($data, $this->templateVariables[$template]);
			}

			if (isset($this->templateCallbacks[$template])) {
				foreach ($this->templateCallbacks[$template] as $callback) {
					$result = $callback();
					if (is_array($result)) {
						$data = array_merge($data, $result);
					}
				}
			}
		}

		return $data;
	}
}
