<?php
class ModelSaleCustomField extends Model {
	public function addCustomField($data) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "custom_field` SET type = '" . $this->db->escape($data['type']) . "', location = '" . $this->db->escape($data['location']) . "', value = '" . $this->db->escape($data['value']) . "', required = '" . (int)$data['required'] . "', sort_order = '" . (int)$data['sort_order'] . "'");
		
		$custom_field_id = $this->db->getLastId();
		
		foreach ($data['custom_field_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "custom_field_description SET custom_field_id = '" . (int)$custom_field_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
		}

		if (isset($data['custom_field_value'])) {
			foreach ($data['custom_field_value'] as $custom_field_value) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "custom_field_value SET custom_field_id = '" . (int)$custom_field_id . "', sort_order = '" . (int)$custom_field_value['sort_order'] . "'");
				
				$custom_field_value_id = $this->db->getLastId();
				
				foreach ($custom_field_value['custom_field_value_description'] as $language_id => $custom_field_value_description) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "custom_field_value_description SET custom_field_value_id = '" . (int)$custom_field_value_id . "', language_id = '" . (int)$language_id . "', custom_field_id = '" . (int)$custom_field_id . "', name = '" . $this->db->escape($custom_field_value_description['name']) . "'");
				}
			}
		}
	}
	
	public function editCustomField($custom_field_id, $data) {
		$this->db->query("UPDATE `" . DB_PREFIX . "custom_field` SET type = '" . $this->db->escape($data['type']) . "', location = '" . $this->db->escape($data['location']) . "', value = '" . $this->db->escape($data['value']) . "', required = '" . (int)$data['required'] . "', sort_order = '" . (int)$data['sort_order'] . "' WHERE custom_field_id = '" . (int)$custom_field_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "custom_field_description WHERE custom_field_id = '" . (int)$custom_field_id . "'");

		foreach ($data['custom_field_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "custom_field_description SET custom_field_id = '" . (int)$custom_field_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
		}
				
		$this->db->query("DELETE FROM " . DB_PREFIX . "custom_field_value WHERE custom_field_id = '" . (int)$custom_field_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "custom_field_value_description WHERE custom_field_id = '" . (int)$custom_field_id . "'");
		
		if (isset($data['custom_field_value'])) {
			foreach ($data['custom_field_value'] as $custom_field_value) {
				if ($custom_field_value['option_value_id']) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "custom_field_value SET custom_field_value_id = '" . (int)$custom_field_value['custom_field_value_id'] . "', custom_field_id = '" . (int)$custom_field_id . "', image = '" . $this->db->escape(html_entity_decode($custom_field_value['image'], ENT_QUOTES, 'UTF-8')) . "', sort_order = '" . (int)$custom_field_value['sort_order'] . "'");
				} else {
					$this->db->query("INSERT INTO " . DB_PREFIX . "custom_field_value SET custom_field_id = '" . (int)$custom_field_id . "', sort_order = '" . (int)$custom_field_value['sort_order'] . "'");
				}
				
				$custom_field_value_id = $this->db->getLastId();
				
				foreach ($custom_field_value['custom_field_value_description'] as $language_id => $custom_field_value_description) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "custom_field_value_description SET custom_field_value_id = '" . (int)$custom_field_value_id . "', language_id = '" . (int)$language_id . "', custom_field_id = '" . (int)$custom_field_id . "', name = '" . $this->db->escape($custom_field_value_description['name']) . "'");
				}
			}
		}
	}
	
	public function deleteCustomField($custom_field_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "custom_field` WHERE custom_field_id = '" . (int)$custom_field_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "custom_field_description` WHERE custom_field_id = '" . (int)$custom_field_id . "'");	
		$this->db->query("DELETE FROM `" . DB_PREFIX . "custom_field_value` WHERE custom_field_id = '" . (int)$custom_field_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "custom_field_value_description` WHERE custom_field_id = '" . (int)$custom_field_id . "'");
	}
	
	public function getCustomField($custom_field_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "custom_field` o LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE o.option_id = '" . (int)$option_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "'");
		
		return $query->row;
	}
		
	public function getCustomFields($data = array()) {
		$sql = "SELECT * FROM `" . DB_PREFIX . "option` o LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE od.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		
		if (isset($data['filter_name']) && !is_null($data['filter_name'])) {
			$sql .= " AND od.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sort_data = array(
			'od.name',
			'o.type',
			'o.sort_order'
		);	
		
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];	
		} else {
			$sql .= " ORDER BY od.name";	
		}
		
		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}
		
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}					

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}	
		
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}	
		
		$query = $this->db->query($sql);

		return $query->rows;
	}
	
	public function getCustomFieldDescriptions($option_id) {
		$option_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "option_description WHERE option_id = '" . (int)$option_id . "'");
				
		foreach ($query->rows as $result) {
			$option_data[$result['language_id']] = array('name' => $result['name']);
		}
		
		return $option_data;
	}
	
	public function getCustomFieldValue($option_value_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "option_value ov LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE ov.option_value_id = '" . (int)$option_value_id . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
		
		return $query->row;
	}
	
	public function getCustomFieldValues($option_id) {
		$option_value_data = array();
		
		$option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "option_value ov LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE ov.option_id = '" . (int)$option_id . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY ov.sort_order ASC");
				
		foreach ($option_value_query->rows as $option_value) {
			$option_value_data[] = array(
				'option_value_id' => $option_value['option_value_id'],
				'name'            => $option_value['name'],
				'image'           => $option_value['image'],
				'sort_order'      => $option_value['sort_order']
			);
		}
		
		return $option_value_data;
	}
	
	public function getCustomFieldValueDescriptions($option_id) {
		$option_value_data = array();
		
		$option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "option_value WHERE option_id = '" . (int)$option_id . "'");
				
		foreach ($option_value_query->rows as $option_value) {
			$option_value_description_data = array();
			
			$option_value_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "option_value_description WHERE option_value_id = '" . (int)$option_value['option_value_id'] . "'");			
			
			foreach ($option_value_description_query->rows as $option_value_description) {
				$option_value_description_data[$option_value_description['language_id']] = array('name' => $option_value_description['name']);
			}
			
			$option_value_data[] = array(
				'option_value_id'          => $option_value['option_value_id'],
				'option_value_description' => $option_value_description_data,
				'image'                    => $option_value['image'],
				'sort_order'               => $option_value['sort_order']
			);
		}
		
		return $option_value_data;
	}

	public function getTotalCustomFields() {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "option`"); 
		
		return $query->row['total'];
	}		
}
?>