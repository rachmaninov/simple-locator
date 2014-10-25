<?php
/**
* Front-end form handler for simple locator lookup
* @return JSON Response
*/
function wpsl_form_handler()
{
	new WPSL_Handler;
}

require_once('class-sl-validation.php');

/**
* Processes the form data and return results
*/
class WPSL_Handler {

	/**
	* Form Data
	* @var array
	*/
	private $data;

	/**
	* Validator
	*/
	private $validator;

	/**
	* Query Data
	* @var array
	*/
	private $query_data;

	/**
	* Query - the SQL
	*/
	private $sql;

	/**
	* Query Results
	* @var array
	*/
	private $results;

	/**
	* Total Results
	* @var int
	*/
	private $total_results;

	/**
	* JSON Response
	* @var array
	*/
	private $response;


	public function __construct()
	{
		$this->validator = new WPSL_Validation;
		$this->setData();
		$this->validateData();
		$this->setQueryData();
		$this->setQuery();
		$this->runQuery();
		$this->sendResponse();
	}


	/**
	* Sanitize and set the user-submitted data
	*/
	private function setData()
	{
		$this->data = array(
			'nonce' => sanitize_text_field($_POST['locatorNonce']),
			'zip' => sanitize_text_field($_POST['zip']),
			'distance' => sanitize_text_field($_POST['distance']),
			'latitude' => sanitize_text_field($_POST['latitude']),
			'longitude' => sanitize_text_field($_POST['longitude']),
			'unit' => sanitize_text_field($_POST['unit'])
		);
	}


	/**
	* Validate Data
	*/
	private function validateData()
	{
		return ( $this->validator->validates($this->data) ) ? true : false;
	}


	/**
	* Set Query Data
	*/
	private function setQueryData()
	{
		global $wpdb;
		$table_prefix = $wpdb->prefix;
		$this->query_data['post_table'] = $table_prefix . 'posts';
		$this->query_data['meta_table'] = $table_prefix . 'postmeta';
		$this->query_data['distance'] = $this->data['distance'];
		$this->query_data['userlat'] = $this->data['latitude'];
		$this->query_data['userlong'] = $this->data['longitude'];
		$this->query_data['post_type'] = get_option('wpsl_post_type');
		$this->query_data['lat_field'] = get_option('wpsl_lat_field');
		$this->query_data['lng_field'] = get_option('wpsl_lng_field');
		$this->query_data['diameter'] = ( $this->data['unit'] == "miles" ) ? 3959 : 6371;
	}


	/**
	* Set the Query
	*/
	private function setQuery()
	{
		$sql = "
			SELECT 
			p.post_title AS title,
			p.ID AS id,
			p.post_content AS content,";
			if ( $this->query_data['post_type'] == 'location' ) :
			$sql .= "
			t.meta_value AS phone,
			a.meta_value AS address,
			c.meta_value AS city,
			s.meta_value AS state,
			z.meta_value AS zip,
			w.meta_value AS website,";
			endif;
			$sql .= "
			lat.meta_value AS latitude,
			lng.meta_value AS longitude,
			( " . $this->query_data['diameter'] . " * acos( cos( radians(" . $this->query_data['userlat'] . ") ) * cos( radians( lat.meta_value ) ) 
			* cos( radians( lng.meta_value ) - radians(" . $this->query_data['userlong'] . ") ) + sin( radians(" . $this->query_data['userlat'] . ") ) * sin(radians(lat.meta_value)) ) )
			AS distance
			FROM " . $this->query_data['post_table'] . " AS p
			LEFT JOIN " . $this->query_data['meta_table'] . " AS lat
			ON p.ID = lat.post_id AND lat.meta_key = '" . $this->query_data['lat_field'] . "'
			LEFT JOIN " . $this->query_data['meta_table'] . " AS lng
			ON p.ID = lng.post_id AND lng.meta_key = '" . $this->query_data['lng_field'] . "'";
			if ( $this->query_data['post_type'] == 'location' ) :
			$sql .= "
			LEFT JOIN " . $this->query_data['meta_table'] . " AS c
			ON p.ID = c.post_id AND c.meta_key = 'wpsl_city'
			LEFT JOIN " . $this->query_data['meta_table'] . " AS a
			ON p.ID = a.post_id AND a.meta_key = 'wpsl_address'
			LEFT JOIN " . $this->query_data['meta_table'] . " AS s
			ON p.ID = s.post_id AND s.meta_key = 'wpsl_state'
			LEFT JOIN " . $this->query_data['meta_table'] . " AS z
			ON p.ID = z.post_id AND z.meta_key = 'wpsl_zip'
			LEFT JOIN " . $this->query_data['meta_table'] . " AS t
			ON p.ID = t.post_id AND t.meta_key = 'wpsl_phone'
			LEFT JOIN " . $this->query_data['meta_table'] . " AS w
			ON p.ID = w.post_ID AND w.meta_key = 'wpsl_website'";
			endif;
			$sql .= "
			WHERE `post_type` = '" . $this->query_data['post_type'] . "'
			AND `post_status` = 'publish'
			HAVING distance < " . $this->query_data['distance'] . "
			ORDER BY distance
		";
		$this->sql = $sql;
	}


	/**
	* Lookup location data
	*/
	private function runQuery()
	{
		global $wpdb;
		$results = $wpdb->get_results($this->sql);
		$this->total_results = count($results);
		$this->setResults($results);
	}


	/**
	* Prepare Results
	*/
	private function setResults($results)
	{
		foreach ( $results as $qr ) :
			$location = array(
				'title' => $qr->title,
				'permalink' => get_permalink($qr->id),
				'distance' => round($qr->distance, 2),
				'address' => $qr->address,
				'city' => $qr->city,
				'state' => $qr->state,
				'zip' => $qr->zip,
				'phone' => $qr->phone,
				'website' => $qr->website,
				'latitude' => $qr->latitude,
				'longitude' => $qr->longitude
			);
			$this->results[] = $location;
		endforeach;
	}


	/**
	* Send the Response
	*/
	private function sendResponse()
	{
		return wp_send_json(array(
			'status' => 'success',
			'zip'=> $this->data['zip'], 
			'distance'=> $this->data['distance'],
			'latitude' => $this->data['latitude'],
			'longitude' => $this->data['longitude'],
			'unit' => $this->data['unit'],
			'results' => $this->results,
			'result_count' => $this->total_results
		));
	}

}


