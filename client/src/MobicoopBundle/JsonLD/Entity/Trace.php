<?php
/**
 * Created by PhpStorm.
 * User= vagrant
 * Date= 8/13/19
 * Time= 12=41 PM
 */

namespace Mobicoop\Bundle\MobicoopBundle\JsonLD\Entity;


use http\Exception\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

class Trace
{
    /**
     * @var string $namespace;
     */
    private $namespace;
    /**
     * @var string $short_class
     */
    private $short_class= "";
    /**
     * @var string $class
     */
    private $class= "";
    /**
     * @var string $type
     */
    private $type= "";
    /**
     * @var string $function
     */
    private $function= "";
    /**
     * @var string $file
     */
    private $file= "";
    /**
     * @var int $line
     */
    private $line= 0;
    /**
     * @var array $args
     */
    private $args= [];
    
    /**
     * Trace constructor.
     */
    public function __construct()
    {
        $ctp = func_num_args();
        $args = func_get_args();
        if($ctp!=1 && $ctp!=8) throw new InvalidArgumentException('Bad parameters provided!');
        list($namespace,  $short_class,  $class,  $type,  $function,  $file,  $line,  $args) = array_values((($ctp==1)?reset($args): $args));
        $this->namespace = $namespace;
        $this->short_class = $short_class;
        $this->class = $class;
        $this->type = $type;
        $this->function = $function;
        $this->file = $file;
        $this->line = $line;
        $this->args = $args;
    }
    
    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }
    
    /**
     * @param string $namespace
     */
    public function setNamespace(string $namespace): void
    {
        $this->namespace = $namespace;
    }
    
    /**
     * @return string
     */
    public function getShortClass(): string
    {
        return $this->short_class;
    }
    
    /**
     * @param string $short_class
     */
    public function setShortClass(string $short_class): void
    {
        $this->short_class = $short_class;
    }
    
    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }
    
    /**
     * @param string $class
     */
    public function setClass(string $class): void
    {
        $this->class = $class;
    }
    
    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
    
    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }
    
    /**
     * @return string
     */
    public function getFunction(): string
    {
        return $this->function;
    }
    
    /**
     * @param string $function
     */
    public function setFunction(string $function): void
    {
        $this->function = $function;
    }
    
    /**
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }
    
    /**
     * @param string $file
     */
    public function setFile(string $file): void
    {
        $this->file = $file;
    }
    
    /**
     * @return int
     */
    public function getLine(): int
    {
        return $this->line;
    }
    
    /**
     * @param int $line
     */
    public function setLine(int $line): void
    {
        $this->line = $line;
    }
    
    /**
     * @return array
     */
    public function getArgs(): array
    {
        return $this->args;
    }
    
    /**
     * @param array $args
     */
    public function setArgs(array $args): void
    {
        $this->args = $args;
    }
    
    /**
     * Traces Loader.
     *
     * @param array $array
     * @return Trace[] array
     */
    public static function load(array $array): array {
        $traces= [];
        foreach($array as $key=>$value){
            $traces[]= new Trace((array)$value);
        }
        return $traces;
    }
    
}