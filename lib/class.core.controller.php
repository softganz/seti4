<?php
/**
 * Core Function :: Controller Process Web Configuration and Request
 * Created :: 2006-12-16
 * Modify  :: 2025-10-23
 * Version :: 40
 */

/*************************************************************
 * Core class and function library for core process
 *
 * Manage Resource, Controller and Utilities function
**************************************************************/

//---------------------------------------
// Class R :: Core resource
//---------------------------------------
class R {
	public $configFolder;
	public $colorScheme;
	public $request;
	public $appAgent = NULL;
	public $message;
	public $setting;
	public $options;
	public $counter;
	public $timer;
	public $user;
	public $error;
	public $debug;
	public $pageClass = [];
	public $pageAttribute = [];
	public $core;
	public $DB;
	public $myDb;
	public $mysql;
	public $query;
	public $query_items = [];

	function __construct() {
		$this->setting = (Object) [];
		$this->options = (Object) [];
		$this->message = (Object) [];
	}

	public static function Option($key = NULL, $value = NULL) {
		if (isset($key) && isset($value)) $GLOBALS['R']->options->{$key} = $key;
		return isset($GLOBALS['R']->options->{$key}) ? $GLOBALS['R']->options->{$key} : NULL;
	}

	public static function Setting($key = NULL, $value = NULL) {
		if (empty($key)) {
			return $GLOBALS['R']->setting;
		} else if (is_object($key)) {
			$GLOBALS['R']->setting = $key;
			return $GLOBALS['R']->setting;
		} else if (isset($key) && isset($value)) {
			$GLOBALS['R']->setting->{$key} = $key;
			return isset($GLOBALS['R']->setting->{$key}) ? $GLOBALS['R']->setting->{$key} : NULL;
		}
	}

	public static function Module($moduleName, $className = NULL) {
		$paraArgs = func_get_args();
		$rName = $paraArgs[0];
		$rName = 'module.'.$rName;
		$paraArgs[0] = $rName;
		$ret = call_user_func_array('load_resource', $paraArgs);
		return $ret;
	}

	public static function Model($modelName) {
		$paraArgs = func_get_args();
		$rName = $paraArgs[0];
		$rName = 'r.'.$rName;
		$paraArgs[0] = $rName;
		$ret = call_user_func_array('load_resource', $paraArgs);
		return $ret;
	}

	public static function View($viewName) {
		$paraArgs = func_get_args();
		$rName = $paraArgs[0];
		$rName = 'view.'.$rName;
		$paraArgs[0] = $rName;
		$ret = call_user_func_array('load_resource', $paraArgs);
		return $ret;
	}

	public static function Page($pageName) {
		$paraArgs = func_get_args();
		$paraArgs[0] = 'page.'.$paraArgs[0];
		$ret = call_user_func_array('load_resource', $paraArgs);
		return $ret;
	}

	public static function PageWidget($pageName, $args = []) {
		$filePrefix = 'page.';
		$buildMethod = 'build'; // Default build method
		$reservedMethod = ['rightToBuild'];

		if (preg_match('/^api\./', $pageName)) $filePrefix = '';

		// Specific build method using .. method at end of action
		if (preg_match('/([\w].*)(\.\.)([\w\.].*)$/', $pageName, $out)) {
			$pageName = $out[1];
			$buildMethod = $out[3];
			$buildMethod = preg_replace_callback('/\.(\w)/', function($matches) {return strtoupper($matches[1]);}, $buildMethod);
		}

		list($className, $found, $fileName, $resourceType) = SgCore::loadResourceFile($filePrefix.$pageName);

		if ($found && class_exists($className) && method_exists($className, $buildMethod)) {
			// Found page widget then build method
			$pageResult = new $className(...$args);

			// Try to use namespace in template but not work
			// $pageResult = new PPI\ProjectJoinList(...$args);
			// use PPI\ProjectJoinList as MyClass;
			// $pageResult = new MyClass(...$args);

			if ($buildMethod === 'build') {
				// If using standard build method, have 2 way for check right
				// 1. From rightToBuild method
				// 2. Check right from internal build method
				// and return error if not right
				if (method_exists($pageResult, 'rightToBuild')) {
					$error = $pageResult->rightToBuild();
					if (is_object($error)) return $error;
				}
			} else {
				// Other using specific build method that not in reservedMethod
				// Must have rightToBuild method and result is not error
				if (!method_exists($pageResult, 'rightToBuild')) return error(_HTTP_ERROR_FORBIDDEN, 'Access Denied');
				$error = $pageResult->rightToBuild();
				if (is_object($error)) return $error;
			}

			// TODO: Extensions
			// require_once('extensions/ppi/page.project.join.list.php');

			// Build widget, if not in reservedMethod and buildMethod is public
			if (
				!in_array($buildMethod, $reservedMethod)
				&& ($reflection = new ReflectionMethod($pageResult, $buildMethod))
				&& $reflection->isPublic()
			) {
				// $pageResult->extension();

				return $pageResult->{$buildMethod}();
			}
		} else if ($found && function_exists($className)) {
			// Found page function version, function name = className
			array_unshift($args, new Module());
			$ret = $className(...$args);
			return new Widget(['exeClass' => $args[0], 'child' => $ret]);
		}
		return error(_HTTP_ERROR_NOT_FOUND, 'PAGE NOT FOUND');
	}

	public static function On($eventName) {
		$paraArgs = func_get_args();
		$ret = NULL;
		if (is_string($paraArgs[0])) {
			$paraArgs[0] = 'on.'.$paraArgs[0];
			$ret = call_user_func_array('load_resource', $paraArgs);
		}
		return $ret;
	}

	public static function Manifest($modulename) {
		$loadResult = SgCore::loadResourceFile('manifest.'.$modulename);
		return $loadResult;
	}

	public static function Asset($assetName) {
		// assetName Format : project:name.ext
		// assetFile Format : {module}.name.ext
		// Location Folder : modules/{}/template/assets, modules/{module}/assets, core/assets

		list($moduleName, $assetName) = explode(':', $assetName);
		$packageName = 'asset:'.$moduleName.'/'.$assetName;
		list($funcName, $found, $fileName, $resourceType, $resultContent) = SgCore::loadResourceFile($packageName);

		if (preg_match('/.json$/i', $assetName)) {
			$resultContent = preg_replace('/\s+\/\/.*/', '', $resultContent);
		}

		return $resultContent;
	}
}

//---------------------------------------
// Class SgCore :: Core Class
//---------------------------------------
class SgCore {
	/**
	* Find template location
	* @param String $filename
	* @param String ext_folder Each folder seperate by ;
	* @return Mixed False on file not found and file location on found
	*/
	static function getTemplate($filename = NULL, $ext_folder = NULL) {
		if (empty($filename)) return false;
		$theme_folder = [];
		if ($ext_folder) {
			foreach ( explode(';',_CORE_FOLDER) as $folder ) {
				$theme_folder[] = $folder.'/'.$ext_folder.'/'.$GLOBALS['theme'].'/';
				$theme_folder[] = $folder.'/'.$ext_folder.'/default/';
			}
		}
		$theme_folder = array_unique(array_merge($theme_folder,cfg('theme.template')));
		$result = false;
		foreach ($theme_folder as $folder) {
			$load_file = $folder.'tpl.'.$filename.'.php';
			if (file_exists($load_file)) {
				$result = $load_file;
				break;
			}
		}

		// if (debug('template')) {
		// 	echo '<br />load template <b>'.$filename.'</b>'.($ext_folder?' width extension folder '.$ext_folder:'');
		// 	echo $result ? ' found <b>'.$result.'</b><br />' : ' <font color=red>not found</font><br />';
		// 	print_o($theme_folder,'$theme_folder',1);
		// }
		return $result;
	}

