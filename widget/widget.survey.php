<?php
/**
 * Widget   :: Survey widget for create survey form
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2020-10-01
 * Modified :: 2026-07-29
 * Version  :: 4
 *
 * @param Object $patientInfo
 * @param Object $visitInfo
 *
 * Property
 * config {nav: "nav -icons"}
 *
 * @uses new Survey([schema, values => [], children => [form]])
 * Optional [Boolean debug]
 */

class Survey extends Widget {
	public $widgetName = 'Survey';
	public $debug = false;
	public $schema = null;
	public $values = [];

	function __construct($args = []) {
		parent::__construct($args);

		if ($this->debug) {
			debugMsg($this, 'SurveyBefor');
		}

		if ($this->schema) {
			if (is_array($this->schema)) {
				$this->schema = (object) $this->schema;
			} elseif (is_string($this->schema)) {
				$this->schema = json_decode($this->schema);
			} elseif (is_object($this->schema)) {
				$this->schema = $this->schema;
			}
		}

		if (!$this->children['form']) {
			$this->children['form'] = new Form();
		}
	}

	function addValue($key, $value) {
		$this->values[$key] = $value;
	}

	protected function renderField($inputField) {
		if (is_array($inputField)) {
			if ($this->schema->options->groupContainer) {
				$this->children['form']->addText(
					'<div class="' . $this->schema->options->groupContainer->class . '">'
				);
			}
			foreach ($inputField as $key => $value) {
				$this->renderField($value);
			}
			if ($this->schema->options->groupContainer) {
				$this->children['form']->addText('</div>');
			}
		} elseif (is_object($inputField)) {
			// debugMsg($inputField, '$inputField');
			$inputType = \SG\getFirst($inputField->type, $inputField->tag);
			if ($inputType) {
				$inputOption = null;

				// Replace data with variable
				if (
					is_string($inputField->data) &&
					substr($inputField->data, 0, 1) == '$'
				) {
					$variable = substr($inputField->data, 1);
					$inputField->data = $this->schema->vars->{$variable};
				}

				// Set input option
				if (is_string($inputField->data)) {
					$inputOption = $inputField->data;
				} elseif ($inputField->data) {
					$inputOption = [];
					foreach ($inputField->data as $key => $value) {
						//debugMsg(htmlspecialchars($value->label));
						//preg_match('/\$([a-z0-9\_]+)/i', $value->label, $match);
						//debugMsg($match, '$match');
						//$value->label = preg_replace('/\$([a-z0-9\_]+)/i', $value->label, $survey->schema->vars['{1}']);
						$value->label = preg_replace_callback(
							'/\$([a-z0-9\_]+)/i',
							function ($match) {
								return $this->schema->vars->{$match[1]};
							},
							$value->label
						);
						if ($value->name) {
							$inputOption[$value->name]->options[$value->value] =
								$value->label;
						} else {
							$inputOption[$value->value] = $value->label;
						}
					}
					//$inputOption = $inputField->data;
				}

				// Create field information and add to form
				$fieldAttr = (array) $inputField;
				$fieldAttr['type'] = $inputType;
				$fieldAttr['name'] = $inputField->varName;
				if ($inputOption) {
					$fieldAttr['options'] = $inputOption;
				}
				if (array_key_exists($inputField->name, $this->values)) {
					$fieldAttr['value'] = $this->values[$inputField->name];
				}
				unset($fieldAttr['data']);
				//debugMsg($fieldAttr, '$fieldAttr');
				$this->children['form']->addField($inputField->name, $fieldAttr);
				/*
				array(
					'type' => $inputType,
					'label' => $inputField->label,
					'name' => $inputField->varName,
					'class' => $inputField->class,
					'display' => $inputField->display,
					'value' => $this->values[$inputField->name],
					'options' => $inputOption,
					'placeholder' => $inputField->placeholder,
					'pretext' => $inputField->pretext,
					'posttext' => $inputField->posttext,
					'config' => $inputField->config,
					'container' => $inputField->container,
					//'description' => print_o($inputField, '$inputField'),
				)
				*/
			} else {
				$this->children['form']->addText($inputField->label);
			}
		}
	}

	function build() {
		if ($this->schema->formAttr) {
			foreach ($this->schema->formAttr as $key => $value) {
				$this->children['form']->attr($key, $value);
			}
		}
		$ret = '<div class="sg-survey ' . $this->config->class . '">';
		foreach ($this->schema->body as $key => $inputField) {
			$this->renderField($inputField);
		}

		if ($this->schema->submitButton) {
			$this->children['form']->addField('save', [
				'type' => 'button',
				'value' => $this->schema->submitButton->label,
				'container' => $this->schema->submitButton->container
			]);
		}

		$ret .= $this->renderChildren($this->children());

		if ($this->schema->remark) {
			$ret .= '<div class="-remark">' . $this->schema->remark . '</div>';
		}
		//$ret .= print_o($this->schema, 'schema');
		$ret .= '</div>';
		if ($this->debug) {
			debugMsg($this, 'SurveyAfter');
		}
		return $ret;
	}
} // End of class Survey
?>
