<?php

namespace TranscyApp\Controllers;

use Illuminate\Configuration;
use Illuminate\Utils\PagingHelper;
use TranscyApp\Transformers\ResourceTermTransformer;
use TranscyApp\Repositories\ResourceTermRepository;
use Illuminate\Utils\ValidationHelper;
use Illuminate\Utils\Helper;

class ResourceTermController extends BaseController
{
    protected $resourceTermRepository;

    /**
     * ResourcePostController constructor.
     *
     * @param ResourceTermRepository $resourceTermRepository
     */
    public function __construct()
    {
        $this->resourceTermRepository = new ResourceTermRepository();
    }

    /**
     * Display a listing of resource by type
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function index(\WP_REST_Request $request)
    {
        //Validation resource type
        $validator = new ValidationHelper($request->get_params());
        $validator->rule('type')->required()->inArray(Helper::getResourceTerms());

        if ($validator->isFailed()) {
            return $this->responseFailed(
                __('Resource type not found.', 'transcy')
            );
        }
        //Get data resource
        $query      = $this->resourceTermRepository->search($request->get_param('type'), $this->getSearch($request));

        return $this->responseSuccess([
            'paging' => PagingHelper::renderTerm($request, $query),
            'list'   => ResourceTermTransformer::transform(
                $this->resourceTermRepository->wpQueryTermToArray($query)
            )
        ]);
    }

    /**
     * Translate resource by type
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function translate(\WP_REST_Request $request)
    {
        //Validation resource type
        $body    = json_decode($request->get_body(), JSON_OBJECT_AS_ARRAY);

        $object = $this->validation($request, $body, ['name', 'locale']);

        //Get data resource
        $translate  = $this->resourceTermRepository->translate($request->get_param('type'), $object, $body);

        if ($translate instanceof \WP_Error) {
            return $this->responseFailed($translate->get_error_messages());
        }

        return $this->responseSuccess(ResourceTermTransformer::transform($translate));
    }

    /**
     * Delete resource by type
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function delete(\WP_REST_Request $request)
    {
        //Validation resource type
        $body    = json_decode($request->get_body(), JSON_OBJECT_AS_ARRAY);

        $object = $this->validation($request, $body);
        if (!is_object($object)) {
            return $this->responseFailed($object);
        }

        //Get data resource
        $delete  = $this->resourceTermRepository->delete($request->get_param('type'), $object, $body);

        if ($delete !== true) {
            return $this->responseFailed($delete);
        }

        return $this->responseSuccess(__('Deleted successfully', 'transcy'));
    }

    /**
     * Detail resource by type
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function detail(\WP_REST_Request $request)
    {
        $object = $this->validation($request);
        if (!is_object($object)) {
            return $this->responseFailed($object);
        }

        return $this->responseSuccess(ResourceTermTransformer::transform($object));
    }

    /**
     * Collect search request
     *
     * @param \WP_REST_Request $request
     *
     * @return array
     */
    private function getSearch(\WP_REST_Request $request)
    {
        $search = [];

        $search['page']      = $request['page'] ?? Configuration::DEFAULT_CURRENT_PAGE;
        $search['per_page']  = $request['per_page'] ?? Configuration::DEFAULT_PAGINATION_ITEMS;

        return $search;
    }

    public function validation(\WP_REST_Request $request, array $body = [], $filledValidate = [])
    {
        $this->disableRestRespone();

        $validator = new ValidationHelper($request->get_params());

        $validator->rule('type')->required()->inArray(Helper::getResourceTerms());
        $validator->rule('id')->required()->pattern('int');

        if (in_array('locale', $filledValidate)) {
            if (!in_array($body['locale'], getAdvancedLang())) {
                return $this->responseFailed(__('Locale not is advanced lang', 'transcy'));
            }
            $validator->name('locale')->value($body['locale'])->required();
        }
        
        if ($validator->isFailed()) {
            return $this->responseFailed($validator->getErrors());
        }

        $object = get_term($request->get_param('id'));

        if (empty($object) || $object->taxonomy != $request->get_param('type')) {
            return $this->responseSuccess([], __('Resource translate not found', 'transcy'));
        }

        return $object;
    }
}