	function mergeConfig() {
	}

	/**
	* Load configuration from file and store into cfg
	* @param Mixed $configFile
	* @param Mixed $folder
	*/
	static function loadConfig($configFile = NULL, $folders = ['./core/assets/conf']) {
		$debugStr = '';
		$configArray = [];

		if (is_array($configFile)) {
			// merge array config to current config value
			$configArray = $configFile;
		} else if (is_string($configFile) && preg_match('/\.(\w*)\.(\w*)$/', $configFile, $out)) {
			// get match end with .module.ext from config file
			// last is config file extension
			// before is module name

			if (is_string($folders)) $folders = explode(';',$folders);

			$debugStr .= '<b>START LOAD CONFIG :: '.$configFile.' :: </b> from '.implode(';', (Array) $folders).'<br />';

			$module = $out[1];
			$configExt = $out[2];

			$debugStr .= 'module = <b>'.$module.'</b> config extension = <b>'.$configExt.'</b><br />';
			// if (i()->username == 'softganz') {
				// debugMsg($folders, '$folders');
			// }

			foreach ($folders as $folder) {
				$each_config_file = $folder.'/'.$configFile;
				if (!(file_exists($each_config_file) && is_file($each_config_file))) {
					$debugStr .= '* Load from '.$each_config_file.' <span style="color: red; font-weight: bold;">not found!!!.</span><br />';
					continue;
				}

				$debugStr .= '<span style="color: green;">* Load from '.$each_config_file.' <b>found!</b></span>';
				// if (i()->username == 'softganz') debugMsg('START LOAD CONFIG : '.$each_config_file);
				if ($configExt === 'php') {
					// $debugStr .= 'Load from php';
					include($each_config_file);
					if (isset($cfg) && is_array($cfg)) {
						$configArray = $cfg;
					}
					break;
				} else if ($configExt === 'json') {
					$jsonString = file_get_contents($each_config_file);

					// debugMsg($configFile.' => '.$each_config_file);

					// Merge json config file to current config
					// Module json config file was load after database, so it less important than database
					// Current cfg($module) is config from file conf.???.php and config from table variable
					$jsonTest = json_decode($jsonString);
					if (!(isset($jsonTest) && is_object($jsonTest))) {
						$debugStr .= ' <span style="color: red; font-weight: bold;">json error!!!!!!.</span><br />';
					}

					$debugStr .= ' <span style="color: green; font-weight: bold;">json completed!.</span><br />';
					$debugStr .= '<pre>jsonString = '.$jsonString.'</pre>';
					$debugStr .= '<pre>decode = '.print_r($jsonTest,1).'</pre>';
					$debugStr .= '<br />';


					if ($module === 'core') {
						$debugStr .= 'MERGE CORE CONFIG';
						$jsonValue = SG\json_decode($jsonTest, cfg());
						$debugStr .= ('<pre>'.htmlspecialchars(print_r($jsonValue,1)).'</pre>');
						cfg((Array) $jsonValue);
						// $cfg = cfg();
						// array_walk_recursive($cfg, '__htmlspecialchars');
						// debugMsg($cfg, 'coreCfg');
						// $jsonValue = cfg();
						// foreach ($jsonTest as $key => $value) {
						// 	$jsonValue = SG\json_decode($value, $jsonValue);
						// }
					} else {
						$jsonValue = SG\json_decode($jsonTest, cfg($module));

						// if (i()->username == 'softganz') {
						// 	debugMsg('LOAD JSON : '.$each_config_file);
						// 	debugMsg(\json_decode($jsonString),'\json_decode($jsonString)');
						// 	debugMsg($jsonValue, '$jsonValue');
						// 	debugMsg(cfg($module), '$cfg['.$module.']');
						// }

						if (isset($jsonValue) && is_object($jsonValue)) {
							cfg($module, $jsonValue);
							// $debugStr .= ' <span style="color: red; font-weight: bold;">complete!!!!!!.</span>';
						} else {
							// $debugStr .= ' <span style="color: red; font-weight: bold;">error!!!!!!.</span>';
						}
					}
				}
			}
		} else {
			// $debugStr .= '<b>START LOAD CONFIG :: '.$configFile.' :: </b> from '.implode(';', (Array) $folders).'<br />';
		}

		if (i() && i()->ok && debug('config')) debugMsg($debugStr);

		// Add each config key to cfg(), except conf.[module].json
		foreach ($configArray as $configKey => $configValue) {
			unset($jsonValue);
			if (is_array($configValue)) {
				cfg($configKey,$configValue);
			} else if (is_string($configValue) && preg_match('/^\{/', trim($configValue))) {
				$jsonValue = \SG\json_decode($configValue, cfg($configKey));
				//debugMsg($jsonValue, '$jsonValue['.$configKey.']');
				if (isset($jsonValue) && is_object($jsonValue)) {
					cfg($configKey, $jsonValue);
				}
			} else if (is_string($configValue) && preg_match('/^\[/', trim($configValue))) {
				$jsonValue = (Array) SG\json_decode($configValue, cfg($configKey));
				//debugMsg($jsonValue, '$jsonValue['.$configKey.']');
				if (isset($jsonValue) && is_array($jsonValue)) {
					cfg($configKey, $jsonValue);
				}
			} else {
				cfg($configKey, $configValue);
			}
		}
	}

	/**
	* Find and load template
	* @param String $filename
	* @param String ext_folder Each folder seperate by ;
	* @param Boolean $show_result
	* @return String Result from template file
	*/
	static function loadTemplate($filename = NULL, $ext_folder = NULL, $show_result = true) {
		$template_file = self::getTemplate($filename, $ext_folder);
		if (!$template_file) return;
		$ret = '';
		if ($show_result) {
			require($template_file);
		} else {
			ob_start();
			require($template_file);
			$ret = ob_get_contents();
			ob_end_clean();
		}
		return $ret;
	}

