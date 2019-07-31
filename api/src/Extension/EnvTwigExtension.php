<?php
 
 namespace App\Extension;
 
 use Twig\Extension\AbstractExtension;
 use Twig\TwigFunction;
 use Twig_Environment;

 class EnvTwigExtension extends AbstractExtension
 {
	public function getFunctions(): array
	{
	 return [
		 new TwigFunction('api_env', 'getenv'),
	 ];
	}
 
	/**
	 * Initializes the runtime environment.
	 *
	 * This is where you can load some file that contains filter functions for instance.
	 *
	 * @param Twig_Environment $environment The current Twig_Environment instance
	 */
	public function initRuntime(Twig_Environment $environment)
	{
	 // TODO: Implement initRuntime() method.
	}
 
	/**
	 * Returns a list of global variables to add to the existing list.
	 *
	 * @return array An array of global variables
	 */
	public function getGlobals()
	{
	 // TODO: Implement getGlobals() method.
	}
 
	/**
	 * Returns the name of the extension.
	 *
	 * @return string The extension name
	 */
	public function getName()
	{
	 // TODO: Implement getName() method.
	 return 'env_twig_extension';
	}
 }