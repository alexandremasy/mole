<?php 
/**
 *	PHP - Mole
 *	A javascript finder utility script. Find all the javascript files in the requested folders.
 *	
 *	@author Alexandre Masy
 **/
class Mole
{

	/**
	 *	HTML output
	 *	
	 *	@const HTML
	 **/
	const HTML = 'html';

	/**
	 *	Closure output
	 *
	 *	@const CLOSURE
	 **/
	const CLOSURE = 'closure';

	/**
	 *	List all the added paths
	 *
	 *	@var array
	 **/
	protected $paths;

	/**
	 *	List all the resolved files
	 *	
	 *	@var array
	 **/
	protected $list;

	/**
	 *	root path. Used for physically finding the files
	 *
	 *	@var string
	 **/
	protected $root;

	/**
	 *	base path. This path is used to compute the path for html output
	 *	
	 *	@var string
	 **/
	protected $base;

	/**
	 *	path to the minified file
	 *
	 *	@var string
	 **/
	protected $minified;

	/**
	 *	Do we use relative path
	 *
	 *	@var bool
	 **/
	protected $relative;

	/**
	 *	Are we aware of the environment variable
	 *
	 *	@var bool
	 **/
	protected $env;

	/**
	 *	El constructor
	 *
	 *	@param minified String Path where would be ouputed the minified version.
	 *	@param root String Path to the root folder. This path will be used to detect file existance.
	 *	@param base String Path to the document root. Used to output html path relative to this one.
	 *	@param relative Boolean If true output relative path, if not absolute path
	 *	@param env Boolean Is the output aware of the environment.
	 **/
	public function __construct( $minified, $root, $base=null, $relative=false, $env=true )
	{
		$this->list = array();
		$this->paths = array();
		$this->root = realpath($root);
		$this->base = $base;
		$this->minified = $minified;
		$this->relative = $relative;
		$this->env = $env;
	}

	/**
	 *	Add a path to the library
	 *
	 *	@param path String the path to add
	 **/
	public function add( $path )
	{
		if ( !$this->exists($path) )
			array_push( $this->paths, $path );
	}

	/**
	 *	Is the path already in the list
	 *
	 *	@param path String
	 *	@return Boolean
	 **/
	public function exists( $path )
	{
		return in_array( $path, $this->paths );
	} 

	/**
	 *	Build the output to the provided type
	 *	
	 *	@param type String
	 *	@return String
	 *	@see Mole::HTML
	 *	@see Mole::CLOSURE
	 **/
	public function build( $type )
	{
		$this->resolve();

		$ret = '';
		$format = '';


		// pre loop 
		switch ($type)
		{
			case self::CLOSURE:
				$format = "--js %s \n";
				break;

			default:
			case self::HTML:
				$format = "<script type=\"text/javascript\" src=\"%s\"></script>\n";
				break;
		}

		// loop
		foreach ($this->list as $path) 
		{
			// remove the first / if present
			$path = $path[0] == '/' ? substr($path, 1) : $path; 

			// normalise
			$path = $this->root. '/' .$path;

			// relative
			if ( $this->relative or $type == self::HTML )
			{
				// add the base
				if ( isset($this->base) )
				{
					$path = substr($path, strlen($this->base));
				}
				else
				{
					$n = $type == self::HTML ? strlen($this->root) : strlen($this->root)+1;
					$path = substr($path, $n);
				}
			}
			else if ( $type == self::CLOSURE )
			{
				// resolve
				$path = realpath($path);

				// shell escape
				$path = str_replace(array('\\', '%', ' '), array('\\\\', '%%', '\ '), $path);
			}

			$ret .= sprintf( $format, $path );
		}

		// post loop
		switch ($type)
		{
			case self::CLOSURE:
				$ret .= "\n";
				$ret .= "--compilation_level WHITESPACE_ONLY \n";
				$ret .= sprintf("--js_output_file %s", $this->minified);
				break;

			default:
				break;
		}

		return $ret;
	}

	/**
	 *	Write the build output to the specified location
	 *
	 *	@param type String Type of output. Available values Mole::HTML, Mole::CLOSURE.
	 *	@param destination String Path to ouput the result
	 **/
	public function write( $type, $destination )
	{
		$dir = dirname($destination);
		if ( !is_writable($dir) )
		{
			echo "<div class='error'>The directory is not writable: <em>$dir</em></div>";		
			return;
		}

		$content = $this->build( $type );

		$mode = file_exists($destination) ? 'w+' : 'x+';
		$fh = fopen($destination, $mode);
		fwrite($fh, $content);
		fclose($fh);
	}	

	/**
	 *	Resolve all the path to find the javscript files
	 *	
	 **/
	protected function resolve()
	{
		$n = count( $this->paths ) -1;
		$i = -1;
		$e;

		while( $i++<$n )
		{
			$e = $this->paths[$i];
			$path = $this->root. DIRECTORY_SEPARATOR . $e;

			if ( !file_exists($path) )
			{
				printf("<div class='error'>The directory does not exists <em>%s </em></div>\n", $path);
				continue;
			}

			if ( is_dir($path) )
				$list = $this->getFilesInDirectory( $path );
			else
				$list = array( $e );

			$this->list = array_merge($this->list, $list);
			$this->list = array_unique($this->list);
		}
	}

	/**
	 *	Find all the files in the given path
	 *
	 *	@param path String
	 *	@return Array
	 **/
	protected function getFilesInDirectory( $sourcePath )
	{
		$ret = array();

		$paths = scandir($sourcePath, 0);
		$len = count($paths);

		for ($i = 0; $i < $len; $i++)
		{
			$path = $paths[$i];

			if ($path != '..' && $path != '.')
			{
				$fullPath = $sourcePath . '/' . $path;

				if (is_dir($fullPath))
				{
					$ret = array_merge($ret, $this->getFilesInDirectory($fullPath));
				}
				else if (strstr($path, '.js') !== false)
				{
					$path = substr($fullPath, strlen($this->root)+1);
					$path = str_replace('\\', '/', $path);
					array_push($ret, $path);
				}
			}
		}

		return $ret;
	}

}


?>