	/**
	* Load Resource File and return array
	* @param String $packageName exp [form/]module[.submodule].method
	* @param Boolean $debugResourceFile
	* @return Mixed
	*/
	static function loadResourceFile($packageName, $debugResourceFile = false) {
		static $loadCount = 0;
		static $debugFunc = [];
		static $loadFiles = [];
		static $loadCfg = [];

		$srcPackageName = $packageName;
		$resourceFileToLoad = '';
		$found = false;
		$resourceType = '';
		$resultContent = NULL;
		$coreFolder = rtrim(_CORE_FOLDER,'/');
		$mainFolder = '';
		$paths = [];
		$fileName = '';
		$funcName = NULL;
    $className = NULL;
		$isDebugable = true;
		$debugLoadfile = debug('load') || $debugResourceFile;
		$fixFolders = ['widget' => 'widgets', 'model' => 'models', 'api' => 'api'];
		$template = cfg('template');
		$caller = get_caller(__FUNCTION__);

		if (cfg('template.add')) {
			$template = cfg('template.add').';'.$template;
		}

		$loadCount++;

		// debugMsg('$packageName = '.$packageName);

		// Remove .php extension
		$packageName = preg_replace('/\.php$/i', '', $packageName);

		// debugMsg('$packageName = '.$packageName);

		if (preg_match('/^(widget|page|api|manifest|module|r|view|on|)\.(.*)/i', $packageName, $out)) {
			// Begin with keyword and follow by .
			list(, $resourceType, $package) = $out;
			$request = explode('.',$package);
			$module = $request[0];
		} else if (preg_match('/^(.*)\:(.*)/', $packageName, $out)) {
			// Begin with keyword and follow by :
			// Have / (folder) in package name
			list(, $resourceType, $package) = $out;
			if (preg_match('/^(.*)\/(.*)/', $package, $out)) {
				list(, $packageFolder, $package) = $out;
			}
			$request = explode('.',$package);
			if ($resourceType === 'asset') {
				$module = $packageFolder;
			} else {
				$module = $request[0];
			}
			// if ($debugLoadfile) {
			// 	debugMsg('resourceType = <b>'.$resourceType.'</b>, packageName = <b>'.$package.'</b>, packageFolder = <b>'.$packageFolder.'</b>');
			// 	debugMsg($request, '$request');
			// }
		} else {
			return false;
		}

		$subModule = isset($request[1]) ? $request[1] : NULL;
		$actionModule = isset($request[2]) && is_string($request[2]) ? $request[2] : NULL;

		// debugMsg($request, '$request');
		// debugMsg('$subModule = '.$subModule.' $actionModule = '.$actionModule);

		$loadAction = in_array($resourceType, ['asset']) ? 'content' : 'include';


		$debugStr = '<div>Debug of '.__FUNCTION__.'() #'.$loadCount.' in <b>'
			. (isset($caller['class']) ? $caller['class'] : '')
			. (isset($caller['type']) ? $caller['type'] : '')
			. (isset($caller['function']) ? $caller['function'].'(\''.$srcPackageName.'\')' : '').'</b> '
			. 'from '.$caller['file'].' line '.$caller['line'].' '
			. '<a href="#" onclick="$(this).next().toggle();return false;">Caller</a>'
			. '<div class="loadfunction__detail -hidden" style="border: 1px #ccc solid; margin: 0 8px; padding: 8px; border-radius: 8px;">'
			. (isset($caller['from']) ? 'Call from '.$caller['from'] : '')
			. '</div>'._NL
			. '</div>'._NL
			. 'Start load <b>'.($resourceType?' Resource '.strtoupper($resourceType).'':'Page').'</b> from package <b>'.$package.'</b> '._NL
			. 'module = <b>'.$module.'</b>'.($subModule ? ' , Sub Module = <b>'.$subModule.'</b>' : '').'<br />'._NL;

		$importOnly = $caller['function'] === 'import';
		if (is_dir('./modules/'.$module)) $mainFolder .= '.;';
		$mainFolder .= $coreFolder;

		// Add template tp path
		if ($template && in_array($resourceType, ['widget', 'model', 'page', 'api', 'asset', /* @deprecated */ 'r', 'view', 'on'])) {
			foreach (explode(';', $template) as $item) {
				$item = trim($item);

				// debugMsg($coreFolder.'/modules/'.$module.'/template/'.$item.(is_dir($coreFolder.'/modules/'.$module.'/template/'.$item) ? ' Exists' : ' Not Exists'));

				if (in_array($resourceType, ['widget', 'model', 'api'])) {
					if ($subModule) $paths[] = 'modules/'.$module.'/template/'.$item.'/'.$subModule.'/'.$fixFolders[$resourceType];
					$paths[] = 'modules/'.$module.'/template/'.$item.'/models';
				} else if (in_array($resourceType, ['page'])) {
					if ($subModule && $actionModule) {
						$paths[] = 'modules/'.$module.'/template/'.$item.'/'.$subModule.'/'.$actionModule;
					}
				}
				if ($subModule) $paths[] = 'modules/'.$module.'/template/'.$item.'/'.$subModule;
				$paths[] = 'modules/'.$module.'/template/'.$item;
			}
		}

		switch ($resourceType) {
			case 'manifest' : // Manifest Resource
				$fileName = 'manifest.';
				if (is_dir(_CORE_FOLDER.'/modules/'.$module)) {
					$paths[] = 'modules/'.$module;
				} else if (is_dir(_CORE_MODULE_FOLDER.'/'.$module)) {
					$paths[] = 'core/modules/'.$module;
				} else {
					$paths[] = 'core/modules/system';
				}
				break;

			case 'module' :
				$fileName = 'module.';
				$funcName = 'module_';
				if (is_dir(_CORE_FOLDER.'/modules/'.$module)) {
					$paths[] = 'modules/'.$module;
				} else if (is_dir(_CORE_MODULE_FOLDER.'/'.$module)) {
					$paths[] = 'core/modules/'.$module;
				} else {
					$paths[] = 'core/modules/system';
				}
				break;

			case 'widget' : // Widget Resource
				$fileName = 'widget.';
				if ($subModule) $paths[] = 'modules/'.$module.'/'.$subModule.'/widgets';
				if ($subModule) $paths[] = 'modules/'.$module.'/'.$subModule;
				$paths[] = 'modules/'.$module.'/widgets';
				if (is_dir(_CORE_MODULE_FOLDER.'/'.$module)) {
					if ($subModule) $paths[] = 'core/modules/'.$module.'/'.$subModule;
					$paths[] = 'core/modules/'.$module.'/widgets';
				} else {
					$paths[] = 'core/widgets';
				}
				// debugMsg($paths, '$paths');
				break;

			case 'model' : // Model Resource
				$fileName = 'model.';
				// if ($subModule && $template) $paths[] = 'modules/'.$module.'/template/'.$template.'/'.$subModule.'/models';
				# $paths[] = 'modules/'.$module.'/template/'.$template;
				if ($subModule) $paths[] = 'modules/'.$module.'/'.$subModule.'/models';
				$paths[] = 'modules/'.$module.'/models';
				if (is_dir(_CORE_MODULE_FOLDER.'/'.$module)) {
					if ($subModule) $paths[] = 'core/modules/'.$module.'/'.$subModule.'/models';
					$paths[] = 'core/modules/'.$module.'/models';
				}
				$paths[] = 'core/models';
				break;

			case 'api' : // Page Resource
				$fileName = 'api.';
				$className = implode('', array_map(function ($v) {return strtoupper(substr($v, 0,1)).strtolower(substr($v,1));},$request)).'Api';

				if ($subModule && $template) $paths[] = 'modules/'.$module.'/template/'.$template.'/'.$subModule.'/api';
				$paths[] = 'modules/'.$module.'/template/'.$template;
				if ($subModule) {
					$paths[] = 'modules/'.$module.'/'.$subModule.'/api';
					if (isset($request[2]) && is_string($request[2])) {
						$paths[] = 'modules/'.$module.'/'.$subModule.'/'.$request[2];
					}
					$paths[] = 'modules/'.$module.'/'.$subModule;
				}
				$paths[] = 'modules/'.$module.'/api';
				if ($subModule) {
					$paths[] = 'core/modules/'.$module.'/'.$subModule;
				}
				$paths[] = 'core/modules/'.$module.'/api';
				$paths[] = 'core/modules/api';
				break;

			case 'page' : // Page Resource
				$fileName = 'page.';
				$funcName = ''; // for page function of old version
				$className = implode('', array_map(function ($v) {return strtoupper(substr($v, 0,1)).strtolower(substr($v,1));},$request));

				if ($subModule) {
					if ($actionModule) $paths[] = 'modules/'.$module.'/'.$subModule.'/'.$request[2];
					$paths[] = 'modules/'.$module.'/'.$subModule;
				}
				$paths[] = 'modules/'.$module.'/default';
				$paths[] = 'modules/'.$module;

				// Is in core module
				if (is_dir(_CORE_MODULE_FOLDER.'/'.$module)) {
					if ($subModule) {
						if ($actionModule) $paths[] = 'core/modules/'.$module.'/'.$subModule.'/'.$request[2];
						$paths[] = 'core/modules/'.$module.'/'.$subModule;
					}
					$paths[] = 'core/modules/'.$module.'/default';
					$paths[] = 'core/modules/'.$module;
				} else {
					$paths[] = 'core/modules/system';
				}
				break;

			case 'package':
				$paths[] = 'modules/'.$packageFolder;
				$paths[] = 'core/'.$packageFolder;
				break;

			case 'asset':
				$paths[] = 'modules/'.$packageFolder.'/assets';
				$paths[] = 'core/'.$packageFolder.'/assets';
				break;

			// @deprecated
			case 'r' : // Model Resource
				$fileName = 'r.';
				$funcName = 'r_';
				$paths[] = 'modules/'.$module.'/r';
				if (is_dir(_CORE_MODULE_FOLDER.'/'.$module)) {
					$paths[] = 'core/modules/'.$module.'/r';
				}
				$paths[] = 'core/models';
				break;

			// @deprecated
			case 'view' : // View Resource
				$fileName = 'view.';
				$funcName = 'view_';
				$className = 'View'.implode('', array_map(function ($v) {return strtoupper(substr($v, 0,1)).strtolower(substr($v,1));},$request));
				//$paths[]='modules/'.$module;
				if ($subModule) $paths[] = 'modules/'.$module.'/'.$subModule;
				$paths[] = 'modules/'.$module.'/default';
				$paths[] = 'modules/'.$module;
				if (is_dir(_CORE_MODULE_FOLDER.'/'.$module)) {
					if ($subModule) $paths[] = 'core/modules/'.$module.'/'.$subModule;
					$paths[] = 'core/modules/'.$module.'/default';
					$paths[] = 'core/modules/'.$module;
				}
				$paths[] = 'core/view';
				break;

			// @deprecated
			case 'on' : // Event Resource
				$fileName = 'on.';
				$funcName = 'on_';
				$paths[] = 'modules/'.$module.'/r';
				$paths[] = 'core/modules/'.$module.'/r';
				$paths[] = 'core/models';
				break;
		}

		// Load module configuration file in json format, if nerver loaded
		if (!in_array($module, $loadCfg)) {
			$cfgPaths = [$coreFolder.'/modules/'.$module];
			if (file_exists($coreFolder.'/core/modules/'.$module)) $cfgPaths[] = $coreFolder.'/core/modules/'.$module;
			$cfgPaths[] = $coreFolder.'/system';
			$cfgPaths[] = './conf.d';
			$cfgPaths[] = '.';
			$cfgFileProduction = 'conf.'.$module.'.json';
			$cfgFileDevelop = 'conf.local.'.$module.'.json';

			// debugMsg('<b>START LOAD CONFIG WEB :: '.$cfgFileProduction.' :: </b> from '.implode(';',$cfgPaths));
			self::loadConfig($cfgFileProduction, $cfgPaths);
			// foreach ($cfgPaths as $path) {
			// 	debugMsg('<b>START LOAD CONFIG WEB :: '.$cfgFileProduction.' :: </b>'.$path.'/'.$cfgFileProduction);
			//  	self::loadConfig($cfgFileProduction, $path);
			// }
			// debugMsg('<b>START LOAD CONFIG LOCAL :: '.$cfgFileDevelop.' :: </b> ./'.$cfgFileDevelop);
			self::loadConfig($cfgFileDevelop, ['conf.local', '.']);
			$loadCfg[] = $module;
			// debugMsg($loadCfg, '$loadCfg');
		}


		$fileName .= implode('.',$request);
		if (!in_array($resourceType, ['asset'])) {
			$fileName .= preg_match('/\.php$/i', $package) ? '' : '.php';
		}

		if(!is_null($funcName)) {
			$funcName .= implode('_',$request);
		}

		if ((($funcName && function_exists($funcName)) || ($className && class_exists($className))) && array_key_exists($packageName, $loadFiles)) {
			// Resource file was loaded
			$found = true;
			$debugStr .= '<font color="green">Function '.$funcName.'() was already load.</font><br />'._NL;
		} else {
			// Load resource file
			if (cfg('template.add')) $debugStr .= 'template.add='.cfg('template.add').'<br />';

			$debugStr .= '<div>Request <b>'.implode(($resourceType == 'page' ? '/' : '.'),$request).'</b> and load filename <b>'.$fileName.'</b>'
				. ($funcName ? ' then call function <b>'.$funcName.'() </b>' : '')
				. '</div>'._NL;

			$debugStr .= 'Main folder = <b>'.$mainFolder.'</b><br />';
			$debugStr .= 'Path = <b>'.implode('; ',$paths).'</b><br />'._NL;

			foreach (explode(';', $mainFolder) as $mainFolderItem) {
				// debugMsg('$mainFolderItem = '.$mainFolderItem.' $paths = '.implode(';',$paths));
				foreach ($paths as $path) {
					$folderName = $mainFolderItem.'/'.$path;
					$isPathExists = is_dir($folderName);
					$debugStr .= 'Locate file in folder '.$folderName.' <b>(folder '.($isPathExists ? 'exists' : 'not exists').')</b><br />'._NL;
					if (!$isPathExists) continue;

					$resourceFileToLoad = $folderName.'/'.$fileName;
					// debugMsg('$resourceFileToLoad = '.$resourceFileToLoad);

					// Check function exists, if not set function return found
					if (file_exists($resourceFileToLoad)) {
						$loadFiles[$packageName] = $resourceFileToLoad;
						if ($loadAction == 'content') {
							$resultContent = file_get_contents($resourceFileToLoad);
							$found = true;
							$debugStr .= '<span style="color:green;">Found and get file content <b>'.$resourceFileToLoad.'</b></span><br />'._NL;
						} else {
							require_once($resourceFileToLoad);
							// debugMsg($loadFiles,'$loadFiles');

							// Set Debug Load Resource from $debugLoadResource in resource file
							// If you don't want to show in debug mode, add $debugLoadResource = false in top of resource file
							$debugFunc[$funcName] = $isDebugable = !(isset($debugLoadResource) && $debugLoadResource === false);
							$debugStr .= '<span style="color:green;">Found and load file <b>'.$resourceFileToLoad.'</b></span><br />'._NL;

							// Check function exists, if not set function return found
							if ($importOnly) {
								$found = true;
							} else if ($className && class_exists($className)) {
								$debugStr .= '<span style="color:green;">Found Execute class <b>'.$className.'()</b>.</span><br />'._NL;
								$found = true;
								break;
							} else if ($funcName && function_exists($funcName)) {
								$debugStr .= '<span style="color:green;">Found Execute function <b>'.$funcName.'()</b>.</span><br />'._NL;
								$found = true;
								break;
							} else if ($funcName || $className) {
								// If has $funcName or $className but not found class or function
								// Then continue load next file
							} else {
								$found = true;
							}
						}
					} else {
						$resourceFileToLoad = '';
					}
					if ($found) break;
				}
				if ($found) break;
			}
		}

		// $debugStr .= print_o(debug_backtrace(), '$backtrace');


		if ($debugLoadfile && ($isDebugable || debug('force'))) debugMsg($debugStr);

		if ($className && class_exists($className)) {
			return [$className, $found, $resourceFileToLoad, $resourceType];
		} else {
			return [$funcName, $found, $resourceFileToLoad, $resourceType, $resultContent];
		}
	}

