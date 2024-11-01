<?php

namespace TranscyApp\Controllers;

use TranscyApp\Repositories\MenuRepository;
use TranscyApp\Transformers\MenuTransformer;
use Illuminate\Utils\PagingHelper;
use Illuminate\Configuration;
use Illuminate\Utils\ValidationHelper;

class MenuController extends BaseController
{
    protected $menuRepository;

    protected $type     = 'nav_menu';

    protected $typeItem = 'nav_menu_item';

    /**
     * MenuRepository constructor.
     *
     * @param MenuRepository $menuRepository
     */
    public function __construct()
    {
        $this->menuRepository = new MenuRepository();
    }

    /**
     * Get information site controller
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function get(\WP_REST_Request $request)
    {
        //Get data resource
        $query      = $this->menuRepository->get($this->type, $this->getSearch($request));

        return $this->responseSuccess([
            'paging' => PagingHelper::renderTerm($request, $query),
            'list'   => MenuTransformer::transform(
                $this->menuRepository->wpQueryTermToArray($query)
            )
        ]);
    }

    /**
     * Get information site controller
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function getItem(\WP_REST_Request $request)
    {
        $object = $this->validation($request);
        
        $query  = $this->menuRepository->getItem($object, $this->typeItem, $this->getSearch($request));

        return $this->responseSuccess([
            'paging' => PagingHelper::render($query),
            'list'   => MenuTransformer::callInforItem(
                $this->menuRepository->wpQueryToArray($query)
            )
        ]);
    }


    /**
     * Get information site controller
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function translateItem(\WP_REST_Request $request)
    {
        //Validation body
        $body   = json_decode($request->get_body(), JSON_OBJECT_AS_ARRAY);

        $object = $this->validationItem($request, $body, ['locale']);

        //Get data resource
        $translate  = $this->menuRepository->translateItem($this->typeItem, $object, $body);

        if ($translate instanceof \WP_Error) {
            return $this->responseFailed($translate->get_error_messages());
        }

        return $this->responseSuccess(MenuTransformer::callInforItem($translate));
    }

    /**
     * Delete resource by type
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function deleteItem(\WP_REST_Request $request)
    {
        //Validation body
        $body   = json_decode($request->get_body(), JSON_OBJECT_AS_ARRAY);

        $object = $this->validationItem($request, $body, ['locale']);
        if (!is_object($object)) {
            return $this->responseFailed($object);
        }

        //Get data resource
        $delete  = $this->menuRepository->deleteItem($this->typeItem, $object, $body);

        if ($delete !== true) {
            return $this->responseFailed($delete);
        }

        return $this->responseSuccess(__('Deleted successfully', 'transcy'));
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

        if (empty($object) || $object->taxonomy != $this->type) {
            return $this->responseFailed(__('Menu translate not exists', 'transcy'));
        }

        return $object;
    }

    public function validationItem(\WP_REST_Request $request, array $body = [], $filledValidate = [])
    {
        $this->disableRestRespone();

        //Validation resource type
        $validator = new ValidationHelper($request->get_params());
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

        $object = get_post($request->get_param('id'));

        if (empty($object) || $object->post_type != $this->typeItem) {
            return $this->responseSuccess([], __('Item menu translate not exists', 'transcy'));
        }

        return $object;
    }

    public function locations(){
        $locations = $this->menuRepository->locations();
        return $this->responseSuccess($locations);
    }
}
