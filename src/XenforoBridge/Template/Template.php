<?php

namespace XenforoBridge\Template;

use XenForo_Application;
use XenForo_Dependencies_Public;
use XenForo_ViewRenderer_HtmlPublic;
use XenforoBridge\Contracts\TemplateInterface;
use Zend_Controller_Request_Http;
use Zend_Controller_Response_Http;

class Template implements TemplateInterface
{
    const XENFORO_DEFAULT_CONTAINER = 'PAGE_CONTAINER';
    protected $xenBasePath;

    public function __construct($xenBasePath)
    {
        if (!is_string($xenBasePath)) {
            throw new Exception('Require string passed '.gettype($xenBasePath));
        }
        $this->xenBasePath = $xenBasePath;
    }

    /**
     * Render view with Xenforo Template.
     *
     * @param string $name     - template name
     * @param string $contents - xenforo template contents
     * @param array  $params   - overrided xenforo template parameters
     *
     * @return string;
     */
    public function renderTemplate($name, $content = '', $params = [], $container = self::XENFORO_DEFAULT_CONTAINER)
    {
        $template = new XenForo_Dependencies_Public();

        $template->preLoadData();

        $template->preRenderView();

        $finalParams = $this->createParams($content, $params, $container);


        $response = new XenForo_ViewRenderer_HtmlPublic($template, new Zend_Controller_Response_Http(), new Zend_Controller_Request_Http());

        return $response->renderContainer(
            $response->renderView('urb_itemhub_view', $finalParams, $name),
            $finalParams
        );
    }

    /**
     * Get Xenforo Template Dependencies.
     *
     * @return array
     */
    public function getDependenciesPublic()
    {

        //Initiallize Application
        $application = new XenForo_Application();
        $dependencies = new XenForo_Dependencies_Public();
        $request = new Zend_Controller_Request_Http();

        //Set Xenforo Base Path
        $basePath = parse_url($this->xenBasePath, PHP_URL_PATH);
        $request = $request->setBasePath($basePath);

        $application->set('requestPaths', $application::getRequestPaths($request));

        $dependencies->preLoadData();

        $params = $dependencies->getEffectiveContainerParams([], $request);

        return $params;
    }

    /**
     * Create parameters for rendering xenforo templates.
     *
     * @param string $contents                   - main content area of template
     * @param array  $additionalParams(Optional) - merge additional parameters
     *
     * @return array
     */
    public function createParams($content = '', $additionalParams = [], $container = self::XENFORO_DEFAULT_CONTAINER)
    {
        //Validates content
        if (!is_string((string) $content)) {
            $content = '';
        }
        $fixed_params = [
            'contents'          => (string) $content,
            'requestPaths'      => ['fullBasePath' => $this->xenBasePath],
            'serverTimeInfo'    => ['now' => time(), 'today' => time(), 'todayDow' => time()],
            'containerTemplate' => $container,
        ];

        $new_params = $this->getDependenciesPublic();

        $new_params = array_merge_recursive($new_params, $fixed_params, $additionalParams);

        return $new_params;
    }
}