	/**
	* Load widget request from tag <div class="widget" ></div>
	*
	* @param String $name , widget-request , widget-addons
	* @param Object $para
	* @return String
	*/
	static function loadWidget($name, $para) {
		static $lists = [];
		static $folders = [];
		static $counter = 0;

		$result = '';
		// $result.='name='.$name.'<br />'.print_o($para,'$para');

		if (!empty($para->{'widget-request'})) {
			$wpara = '';
			foreach ($para as $k => $v) {
				if (preg_match('/widget-|style/',$k)) continue;
				list(,$dk) = explode('-',$k);
				if ($dk) $wpara .= '/'.$dk.'/'.$v;
			}
			//$result.='gadget #'.(++$counter).' para='.($para->{'widget-request'}).$wpara;
			$widget_result = gadget($para->{'widget-request'}.$wpara);
		} else if (!empty($para->{'data-url'})) {
			// Load widget from page url
			list($module) = explode('/',$para->{'data-url'});
			$pageName = str_replace('/', '.', $para->{'data-url'});

			$paraArgs = [];
			foreach ($para as $k => $v) {
				if ($k == 'data-url') continue;
				if (preg_match('/data-para-/', $k) && $v != '') {
					$paraArgs[] = $v;
				}
			}
			R::Manifest($module);
			$widget_result = R::PageWidget($pageName, $paraArgs);
			// debugMsg('widget module = '.$module.' $pageName = '.$pageName);
			// $widget_result .= print_o($paraArgs,'$paraArgs');
			// debugMsg($widget_result,'$widget_result');
		} else {
			// Load widget from filename widget.name.php function=widget_name
			if (empty($folders)) {
				$folders = cfg('theme.template');
				$folders[] = _CORE_FOLDER.'/core/widgets/';
			}

			$is_debug = debug('widget');

			if ($is_debug) debugMsg('<b>Start load widget '.$name.'</b> from widget folders '.implode(' , ',$folders));

			foreach ($folders as $folder) {
				$filename = $folder.'widget.'.$name.'.php';
				if (file_exists($filename)) {
					$lists[] = $name;
					if ($is_debug) debugMsg('<em style="color:#f60;font-weight:bold;">Load widget file '.$filename.' found</em>');
					include_once($filename);
					break;
				}
				if ($is_debug) debugMsg('Load widget file '.$filename.' not found.');
			}

			$func_name = 'widget_'.$name;
			if (function_exists($func_name)) {
				list($widget_result) = call_user_func($func_name, $para);
			}
			// $widget_result = 'Widget '.$widget_result;
		}

		if (is_object($widget_result) && method_exists($widget_result, 'build')) {
			unset($widget_result->appBar);
			$widget_result = $widget_result->build();
		}

		// Result of $widget_result must be string
		$widget_result = trim($widget_result);

		if (!empty($para->{'data-header'}) && !in_array(strtolower($para->{'option-header'}), ['0','no'])) {
			$header = '<h2>'.($para->{'data-header-url'}?'<a href="'.$para->{'data-header-url'}.'">':'').'<span>'.\SG\getFirst($para->{'data-header'},$para->id).'</span>'.($para->{'data-header-url'}?'</a>':'').'</h2>'._NL;
		}

		if (trim($para->{'data-option-replace'}) == 'yes') {
			$result .= $widget_result;
		} else {
			$result .= _NL.'<!-- Start widget '.$name.' -->'._NL
				. $header
				. ($widget_result ? '<div class="widget-content container">'._NL. $widget_result._NL._NL. '</div>'._NL : '')
				. '<!-- End of widget '.$name.' -->'._NL;
		}
		return $result;
	}

