<?php
require_once 'modules/pmse_Inbox/engine/PMSEPreProcessor/PMSEPreProcessor.php';
require_once 'modules/pmse_Inbox/engine/PMSEPreProcessor/PMSERequest.php';
require_once 'modules/pmse_Inbox/engine/PMSELogger.php';

class PMSEEngineRequestHandler
{
    /**
     *
     * @var type
     */
    protected $logger;

    /**
     *
     * @var type
     */
    protected $request;

    /**
     *
     * @var type
     */
    protected $preProcessor;

    public function __construct()
    {
        $this->request = new PMSERequest();
        $this->logger = PMSELogger::getInstance();
        $this->request->setType('engine');
        $this->preProcessor = PMSEPreProcessor::getInstance();
    }

    /**
     *
     * @param type $element
     * @param type $createThread
     * @param type $bean
     * @param type $externalAction
     * @param type $args
     * @return type
     */
    public function executeRequest($args = array(), $createThread = false, $bean = null, $externalAction = '')
    {
        $this->logger->info('Processing an engine request.');
        $this->logger->debug('Engine request params: ' . print_r($args));
        $this->request->setCreateThread($createThread);
        $this->request->setExternalAction($externalAction);
        $this->request->setBean($bean);
        $this->request->setArguments($args);
        $preProcessor = $this->preProcessor->getInstance();
        $response = $preProcessor->processRequest($this->request);
        return $response;
    }
}
