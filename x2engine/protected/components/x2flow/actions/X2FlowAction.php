<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

/**
 * Color utilities (unused)
 * 
 * @package X2CRM.components.x2flow
 */
abstract class X2FlowAction extends X2FlowItem {
	public $trigger = null;

	/**
	 * Runs the automation action with provided params.
	 * @return boolean the result of the execution
	 */
	abstract public function execute(&$params);

	/**
	 * Checks if all the config variables and runtime params are ship-shape
	 * Ignores param requirements if $params isn't provided
	 */
	public function validate(&$params=array()) {
		$paramRules = $this->paramRules();
		if(!isset($paramRules['options'],$this->config['options']))
			return false;
		
		if(isset($paramRules['modelRequired'])) {
			if(!isset($params['model']))	// model not provided when required
				return false;
			if($paramRules['modelRequired'] != 1 && $paramRules['modelRequired'] !== get_class($params['model']))	// model is not the correct type
				return false;
		}
		return $this->validateOptions($paramRules);
	}

	/* 
	 * 
	 */
	public function parseOption($name,&$params) {
		$options = &$this->config['options'];
		if(!isset($options[$name]['value']))
			return null;
		
		$type = isset($options[$name]['type'])? $options[$name]['type'] : '';
		
		return X2Flow::parseValue($options[$name]['value'],$type,$params);
	}

	/**
	 * @return mixed either a string containing the notification type for this flow's trigger, or null
	 */
	public function getNotifType() {
		if($this->trigger !== null && !empty($this->trigger->notifType))
			return $this->trigger->notifType;
		return null;
	}
	/**
	 * @return mixed either a string containing the notification type for this flow's trigger, or null
	 */
	public function getEventType() {
		if($this->trigger !== null && !empty($this->trigger->eventType))
			return $this->trigger->eventType;
		return null;
	}
	
	/**
	 * Sets model fields using the provided attributes and values.
	 * 
	 * @param CActiveRecord $model the model to set fields on
	 * @param array $attributes an associative array of attributes
	 * @param array $params the params array passed to X2Flow::trigger()
	 * @return boolean whether or not the attributes were valid and set successfully
	 * 
	 */
	public function setModelAttributes(&$model,&$attributeList,&$params) {
		foreach($attributeList as &$attr) {
			if(!isset($attr['name'],$attr['value']))
				continue;
			
			if(null !== $field = $model->getField($attr['name']))
				$model->setAttribute($attr['name'],X2Flow::parseValue($attr['value'],$field->type,$params));	// first do variable/expression evaluation, // then process with X2Fields::parseValue()
		}
		return true;
	}

	public static function getActionTypes() {
		$types = array();
		foreach(scandir(Yii::getPathOfAlias('application.components.x2flow.actions')) as $file) {
			if($file === '.' || $file === '..' || $file === 'X2FlowAction.php')
				continue;
			$class = self::create(array('type'=>substr($file,0,-4)));	// remove file extension and create instance
			if($class !== null)
				$types[get_class($class)] = $class->title;
		}
		ksort($types);
		return $types;
	}
}