	/**
	* Load extension file
	* @param String $name
	*/
	static function loadExtension($name) {
		static $lists = [];
		static $folders = [];
		if (in_array($name,$lists)) return;
		if (empty($folders)) {
			$folders=cfg('theme.template');
			$folders[]=cfg('core.location').'extensions/';
		}
		$is_debug=debug('extension');
		if ($is_debug) echo 'load extension '.$name.'<br />';
		if ($is_debug) print_o($folders,'extension folders',1);
		foreach ($folders as $folder) {
			$file=$folder.$name.'.extension.php';
			if ($is_debug) echo 'load extension file '.$file;
			if (file_exists($file)) {
				$lists[]=$name;
				if ($is_debug) echo ' <em style="color:#f60;">found</em><br />';
				include_once($file);
				break;
			}
			if ($is_debug) echo ' not found<br />';
		}
	}

	static function processIndex($page = 'index', $text = NULL) {
		global $request_result;
		$request_result = $text;
		$result = self::loadTemplate($page, NULL, false);
		return $result;
	}

	/**
	* Process variable and replace with value
	* @param String $html
	* @return String
	*/
	static function processVariable($html) {
		$vars = [
			'q' => q(),
			'domain' => cfg('domain'),
			'url' => cfg('url'),
			'upload_folder' => cfg('upload_folder'),
			'theme' => cfg('theme'),
			'_HOME_STICKY' => _HOME_STICKY,
		];

		// Searching textarea and pre
		preg_match_all('#\<textarea.*\>.*\<\/textarea\>#Uis', $html, $foundTxt);
		preg_match_all('#\<pre.*\>.*\<\/pre\>#Uis', $html, $foundPre);

		// replacing both with <textarea>$index</textarea> / <pre>$index</pre>
		$html=str_replace($foundTxt[0], array_map(function($el){ return '<textarea>'.$el.'</textarea>'; }, array_keys($foundTxt[0])), $html);
		$html=str_replace($foundPre[0], array_map(function($el){ return '<pre>'.$el.'</pre>'; }, array_keys($foundPre[0])), $html);

		// Replace {$var} with $vars[var]
		$html=preg_replace_callback(
			'#{\$(.*?)}#',
			function($match) use ($vars){
				return $vars[$match[1]];
			},
			$html
		);

		// Replace {url:} with url()
		$html=preg_replace_callback(
			'#{(url\:)(.*?)}#',
			function($match){
				return url($match[2]);
			},
			$html
		);

		// Replace {tr:} with url()
		$html=preg_replace_callback(
			'#{(tr\:)(.*?)}#',
			function($match){
				$para=preg_split('/,/', $match[2]);
				return tr($para[0],$para[1]);
			},
			$html
		);

		// Replacing back with content
		$html=str_replace(array_map(function($el){ return '<textarea>'.$el.'</textarea>'; }, array_keys($foundTxt[0])), $foundTxt[0], $html);
		$html=str_replace(array_map(function($el){ return '<pre>'.$el.'</pre>'; }, array_keys($foundPre[0])), $foundPre[0], $html);
		return $html;
	}

