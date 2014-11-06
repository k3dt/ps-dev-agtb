<?php
require_once 'psr/Log/AbstractLogger.php';
require_once 'PMSELoggerWriter.php';


class PMSELogger extends AbstractLogger
{
    /**
     * Save the instance of class
     *
     * @var
     */
    private static $instance;

    /**
     * Current minimum logging threshold
     * @var integer
     */
    private $logLevel = LogLevel::DEBUG;

    /**
     *
     * @var type 
     */
    private $logWriter;

    /**
     *
     * @var type 
     */
    private $logLevels = array(
        LogLevel::EMERGENCY => 0,
        LogLevel::ALERT     => 1,
        LogLevel::CRITICAL  => 2,
        LogLevel::ERROR     => 3,
        LogLevel::WARNING   => 4,
        LogLevel::NOTICE    => 5,
        LogLevel::INFO      => 6,
        LogLevel::DEBUG     => 7,
    );

    /**
     * Valid PHP date() format string for log timestamps
     * @var string
     */
    private $dateFormat = 'Y-m-d G:i:s.u';

    /**
     * Class constructor
     * @codeCoverageIgnore
     * @param string $logLevel
     */
    private function __construct($logLevel = LogLevel::DEBUG)
    {
        $this->logWriter = new PMSELoggerWriter();
    }
    
    /**
     * 
     * @return PMSELoggerWritter
     * @codeCoverageIgnore
     */
    public function getLogWriter()
    {
        return $this->logWriter;
    }

    /**
     * 
     * @param PMSELoggerWritter $logWriter
     * @codeCoverageIgnore
     */
    public function setLogWriter($logWriter)
    {
        $this->logWriter = $logWriter;
    }
    
    /**
     * Retrieve unique instance of the PMSELogger singleton
     * @return type
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }

        return self::$instance;
    }

    /**
     * Clone method is not allowed so has been overriden
     * @codeCoverageIgnore
     */
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

    /**
     * Sets the date format used by all instances of KLogger
     *
     * @param string $dateFormat Valid format string for date()
     * @codeCoverageIgnore
     */
    public function setDateFormat($dateFormat)
    {
        $this->dateFormat = $dateFormat;
    }

    /**
     * Sets the Log Level
     *
     * @param integer logLevel
     * @codeCoverageIgnore
     */
    public function setLogLevel($logLevel)
    {
        $this->logLevel = $logLevel;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log($level, $message, array $context = array())
    {
        if ($this->logLevels[$this->logLevel] < $this->logLevels[$level]) {
            return;
        }
        $message = $this->formatMessage($message, $context);
        $this->write($level, $message);
    }

    /**
     * 
     * @param type $level
     * @param type $message
     */
    public function write($level, $message)
    {
        $this->logWriter->log($level, $message);
    }

    /**
     * Formats the message for logging.
     *
     * @param  string $message The message to log
     * @param  array  $context The context
     * @return string
     * @codeCoverageIgnore
     */
    private function formatMessage($message, $context)
    {
        if (! empty($context)) {
            $message .= PHP_EOL.$this->indent($this->contextToString($context));
        }
        return "{$message}";
    }


    /**
     * Takes the given context and coverts it to a string.
     *
     * @param  array $context The Context
     * @return string
     * @codeCoverageIgnore
     */
    private function contextToString($context)
    {
        $export = '';
        foreach ($context as $key => $value) {
            $export .= "{$key}: ";
            $export .= preg_replace(array(
                    '/=>\s+([a-zA-Z])/im',
                    '/array\(\s+\)/im',
                    '/^  |\G  /m',
                ), array(
                    '=> $1',
                    'array()',
                    '    ',
                ), str_replace('array (', 'array(', var_export($value, true)));
            $export .= PHP_EOL;
        }
        return str_replace(array('\\\\', '\\\''), array('\\', '\''), rtrim($export));
    }

    /**
     * Indents the given string with the given indent.
     *
     * @param  string $string The string to indent
     * @param  string $indent What to use as the indent.
     * @return string
     * @codeCoverageIgnore
     */
    private function indent($string, $indent = '    ')
    {
        return $indent.str_replace("\n", "\n".$indent, $string);
    }

    /**
     * Getter method obtain log_dir property
     * @return string  log_dir value
     * @codeCoverageIgnore
     */
    public function getLogFileNameWithPath() {
        return $this->logWriter->getLogFileNameWithPath();
    }

    /**
     * 
     * @param type $message
     * @param type $params
     * @codeCoverageIgnore
     */
    public function activity($message, $params = array())
    {
        $data = new stdClass();
        $data->value = $message; //"This a message user: @[Users:seed_sally_id:Sally Bronsen] whit @[Users:seed_sarah_id:Sarah Smith] for the record: @[Leads:52f46f19-7a10-4dd5-28ed-53b4671f964d:Stephanie Plunk] end of the message.";
        $module_id = isset($params['module_id']) ? $params['module_id'] : null;
        $module_name = isset($params['module_name']) ? $params['module_name'] : null;
        if (isset($params['tags']) && !empty($params['tags']) && is_array($params['tags'])){
            $data->tags = $params['tags'];
            $i = 0;
            foreach ($params['tags'] as $value) {
                $patterns[] = "/&" . $i . "/";
                $substitutions[] = "@[{$value['module']}:{$value['id']}:{$value['name']}]";
                $i++;
            }
            $data->value = preg_replace($patterns, $substitutions, $message);
        }

        require_once 'modules/ActivityStream/Activities/Activity.php';
        $beanActivity = new Activity();
        $beanActivity->parent_id = $module_id;
        $beanActivity->parent_type = $module_name;
        $beanActivity->activity_type = 'post';
        $beanActivity->data = json_encode($data);
        $beanActivity->save();
    }
    
}