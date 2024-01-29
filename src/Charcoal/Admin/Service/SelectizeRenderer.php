<?php

namespace Charcoal\Admin\Service;

use Exception;

// From PSR-3
use Psr\Log\LoggerAwareTrait;

// From 'charcoal-cms'
use Charcoal\Cms\TemplateableTrait;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From 'charcoal-core'
use Charcoal\Model\ModelInterface;

// From 'charcoal-translator'
use Charcoal\Translator\TranslatorAwareTrait;

// From 'charcoal-view'
use Charcoal\View\ViewInterface;

/**
 * Renders a template given the object meta or template controller.
 *
 * Selectize renderer service
 */
class SelectizeRenderer
{
    use TranslatorAwareTrait;
    use TemplateableTrait;
    use LoggerAwareTrait;

    /**
     * Template Factory
     *
     * @var FactoryInterface
     */
    private $templateFactory;

    /**
     * @var ViewInterface
     */
    private $view;

    /**
     * @param array $data Dependencies.
     * @throws Exception If missing dependencies.
     */
    public function __construct(array $data)
    {
        if (!isset($data['logger'])) {
            throw new Exception(
                'You must set the logger in the Exporter Constructor.'
            );
        }

        $this->logger          = $data['logger'];
        $this->templateFactory = $data['template_factory'];
        $this->view            = $data['view'];
        $this->setTranslator($data['translator']);

        return $this;
    }

    /**
     * @param string                    $templateIdent   The templateIdent as string.
     * @param ModelInterface|array|null $context         The context as Model or array.
     * @param string|null               $controllerIdent The ControllerIdent string to override Object context.
     * @throws \InvalidArgumentException If the callable id not callable.
     * @return string
     */
    public function renderTemplate($templateIdent, $context, $controllerIdent = null)
    {
        $template = null;

        if ($controllerIdent && is_string($controllerIdent)) {
            $controllerIdent    = explode('::', $controllerIdent);
            $controllerCallable = isset($controllerIdent[1]) ? $controllerIdent[1] : null;
            $controllerIdent    = $controllerIdent[0];

            $template = $this->templateFactory->create($controllerIdent);

            if ($controllerCallable) {
                $method = [$template, $controllerCallable];
                if (!is_callable($method)) {
                    throw new \InvalidArgumentException(sprintf(
                        '%s::%s supplied in %s::%s is not a callable method.',
                        $controllerIdent,
                        $controllerCallable,
                        __CLASS__,
                        __FUNCTION__
                    ));
                }

                call_user_func($method, $context);
            } elseif (method_exists($template, 'setData')) {
                if ($context instanceof ModelInterface) {
                    if (is_callable([$context, 'dataForTemplate'])) {
                        $template->setData($context->dataForTemplate());
                    } else {
                        $template->setData($context->data());
                    }
                } else {
                    $template->setData($context);
                }
            } else {
                throw new \InvalidArgumentException(sprintf(
                    '%s supplied in %s::%s is not callable.',
                    get_class($template),
                    __CLASS__,
                    __FUNCTION__
                ));
            }
        }

        if (!$template) {
            $template = $context;
        }

        return $this->view->render($templateIdent, $template);
    }
}