	/*
	* Process Setting
	*
	* https://domain.com?setting:[app[=yes]][theme[=name]]
	* https://domain.com?options:option1[,option2[,option3[...]]]
	*/
	static function processSetting(&$R) {
		$scriptName = str_replace('/', '.', ltrim($_SERVER['SCRIPT_NAME'],'/'));
		$cookie_id = substr($scriptName, strpos($scriptName, '.') + 1, strpos($scriptName, '.',strpos($scriptName, '.')+1) - strpos($scriptName, '.') - 1); // folder/domain.com/index.php => domain
		$cookieKey = _ON_LOCAL ? 'setting:'.$cookie_id : 'setting';
		$setting = isset($_COOKIE[$cookieKey]) ? json_decode($_COOKIE[$cookieKey]) : new \stdClass();
		$hasSetting = false;
		$hasOptions = false;

		//echo 'ID = '.$cookie_id. ' = '.$cookieKey.' VALUE = '.$_COOKIE[$cookieKey].'<br />';

		foreach (array_keys($_GET) as $key) {
			if (preg_match('/^options\:/', $key)) $hasOptions = $key;
		}
		if ($hasOptions) {
			foreach (explode(',', substr($hasOptions, 8)) as $value) {
			 	$R->option($value, $value);
			 }
		}

		if (isset($_GET['setting:'])) return R::setting($setting);

		foreach (array_keys($_GET) as $key) {
			if (preg_match('/^setting\:/', $key)) {$hasSetting = true;}
		}
		if (!$hasSetting) return R::setting($setting);

		if (isset($_GET['setting:app'])) {
			if (($getSettingApp = $_GET['setting:app']) && $getSettingApp != '{}') {
				$setting->app = \SG\json_decode($getSettingApp);
			} else {
				unset($setting->app);
			}
		}

		$setting->theme = isset($_GET['setting:theme']) ? $_GET['setting:theme'] : NULL;
		if (empty($setting->theme)) unset($setting->theme);

		$settingJson = trim(json_encode($setting));
		if (in_array($settingJson, ['{}', 'null'])) {
			setcookie(
				$cookieKey,
				'',
				time()-3600,
				is_null(cfg('cookie.path') ? '' : cfg('cookie.path')),
				is_null(cfg('cookie.domain') ? '' : cfg('cookie.domain'))
			);
			unset($_COOKIE[$cookieKey]);
		} else {
			setcookie(
				$cookieKey,
				$settingJson,
				time()+365*24*60*60,
				is_null(cfg('cookie.path') ? '' : cfg('cookie.path')),
				is_null(cfg('cookie.domain') ? '' : cfg('cookie.domain'))
			);
		}
		return R::setting($setting);
	}

	/**
	* Do module method from request menu item
	* @param Array $menu
	* @return String
	*/
	static function processMenu($menu, &$buildMethod = 'build', $prefix = 'page') {
		$pageClass = NULL;
		$module = $menu['call']['module'];
		$auth_code = $menu['access'];
		$is_auth = user_access($auth_code);
		$debugLoadfile = debug('load');

		// Create self object
		if (class_exists($module)) {
			$pageClass = new $module($module);
			$pageClass->module = $module;
		} else {
			$pageClass = new Module($module);
			$pageClass->module = $module;
			cfg('page_id', $module);
		}

		R::Module($module.'.init', $pageClass);

		$options = \SG\json_decode($menu['options']);
		$verify = isset($options->verify) ? R::Model($options->verify,$pageClass) : true;

		if ($verify === false) {
			http_response_code(_HTTP_ERROR_FORBIDDEN);
			return [$pageClass, true, message('error', 'Access denied', NULL)];
		} else if (is_string($verify)) {
			return [$pageClass, true, $verify];
		} else if ($is_auth === false) {
			http_response_code(_HTTP_ERROR_FORBIDDEN);
			return [$pageClass, true, message('error', 'Access denied', NULL, $options->signform)];
		}

		$menuArgs = array_merge([$module], is_array($menu['call']['arg']) ? $menu['call']['arg'] : [] );

		// Load request from package function file page.package.method[.method].php
		$funcName = $funcArg = [];
		foreach ($menuArgs as $value) {
			if (is_numeric($value) || $value == '*' || preg_match('/^[0-9]/', $value)) break;
			$funcName[] = $value;
		}

		// Find method in function name
		if (preg_match('/([\w].*)(\.\.)([\w\.].*)$/', end($funcName), $out)) {
			$funcName[count($funcName) - 1] = $out[1]; // Last argument
			$buildMethod = $out[3]; // After method separator
			$buildMethod = preg_replace_callback('/\.(\w)/', function($matches) {return strtoupper($matches[1]);}, $buildMethod);
		}

		$found = false;

		do {
			$funcArg = array_slice($menuArgs, count($funcName));
			$pageFile = $prefix.'.'.implode('.', $funcName);

			if ($debugLoadfile) {
				debugMsg('<div style="color: blue;">Load Page <b>'.$pageFile.'.php</b> in self::processMenu()</div>');
			}

			$loadResult = list($retClass, $found, $filename) = self::loadResourceFile($pageFile);

			if ($debugLoadfile) {
				// debugMsg(''.($found?'Found ':'Not found ').'<b>'.$retClass.'</b> in <b>'.$pageFile.'</b><br />');
				// debugMsg($loadResult,'$loadResult');
				debugMsg('<div style="color: green;">Load Page <b>'.$pageFile.'.php</b> complete.</div>');
			}

			array_pop($funcName);
		} while (!$found && count($funcName) >= 1);

		// debugMsg($retClass);
		// debugMsg($menuArgs, '$menuArgs-after');
		// debugMsg($funcName, '$funcName');
		// debugMsg($funcArg, '$funcArg');
		// debugMsg($funcArg, '$funcArg');

		if ($found && class_exists($retClass) && method_exists($retClass, $buildMethod)) {
			$pageClassWidget = new $retClass(...$funcArg);
			// debugMsg($pageClassWidget, '$pageClassWidget');

			// Check methid is public
			if (($pageBuildReflection = new ReflectionMethod($pageClassWidget, $buildMethod))
				&& $pageBuildReflection->isPublic()
			) {
				$pageBuildWidget = $pageClassWidget->$buildMethod();
				// debugMsg($pageClassWidget, '$pageClassWidget');
				// debugMsg($pageBuildWidget, '$pageBuildWidget');
				if (isset($pageBuildWidget->exeClass)) {
					$pageClass = $pageBuildWidget->exeClass;
					$pageClass->module = $module;
				}
			} else {
				$found = false;
			}
		} else if ($found && function_exists($retClass)) {
			$pageBuildWidget = $retClass(...array_merge([$pageClass], $funcArg));
			$pageClassWidget = NULL;
		} else {
			$pageBuildWidget = NULL;
			$pageClassWidget = NULL;
		}

		return [$pageClass, $found, $pageBuildWidget, $pageClassWidget];
	}

