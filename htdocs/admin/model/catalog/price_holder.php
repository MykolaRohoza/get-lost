<?php
class ModelCatalogPriceHolder extends Model {

	public function deleteLinkedPriceHolderProducts($product_id) {

		$this->db->query("DELETE FROM " . DB_PREFIX . "price_holder_product_to_product WHERE product_id = '" . (int)$product_id . "'");

	}
	public function linkPriceHolderProductsToProduct($price_holder_products, $product_id) {
		$php_ids = array();
		foreach ($price_holder_products as $php_id) {
			$sql = "INSERT INTO " . DB_PREFIX . "price_holder_product_to_product";
			$sql .= " SET product_id = '" . (int)$product_id . "',";
			$sql .= " php_id='" . (int)$php_id . "'";
			$this->db->query($sql);
			$php_ids[] = $this->db->getLastId();
		}
		return $php_ids;
	}
	


	public function getProducts($data = array()) {
		$sql = "SELECT php.*, phc.company_name, php2p.product_id";
		$sql .= " FROM " . DB_PREFIX . "price_holder_products php";
		$sql .= " LEFT JOIN " . DB_PREFIX . "price_holder_company phc USING (phc_id)";
		$sql .= " LEFT JOIN " . DB_PREFIX . "price_holder_product_to_product php2p USING (php_id)";
		$sql .= " WHERE 1=1 ";

		if (!empty($data['filter_name'])) {
			$sql .= " AND php.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}
		if (!empty($data['filter_year'])) {
			$sql .= " AND php.year LIKE '" . $this->db->escape($data['filter_year']) . "'";
		}
		if (!empty($data['filter_short_size'])) {
			$sql .= " AND php.short_size LIKE '" . $this->db->escape($data['filter_short_size']) . "'";
		}

		if (!empty($data['filter_model'])) {
			$sql .= " AND php.code = '" . $this->db->escape($data['filter_model']) . "'";
		}
		if (!empty($data['filter_manufacturer'])) {
			$sql .= " AND php.manufacturer = '" . $this->db->escape($data['filter_manufacturer']) . "'";
		}
		if (!empty($data['product_id'])) {
			$sql .= " AND php2p.product_id = '" . (int)$data['product_id'] . "'";
		}

		$sort_data = array(
			'ad.name',
			'price_holder_group',
			'a.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY php.name";
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
}