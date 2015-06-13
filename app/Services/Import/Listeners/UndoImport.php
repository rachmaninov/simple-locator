<?php

namespace SimpleLocator\Services\Import\Listeners;

use SimpleLocator\Repositories\ImportRepository;

/**
* Undo a previous import and erase all data
*/
class UndoImport extends ImportListenerBase
{
	/**
	* Import ID
	* @var int
	*/
	private $import_id;

	/**
	* Post IDs
	* @var array
	*/
	private $post_ids;

	/**
	* Import Repository
	* @var object
	*/
	private $import_repo;


	public function __construct()
	{
		parent::__construct();
		$this->validateUser();
		$this->import_repo = new ImportRepository;
		$this->setIDs();
		$this->deletePosts();
		$this->success();
	}

	/**
	* Check Capabilities
	*/
	private function validateUser()
	{
		if ( !current_user_can('delete_others_posts') ) return $this->error(__('You do not have the necessary capabilities to undo an import. Contact your site administrator to perform this action.', 'wpsimplelocator'));
	}

	/**
	* Set the Import ID & Post IDs
	*/
	private function setIDs()
	{
		$this->import_id = ( isset($_POST['undo_import_id']) ) ? intval($_POST['undo_import_id']) : 0;
		$this->post_ids = $this->import_repo->getImportedPostIDs($this->import_id);
	}

	/**
	* Delete the Posts
	*/
	private function deletePosts()
	{
		foreach($this->post_ids as $id){
			wp_delete_post($id, true);
		}
		wp_delete_post($this->import_id, true);
	}

	/**
	* Redirect to next step on success
	*/
	protected function success($step = null, $message = null)
	{
		$url = 'options-general.php?page=wp_simple_locator&tab=import&success=' . __('Import successfully undone. All post data has been removed.', 'wpsimplelocator');
		$url = admin_url($url);
		return header('Location:' . $url);
	}

}