	/**
	* Do request process from url address and return result in string
	* @return String
	*/
	static function processController($loadTemplate = true, $pageTemplate = NULL) {
		global $page,$request_time,$request_process_time;
		$request = R()->request;
		$requestResult = '';
		$requestFilePrefix = 'page';
		$isDebugProcess = debug('process');
		$process_debug = '';
		$buildMethod = 'build'; // Default build method
		$templateVar = [];

		if ($isDebugProcess) $process_debug = 'process debug of <b>'.$request.'</b> request<br />'._NL;

		self::setPageClass(q(0, 'all'));

		if (isset($GLOBALS['message'])) $requestResult .= $GLOBALS['message'];
		if (cfg('web.readonly')) $requestResult .= message('status',cfg('web.readonly_message'));

		R()->timer->start($request);

		// To view url parameter
		// echo '<p style="padding-top:86px;">$request = '.$request.'<br />$_GET<br/><pre>'.print_r($_GET,1).'</pre><br />$_REQUEST<pre>'.print_r($_REQUEST,2).'</pre></p>';

		if (is_home()) {
			// Check for splash page
		 	// Show splash if not visite site in time
			if (cfg('web.splash.time') > 0 && $splashPage = url_alias('splash') && empty($_COOKIE['splash'])) {
				cfg('page_id','splash');
				location('splash');
			}

			// Set page id to home
			page_class('module-home');

			// Show home page
			$home = cfg('web.homepage');

			if (empty($home)) {
				ob_start();
				self::loadTemplate('home');
				$requestResult .= ob_get_contents();
				ob_end_clean();
				$request = '';
			} else {
				R()->request = $request = $home;
				q($request);
				// debugMsg('$home='.$home.' $request='.$request.' q()='.q()).' cfg(page_id)='.cfg('page_id');
				$manifest = R::Manifest(q(0));
				$menu = menu($request);
			}
		} else if ($request) {
			// Load Module Manifest
			// Is API request?
			if (preg_match('/^api\/(.*)/', $request, $out)) {
				$request = $out[1];
				q($request);
				$requestFilePrefix = 'api';
			}

			if (q(0)) $manifest = R::Manifest(q(0));

			if ($url_alias = url_alias($request)) {
				// Check url alias
				$process_debug .= '<p><strong>'.$request.'</strong> is url alias of <strong>'.$url_alias->system.'</strong></p>';
				$request = $url_alias->system;
				q($request);
				$process_debug .= print_o(q(0,'all'),'$q');
				$manifest = R::Manifest(q(0));
				$menu = menu($request);
			} else if ($menu = menu($request)) {
				// debugMsg('Do request menu');
			} else {
				// Do request from R::Page
				//debugMsg('Do request from R::page');
				menu(q(0),q(0).' page',q(0),'__controller',1,true,'static');
				$menu = menu($request);
			}
		}

		if ($isDebugProcess  && $manifest) $process_debug .= 'Manifest module file : '.print_o($manifest,'$manifest').'<br />';

		// Load Page On Request
		if ($manifest[1] && $menu) { // This is a core version 4
			if ($isDebugProcess) $process_debug .= 'Load core version 4 <b>'.$request.'</b><br />';
		} else { // Page no manifest
			if ($isDebugProcess) $process_debug .= 'Load core version 4 on no manifest and no class<br />';
		}

		// Load request page from menu config
		list($pageClass, $found, $pageBuildWidget, $pageClassWidget) = self::processMenu($menu, $buildMethod, $requestFilePrefix);

		if ($found) {
			// Set splash page was show
			if (cfg('web.splash.time')) {
				setcookie('splash', true, time()+cfg('web.splash.time')*60, cfg('cookie.path'), cfg('cookie.domain')); // show splash if not visite site
			}

 			// Page function that return widget and has build method
			if (!is_object($pageClassWidget) && is_object($pageBuildWidget) && method_exists($pageBuildWidget, $buildMethod)) {
				// $pageClassWidget = new Widget(['child' => $pageBuildWidget]); // @deprecated use next line instead
				$pageClassWidget = $pageBuildWidget;
			}

			if ( (is_object($pageClassWidget) && method_exists($pageClassWidget, $buildMethod)) ) {
			    // || (is_object($pageBuildWidget) && method_exists($pageBuildWidget, $buildMethod)) ) {
				// Result is Widget Class then build widget to String
				// Case widget, Call method build()

				// debugMsg($pageClass, '$pageClass');
				// debugMsg($pageClassWidget, '$pageClassWidget');
				// debugMsg($pageBuildWidget, '$pageBuildWidget');

				// Check right to build widget
				if (is_object($pageClassWidget) && method_exists($pageClassWidget, 'rightToBuild')) {
					// debugMsg('RIGHT TO BUILD');
					$rightToBuildError = $pageClassWidget->rightToBuild();
					if (is_object($rightToBuildError)) $pageBuildWidget = $rightToBuildError;
				}

				// Build request result
				if (is_object($pageBuildWidget) && method_exists($pageBuildWidget, 'build')) {
					$requestResult = $pageBuildWidget->build();
				} else {
					$requestResult = $pageBuildWidget;
				}

				// Create App Bar
				if ($pageBuildWidget->appBar) {
					if (is_object($pageBuildWidget->appBar) && method_exists($pageBuildWidget->appBar, 'build')) {
						if ($pageBuildWidget->appBar->removeOnApp && is_object(R()->appAgent)) {
							// don't show appBar
						} else {
							$pageClass->appBarText = $pageBuildWidget->appBar->build();
						}
					} else if (is_object($pageBuildWidget->appBar->title)) {
						$pageClass->theme->toolbar = $pageBuildWidget->appBar->title;
					} else {
					}
					$templateVar['Title'] = $pageBuildWidget->appBar->title;
					$pageClass->appBar = $pageBuildWidget->appBar;
					$pageClass->sideBar = $pageBuildWidget->sideBar;
				}

				// Create Floating Action Button
				if ($pageBuildWidget->floatingActionButton) {
					$pageClass->floatingActionButton = $pageBuildWidget->floatingActionButton;
				}
			} else if (is_array($pageBuildWidget) || is_object($pageBuildWidget)) {
				// Result is array or object
				$requestResult = $pageBuildWidget;
			} else {
				// Result is String, join
				$requestResult .= $pageBuildWidget;
			}
			// debugMsg(gettype($requestResult));
			// debugMsg($requestResult, '$resourceType');

			// Generate result by content type
			if (cfg('Content-Type') == 'text/xml') {
				die(process_widget($requestResult));
			} else if (!_AJAX && is_array($requestResult) && isset($requestResult['location'])) {
				location($body['location']);
 			} else if (_HTML && (is_array($requestResult) || is_object($requestResult))) {
				die(self::processIndex('index', print_o($requestResult, '$result')));
			} else if (_HTML) {
				die(process_widget($requestResult));
			} else if (_AJAX || is_array($requestResult) || is_object($requestResult)) {
				// AJAX Call process
				// Check error result
				$ajaxResult = [];
				// debugMsg($pageBuildWidget, '$pageBuildWidget');
				if (is_object($pageBuildWidget) && $pageBuildWidget->widgetName === 'ErrorMessage') {
					// debugMsg('ErrorMessage');
					if ($pageBuildWidget->responseCode) $ajaxResult['responseCode'] = $pageBuildWidget->responseCode;
					if ($pageBuildWidget->text) $ajaxResult['text'] = $pageBuildWidget->text;
				} else if (is_object($requestResult)) {
					if ($requestResult->responseCode) $ajaxResult['responseCode'] = $requestResult->responseCode;
					if ($requestResult->text) $ajaxResult['text'] = $requestResult->text;
					$ajaxResult = $ajaxResult + (Array) $requestResult;
				} else if (is_array($requestResult)) {
					if ($requestResult['responseCode']) $ajaxResult['responseCode'] = $requestResult['responseCode'];
					if ($requestResult['text']) $ajaxResult['text'] = $requestResult['text'];
					$ajaxResult = $ajaxResult + $requestResult;
				}

				// Send error with json
				if ($ajaxResult['responseCode']) {
					sendHeader('application/json');
					http_response_code($ajaxResult['responseCode']);
					die(\SG\json_encode($ajaxResult));
				}

				if (is_array($requestResult) || is_object($requestResult)) {
					sendHeader('application/json');
					$requestResult = \SG\json_encode($requestResult);
				}

				// Show AppBar as Box Header when has appBar and boxHeader is true
				if (is_object($pageBuildWidget->appBar) && $pageBuildWidget->appBar->boxHeader) {
					$pageBuildWidget->appBar->showInBox = true;
					$requestResult = $pageBuildWidget->appBar->build() . $requestResult;
				}

				die(debugMsg().process_widget($requestResult));
			} else {
				// Do nothing and start render by below code
			}
		} else {
			// Page not found
			$requestResult .= self::pageNotFound($menu);
		}

		// Start Render Page, result is string

		// debugMsg($pageClass, '$pageClass');
		// debugMsg($pageBuildWidget, '$pageBuildWidget');
		// debugMsg($requestResult, $requestResult);

		if (is_object($pageBuildWidget) && isset($pageBuildWidget->var)) {
			$templateVar = array_merge($templateVar, (Array) $pageBuildWidget->var);
		}

		// Set title variabe for page render
		$templateVar['Title'] .= ' | '.cfg('web.title');
		$templateVar['Title'] = self::processTemplate(strip_tags($templateVar['Title']), $templateVar);
		$templateVar['Title'] = trim(trim($templateVar['Title']), '|');

		$requestTextResult = (new PageRenderWidget($pageClass, $requestResult))->build();

		// Replace widget container with associate widget
		$requestTextResult = process_widget($requestTextResult);

		R()->timer->stop($request);
		$request_time[$request] = R()->timer->get($request,5);
		$request_process_time = $GLOBALS['request_process_time']+R()->timer->get($request);


		if ($isDebugProcess) $process_debug .= print_o($menu,'$menu');
		if ($isDebugProcess) $process_debug .= print_o(q(0,'all'),'$q');

		if (debug('menu')) debugMsg(menu(),'$menu');
		if ($isDebugProcess) debugMsg($process_debug.(isset($GLOBALS['process_debug'])?print_o($GLOBALS['process_debug']):''));
		if (debug('timer')) debugMsg('Request process time : '.$request_process_time.' ms.'.print_o($request_time));
		if (debug('html')) debugMsg(htmlview($requestTextResult,'html tag'));

		if (debug('config')) {
			$cfg = cfg();
			array_walk_recursive($cfg, '__htmlspecialchars');
			debugMsg($cfg,'cfg');
		}

		// Start load template with result
		if ($pageTemplate) $page = $pageTemplate;
		else if (empty($page)) $page = 'index';

		if ($loadTemplate) {
			$webResult = self::processIndex($page, $requestTextResult);
			$webResult = self::processTemplate($webResult, $templateVar);
			echo $webResult;
		}

		return $requestTextResult;
	}

