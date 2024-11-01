<?php

namespace TranscyApp\Controllers;

use Illuminate\Configuration;
use Illuminate\Utils\Helper;
use Illuminate\Utils\PagingHelper;
use TranscyApp\Transformers\ResourcePostTransformer;
use TranscyApp\Repositories\ResourcePostRepository;
use Illuminate\Utils\ValidationHelper;

class ResourcePostController extends BaseController
{
    protected $resourcePostRepository;

    /**
     * ResourcePostController constructor.
     *
     * @param ResourcePostRepository $resourcePostRepository
     */
    public function __construct()
    {
        $this->resourcePostRepository = new ResourcePostRepository();
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
        $validator->rule('type')->required()->inArray(Helper::getResourcePosts());

        if ($validator->isFailed()) {
            return $this->responseFailed(__('Resource type not found.', 'transcy'));
        }

        //Get data resource
        $query      = $this->resourcePostRepository->search($request->get_param('type'), $this->getSearch($request));

        return $this->responseSuccess([
            'paging' => PagingHelper::render($query),
            'list'   => ResourcePostTransformer::transform(
                $this->resourcePostRepository->wpQueryToArray($query)
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
        //Validation body
        $body   = json_decode($request->get_body(), JSON_OBJECT_AS_ARRAY);

        $object = $this->validation($request, $body, ['locale', 'title', 'content']);

        //Get data resource
        $translate  = $this->resourcePostRepository->translate($request->get_param('type'), $object, $body);

        if ($translate instanceof \WP_Error) {
            return $this->responseFailed($translate->get_error_messages());
        }

        return $this->responseSuccess(ResourcePostTransformer::transform($translate));
    }

    /**
     * Delete resource by type
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function delete(\WP_REST_Request $request)
    {
        //Validation body
        $body   = json_decode($request->get_body(), JSON_OBJECT_AS_ARRAY);

        $object = $this->validation($request, $body);

        //Get data resource
        $delete  = $this->resourcePostRepository->delete($request->get_param('type'), $object, $body);

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

        return $this->responseSuccess(ResourcePostTransformer::transform($object));
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

        //Validation resource type
        $validator = new ValidationHelper($request->get_params());

        $validator->rule('type')->required()->inArray(Helper::getResourcePosts());
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

        //Can not translate shop page
        if(!empty($shopPage = get_option('woocommerce_shop_page_id')) && $shopPage == $request->get_param('id')){
            return $this->responseFailed(__('Can not translate shop page', 'transcy'));
        }

        $object = get_post($request->get_param('id'));

        if (empty($object) || $object->post_type != $request->get_param('type')) {
            return $this->responseSuccess([], __('Resource translate not found', 'transcy'));
        }

        return $object;
    }
}
