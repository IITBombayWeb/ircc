<?php

namespace Drupal\iitbcontent\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Provides a 'FacultyLookup' migrate process plugin.
 *
 * @MigrateProcessPlugin(
 *  id = "faculty_lookup"
 * )
 */
class FacultyLookup extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // Plugin logic goes here.

	$query = \Drupal::entityQuery('node')
	/*->condition('status', 1)
	->condition('changed', REQUEST_TIME, '<')*/
	//->condition('type', 'faculty_profile')
	->condition('field_emp_code', $value, '=');
	$result = $query->execute();
	//print_r($result);
	if(empty($result)) {
		$value = 0;
	} else {
		reset($result);
		$first_key = key($result);
		$value = $result[$first_key];
		//$value = 855;
	}
	//print_r($value);
	return $value;
  }

}