	/**
	* Process Template
	* 	- Replace {{ .Name }} with value
	* @param String $html
	* @param Array $var
	* @return String
	*/
	static function processTemplate($html, $var = []) {
		// Replace .Name in {{ }} with value of key in variable
		$html = preg_replace_callback(
			'/\{\{\s*\.([\w]*)\s*\}\}/s',
			function ($m) use ($var) {
				return isset($var[$m[1]]) ? $var[$m[1]] : $m[0];
			}, $html);
		return $html;
	}

	/**
	* Set current language
	* @param String $lang
	* @param String
	*/
	static function setLang($lang = NULL) {
		if ($lang) {
			// do nothing
		} else if (($lang = Request::all('lang')) && is_string($lang)) {
			if ($lang === 'clear') {
				setcookie('lang', NULL, time()-100, cfg('cookie.path'), cfg('cookie.domain'));
			} else {
				setcookie('lang', $lang, time()+10*365*24*60*60, cfg('cookie.path'), cfg('cookie.domain'));
			}
			//echo 'lang='.$_REQUEST['lang'].'='.post('lang').'='.$lang.'='.$_COOKIE['lang'];
			//echo cfg('cookie.path').' '.cfg('cookie.domain');
		} else if (array_key_exists('lang', $_COOKIE) && ($lang = $_COOKIE['lang'])) {
			$lang = $_COOKIE['lang'];
		}
		cfg('lang',$lang);
		// echo '<br><br><br>setLang<br>_GET = '.$_GET['lang'].'<br>_REQUEST = '.$_REQUEST['lang'].'<br>post = '.post('lang').'<br>_COOKIE = '.$_COOKIE['lang'].'<br>$lang = '.$lang;
		return $lang;
	}

	static function setPageClass($q) {
		page_class('module');
		page_class('module-'.$q[0]);

		// Remove element that value is numeric or * or leading with 0
		$subModule = array_filter($q, function($v){if (!(is_numeric($v) || $v==='*' || preg_match('/^0/', $v))) return $v;});
		// Replace . with -
		$subModule = explode('-', preg_replace('/\.{1,}/', '-', implode('-',$subModule)));
		// Get from begin to 2nd -
		$subModule = implode('-', array_slice($subModule, 0, 2));
		page_class('module-'.$subModule);
	
		if (isset($q[1])) {
			if (is_numeric($q[1])) {
				page_class('-'.preg_replace('/[\.]{1,}/','-',$q[0]).'-'.preg_replace('/[\.]{1,}/','-',$q[1]));
			 } else {
				page_class('-'.preg_replace('/[\.]{1,}/','-', $q[1]));
			 }
		}

		$callFromMobile = isMobileDevice();

		page_class($callFromMobile ? '-from-mobile -mobile-'.strtolower($callFromMobile) : '-from-desktop');

		page_class('-'.str_replace('.','-',str_replace('www.','',cfg('domain.short'))));

		// . ' class="module module-'.cfg('page_id')
		// . ' module-'.q(0, 'all', 'submodule')
		// . (q(1) ? (is_numeric(q(1)) ? ' -'.preg_replace('/[\.]{1,}/','-',q(0)).'-'.preg_replace('/[\.]{1,}/','-',q(1)) : ' -'.preg_replace('/[\.]{1,}/','-',q(1))) : '')
		// //. (preg_match('/^softganz\/app/i', $_SERVER['HTTP_USER_AGENT']) ? ' -app' : '')
		// . (q(2) && !is_numeric(q(2)) ? ' -'.preg_replace('/[\.]{1,}/','-',q(2)).' --'.preg_replace('/[\.]{1,}/','-',q(2)) : '')
		// . (q(3) && !is_numeric(q(3)) ? ' -'.preg_replace('/[\.]{1,}/','-',q(3)).' --'.preg_replace('/[\.]{1,}/','-',q(3)) : '')
		// . ' -'.str_replace('.','-',str_replace('www.','',cfg('domain.short')))
		// . '"'
	}

	static function pageNotFound($menu) {
		// Set header to no found and noacrchive when url address is load function page
		http_response_code(_HTTP_ERROR_NOT_FOUND);
		header('HTTP/1.0 404 Not Found');
		head('<meta name="robots" content="noarchive" />');

		LogModel::save([
			'module' => 'system',
			'keyword' => 'Page not found'
		]);

		return '<div class="pagenotfound">
		<h1><i class="icon -material -sg-48">report_gmailerrorred</i> Page Not Found.</h1>
		<p>ขออภัย ไม่มีหน้าเว็บนี้อยู่ในระบบ</p><p>The requested URL <b>'.$_SERVER['REQUEST_URI'].'</b> was not found on this server.</p>'
		. (user_access('access debugging program') ? '<p><strong> Load file detail</strong><br />'.print_o($menu,'$menu').'<br />' : '')
		// . 'File : <strong>'.$mainFolder.$pageFile.'</strong><br />'
		// . 'Routine : <strong>function '.$retFunc.'()</strong></p>'
		. '<hr>
		<address>copyright <a href="//'.$_SERVER['SERVER_NAME'].'">'.$_SERVER['SERVER_NAME'].'</a> Allright reserved.</address>
		</div>'._NL;
	}
}
?>