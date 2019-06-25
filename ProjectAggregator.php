<?php
namespace UIOWA\ProjectAggregator;

require_once 'vendor/autoload.php';

class ProjectAggregator extends \ExternalModules\AbstractExternalModule {
	private static $smarty;

	private static $apiUrl = APP_PATH_WEBROOT_FULL . 'api/';

	public function __construct() {
		parent::__construct();
		define("MODULE_DOCROOT", $this->getModulePath());
	}

	public function cronAggregate() {
		$this->bulkAggregate();
	}

	public function bulkAggregate($pid = null) {
		$pids = array();
		$results = array();

		if ($pid == null) {
			// get all project_ids where module is enabled
			$sql="
				SELECT s.project_id
				FROM redcap_external_modules m, redcap_external_module_settings s
				WHERE m.external_module_id = s.external_module_id
					AND s.value = 'true'
					AND m.directory_prefix = 'project_aggregator'
					AND s.`key` = 'enabled'
			";

			$result = db_query($sql);

			while($row = db_fetch_assoc($result)) {
				array_push($pids, $row['project_id']);
			}
		}
		else {
			array_push($pids, $pid);
		}

		// for each destination project
		foreach ($pids as $destinationPid) {
			$token = $this->getProjectSetting('delete-token', $destinationPid);
			$sourceProjects = $this->getSourceProjects($destinationPid, false);
			$aggregatedData = array();

			if ($token) {
				$this->deleteExistingRecords($destinationPid, $token);
			}

			foreach ($sourceProjects as $project) {
				$sourcePid = $project['project_id'];

				$newData = $this->getAggregateData($destinationPid, $sourcePid);

				// test data import and save result
				$results[$sourcePid] = \REDCap::saveData(
					$destinationPid,
					'json',
					json_encode($newData),
					'normal',
					'YMD',
					'flat',
					null,
					false,
					false,
					false
				);

				if (count($results[$sourcePid]['errors']) == 0) {
					$aggregatedData = array_merge($aggregatedData, $newData);
				}
			}

			$results['saved'] = \REDCap::saveData($destinationPid, 'json', json_encode($aggregatedData));

			echo json_encode($results);
		}
	}

	public function getAggregateData($destinationPid, $sourcePid) {
		$selectedInstruments = $this->getProjectSetting('source-project-form', $destinationPid);
		$selectedFields = $this->getProjectSetting('source-project-field', $destinationPid);
		$metadataFields = $this->getProjectSetting('source-project-metadata', $destinationPid);
		$note = $this->getProjectSetting('aggregate-note', $destinationPid);

		$formattedRecords = array();
		$metadata = array();

		$sourceFields = $this->getProjectFieldList($sourcePid, $selectedFields, $selectedInstruments);

		$records = json_decode(\REDCap::getData($sourcePid, 'json', null, $sourceFields), true);

		//get metadata
		if ($metadataFields) {
			$fieldSql = implode(', ', $metadataFields);
			$sql = "SELECT $fieldSql FROM redcap_projects WHERE project_note = \"$note\" AND project_id = $sourcePid";
			$result = db_query($sql);

			$metadata = db_fetch_assoc($result);
		}

		foreach ($records as $key => $record) {
			$record['record_id'] = $sourcePid . '-' . ($key + 1);

			if ($metadata) {
				$record = array_merge($record, $metadata);
			}

			array_push($formattedRecords, $record);
		}

		return $formattedRecords;
	}

	public function getProjectFieldList($pid, $fields, $instruments) {
		$dataDict = \REDCap::getDataDictionary(
			$pid,
			'array',
			true,
			$fields,
			$instruments
		);

		$projectFieldList = array();

		foreach ($dataDict as $field) {
			$projectFieldList[] = $field['field_name'];
		}

		return $projectFieldList;
	}

	public function getSourceProjects($modulePid, $includeCounts) {
		$note = $this->getProjectSetting('aggregate-note', $modulePid);
		$metadataFields = array_filter($this->getProjectSetting('source-project-metadata', $modulePid));
		$requiredMetadataFields = array('project_id', 'app_title');

		$projectsData = array();

		// add project_id field to list and format for SQL
		foreach ($requiredMetadataFields as $field) {
			if (!array_search($field, $metadataFields)) {
				array_unshift($metadataFields, $field);
			}
		}

		$fieldSql = implode(', ', $metadataFields);

		$sql = "SELECT $fieldSql FROM redcap_projects WHERE project_note = \"$note\" AND project_id <> $modulePid";
		$result = db_query($sql);

		while ($row = db_fetch_assoc($result)) {
			if ($includeCounts) {
				$records = json_decode(\REDCap::getData($row['project_id'], 'json', null, 'record_id'), true);
				$row['record_count'] = count($records);
			}

			$projectsData[] = $row;
		}

		return $projectsData;
	}

	public function deleteExistingRecords($modulePid, $token) {
		$recordIdField = 'record_id'; //todo
		$formattedIds = array();

		$recordIds = json_decode(\REDCap::getData($modulePid, 'json', '', $recordIdField), true);

		foreach ($recordIds as $recordId) {
			$formattedIds[] = $recordId[$recordIdField];
		}

		$this->redcapApiCall(
			array(
				'token' => $token,
				'content' => 'record',
				'action' => 'delete',
				'records' => $formattedIds
			)
		);
	}

	public function redcapApiCall($data) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::$apiUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
		$output = curl_exec($ch);

		curl_close($ch);

		return $output;
	}
}