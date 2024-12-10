<?php namespace App\Http\Controllers;


use App\Components\Services\IProjectService;
use App\Exceptions\ProcessException;
use Illuminate\Http\Request;
use App\Constants\Http\StatusCode;
use App\Niu\Transformers\ProjectTransformer;


class ProjectController extends ApiController {

	private $_projectService = null;
	private $_transformer = null;

	public function __construct(IProjectService $projectService, ProjectTransformer $transformer) 
    {
        $this->_projectService  = $projectService;
		$this->_transformer  	= $transformer;
    }
	
	public function index(Request $request)
	{
		$id 	= $request->input('id');
		$old_id = $request->input('old-id');
		$name 	= $request->input('name');
		$limit  = $request->get( 'limit' ) ?? '10';
		try {
			if(isset($id) && $id) {
				$result = $this->getByID($id);
			} else if(isset($old_id) && $old_id) {
				$result = $this->getByOldID($old_id);
			} else if(isset($name) && $name) {
				$result = $this->getByName($name);
			} else {
				$result = $this->_projectService->getAllProjects($limit);
				return $this->respondWithPagination( $result, [
					'data' => $this->_transformer->transformCollection( $result->all() )
				]);
			}
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }

		return $this->setJsonDataResponse($result);
	}

	public function getPhoto(Request $request, $id)
	{
		try {
			$width 	 = $request->input('width' );
			$height  = $request->input('height');

			$image = $this->_projectService->getProjectImage($id, $width, $height);
			return $this->setImageResponse($image['image']);
		} catch (ProcessException $e) {
			return $this->setJsonMessageResponse($e->getMessage(), StatusCode::HTTP_INTERNAL_SERVER_ERROR);
		}
	}


	private function getByID($id)
	{
		try {
			$result = $this->_projectService->getProjectByID($id);
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }
		return $result;
	}

	private function getByName($name)
	{
		try {
			$result = $this->_projectService->getProjectByName($name);
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }
		
		return $result;
	}

	private function getByOldID($old_id)
	{
		try {
			$result = $this->_projectService->getProjectByOldID($old_id);
        } catch (ProcessException $e) {
            return $this->setJsonMessageResponse($e->getMessage(), $e->getCode());
        }
		
		return $result;
	}
	

}
