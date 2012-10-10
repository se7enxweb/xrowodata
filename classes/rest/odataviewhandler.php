<?php
use ODataProducer\Common\ClassAutoLoader;
use ODataProducer\Common\Messages;
use ODataProducer\Common\HttpStatus;
use ODataProducer\Common\ODataConstants;
use ODataProducer\Common\ODataException;
use ODataProducer\OperationContext\DataServiceHost;
use ODataProducer\Common\ServiceConfig;
use ODataProducer\OperationContext\Web\WebOperationContext;
use ODataProducer\OperationContext\Web\IncomingRequest;
use ODataProducer\OperationContext\Web\OutgoingResponse;
use ODataProducer\HttpOutput;

class odataViewHandler implements ezcMvcViewHandler
{
    protected $zoneName;
    protected $result;
    protected $variables = array();

    public function __construct( $zoneName = "odata", $templateLocation = null )
    {
        $this->zoneName = $zoneName;
    }

    public function send( $name, $value )
    {
        $this->variables[$name] = $value;
    }

    // from index.php
    public static function eZUpdateDebugSettings()
    {
        $ini = eZINI::instance();
        
        $settings = array();
        list ( $settings['debug-enabled'], $settings['debug-by-ip'], $settings['log-only'], $settings['debug-by-user'], $settings['debug-ip-list'], $logList, $settings['debug-user-list'] ) = $ini->variableMulti( 'DebugSettings', array( 
            'DebugOutput' , 
            'DebugByIP' , 
            'DebugLogOnly' , 
            'DebugByUser' , 
            'DebugIPList' , 
            'AlwaysLog' , 
            'DebugUserIDList' 
        ), array( 
            'enabled' , 
            'enabled' , 
            'disabled' , 
            'enabled' 
        ) );
        $logMap = array( 
            'notice' => eZDebug::LEVEL_NOTICE , 
            'warning' => eZDebug::LEVEL_WARNING , 
            'error' => eZDebug::LEVEL_ERROR , 
            'debug' => eZDebug::LEVEL_DEBUG , 
            'strict' => eZDebug::LEVEL_STRICT 
        );
        $settings['always-log'] = array();
        foreach ( $logMap as $name => $level )
        {
            $settings['always-log'][$level] = in_array( $name, $logList );
        }
        eZDebug::updateSettings( $settings );
    }

    public function process( $last )
    {
        self::eZUpdateDebugSettings();
        eZDebug::setHandleType( eZDebug::HANDLE_FROM_PHP );
        set_include_path( get_include_path() . PATH_SEPARATOR . './extension/xrowodata/src/ODataProducer/library' );
        
        require_once 'ODataProducer' . DIRECTORY_SEPARATOR . 'Common' . DIRECTORY_SEPARATOR . 'ClassAutoLoader.php';
        
        ClassAutoLoader::register();
        
        try
        {
            
            $this->_dataServiceHost = new DataServiceHost();
            $this->_dataServiceHost->setAbsoluteServiceUri( "http://" . eZSys::hostname() . "/api/odata/v1/ezpublish.svc/" );
            
            $reflectionClass = new \ReflectionClass( 'ezpDataService' );
            $dataService = $reflectionClass->newInstance();
            
            $interfaces = class_implements( $dataService );
            if ( array_key_exists( 'ODataProducer\IDataService', $interfaces ) )
            {
                $dataService->setHost( $this->_dataServiceHost );
                if ( array_key_exists( 'ODataProducer\IRequestHandler', $interfaces ) )
                {
                    // DataService::handleRequest will never throw an error
                    // All exception that can occur while parsing the request and
                    // serializing the result will be handled by 
                    // DataService::handleRequest
                    $dataService->handleRequest();
                }
                else
                {
                    throw new Exception( Messages::dispatcherServiceClassShouldImplementIRequestHandler() );
                }
            }
            else
            {
                throw new Exception( Messages::dispatcherServiceClassShouldImplementIDataService() );
            }
            $this->result = '';
            if ( isset( $_GET['debug'] ) )
            {
                $this->result .= "<html><body><pre>\n";
                $this->result .= htmlentities( $dataService->getHost()->getWebOperationContext()->outgoingResponse()->getStream() );
                $this->result .= "</pre>\n";
                $debug = ezpRestDebug::getInstance();
          		$this->result .= $debug->getDebug();
                $this->result .= "</body></html>\n";
            }
            else
            {
                $this->result .= $dataService->getHost()->getWebOperationContext()->outgoingResponse()->getStream();
            }
        }
        catch ( Exception $e )
        {
            eZDebug::writeError( $e->getMessage() );
            throw $e;
        }
    }

    public function __get( $name )
    {
        return $this->variables[$name];
    }

    public function __isset( $name )
    {
        return array_key_exists( $name, $this->variables );
    }

    public function getName()
    {
        return $this->zoneName;
    }

    public function getResult()
    {
        return $this->result;
    }
}