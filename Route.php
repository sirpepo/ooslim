<?php
namespace OOSlim;

use Slim\Slim;

abstract class Route
{
	protected $app;
	public function __construct()
	{
		$this->app = Slim::getInstance();
	}

	protected function launchOK($data = array())
	{
		$data['success'] = TRUE;
	    $this->launchResponse($data);
	}

	protected function launchError($status = 500, $msg = "Unknown error") 
	{
		$data = array(
			'success' => FALSE,
			'status' => $status,
			'message' => $msg,
		);
		$this->launchResponse($data);
	}

	protected function launchResponse($data = array()) 
	{
	   echo json_encode($data);
	}
}
