<?php

require_once 'data/BeanFactory.php';
require_once 'clients/base/api/ModuleApi.php';

require_once 'wrappers/PMSEDynaForm.php';
require_once 'modules/pmse_Inbox/engine/PMSEEngineUtils.php';

class PMSEProjectCRUDApi extends ModuleApi
{
    public function registerApiRest() {
        return array(
            'create' => array(
                'reqType' => 'POST',
                'path' => array('pmse_Project'),
                'pathVars' => array('module'),
                'method' => 'createRecord',
                'shortHelp' => 'This method creates a new record of the specified type',
                'longHelp' => 'include/api/help/module_post_help.html',
            ),
            'retrieve' => array(
                'reqType' => 'GET',
                'path' => array('pmse_Project','?'),
                'pathVars' => array('module','record'),
                'method' => 'retrieveRecord',
                'shortHelp' => 'Returns a single record',
                'longHelp' => 'include/api/help/module_record_get_help.html',
            ),
            'update' => array(
                'reqType' => 'PUT',
                'path' => array('pmse_Project','?'),
                'pathVars' => array('module','record'),
                'method' => 'updateRecord',
                'shortHelp' => 'This method updates a record of the specified type',
                'longHelp' => 'include/api/help/module_record_put_help.html',
            ),
            'delete' => array(
                'reqType' => 'DELETE',
                'path' => array('pmse_Project','?'),
                'pathVars' => array('module','record'),
                'method' => 'deleteRecord',
                'shortHelp' => 'This method deletes a record of the specified type',
                'longHelp' => 'include/api/help/module_record_delete_help.html',
            ),
        );
    }

    public function deleteRecord($api, $args)
    {
        $this->requireArgs($args,array('module','record'));

        $projectBean = BeanFactory::getBean($args['module'], $args['record']);
        $projectBean->prj_status = 'INACTIVE';
        $projectBean->save();

        $diagramBean =  BeanFactory::getBean('pmse_BpmnDiagram')->retrieve_by_string_fields(array('prj_id'=>$args['record']));
        $diagramBean->deleted = 1;
        $diagramBean->save();

        $processBean = BeanFactory::getBean('pmse_BpmnProcess')->retrieve_by_string_fields(array('prj_id'=>$args['record']));
        $processBean->deleted = 1;
        $processBean->save();

        $processDefinitionBean = BeanFactory::getBean('pmse_BpmProcessDefinition')->retrieve_by_string_fields(array('prj_id'=>$args['record']));
        $processDefinitionBean->deleted = 1;
        $processDefinitionBean->save();

        while($relatedDepBean = BeanFactory::getBean('pmse_BpmRelatedDependency')->retrieve_by_string_fields(array('prj_id'=>$args['record'], 'deleted'=>0))) {
            $relatedDepBean->deleted = 1;
            $relatedDepBean->save();
        }
        

        $bean = $this->loadBean($api, $args, 'delete');
        $bean->mark_deleted($args['record']);

        return array('id'=>$bean->id);
    }

    protected function updateBean(SugarBean $bean, ServiceBase $api, $args)
    {
        $id = parent::updateBean($bean, $api, $args);

        //retrieve a Bean created
        if (isset($args['record']) && !empty($args['record'])) {
            $projectBean = BeanFactory::retrieveBean($args['module'],$args['record']);
        } else {
            $projectBean = $bean;
        }

        //Create a Diagram row
        $diagramBean =  BeanFactory::getBean('pmse_BpmnDiagram')->retrieve_by_string_fields(array('prj_id'=>$id));
        if (empty($diagramBean)) {
            $diagramBean = BeanFactory::newBean('pmse_BpmnDiagram');
            $diagramBean->dia_uid = PMSEEngineUtils::generateUniqueID();
        }
        $diagramBean->name = $projectBean->name;
        $diagramBean->description = $projectBean->description;
        $diagramBean->assigned_user_id = $projectBean->assigned_user_id;
        $diagramBean->prj_id = $id;
        $dia_id = $diagramBean->save();

        //Create a Process row
        $processBean = BeanFactory::getBean('pmse_BpmnProcess')->retrieve_by_string_fields(array('prj_id'=>$id));
        if (empty($processBean)) {
            $processBean = BeanFactory::newBean('pmse_BpmnProcess');
            $processBean->pro_uid = PMSEEngineUtils::generateUniqueID();
        }
        $processBean->name = $projectBean->name;
        $processBean->description = $projectBean->description;
        $processBean->assigned_user_id = $projectBean->assigned_user_id;
        $processBean->prj_id = $id;
        $processBean->dia_id = $dia_id;
        $pro_id = $processBean->save();

        //Create a ProcessDefinition row
        $processDefinitionBean = BeanFactory::getBean('pmse_BpmProcessDefinition')->retrieve_by_string_fields(array('prj_id'=>$id));
        if (empty($processDefinitionBean)) {
            $processDefinitionBean = BeanFactory::newBean('pmse_BpmProcessDefinition');
            $processDefinitionBean->id = $pro_id;
            $processDefinitionBean->new_with_id = true;
        }
        $processDefinitionBean->prj_id = $id;
        $processDefinitionBean->pro_module = $projectBean->prj_module;
        $processDefinitionBean->pro_status = $projectBean->prj_status;
        $processDefinitionBean->assigned_user_id = $projectBean->assigned_user_id;
        $processDefinitionBean->save();

        $keysArray = array('prj_id' => $id, 'pro_id' => $pro_id);
        $dynaF = BeanFactory::getBean('pmse_BpmDynaForm')->retrieve_by_string_fields(array('prj_id'=>$id, 'pro_id'=>$pro_id, 'name'=>'Default'));
        if (empty($dynaF)) {
            $editDyna = false;
        } else {
            $editDyna = true;
        }
        $dynaForm = new PMSEDynaForm();
        $dynaForm->generateDefaultDynaform($processDefinitionBean->pro_module, $keysArray, $editDyna);

        return $id;
    }
}