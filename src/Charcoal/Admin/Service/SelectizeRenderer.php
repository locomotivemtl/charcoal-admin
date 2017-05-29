<?php

namespace Charcoal\Admin\Service;

use Charcoal\Cms\TemplateableTrait;
use Charcoal\Factory\FactoryInterface;
use Charcoal\Model\ModelInterface;
use Charcoal\Translator\TranslatorAwareTrait;
use Charcoal\View\ViewInterface;
use Exception;
use Psr\Log\LoggerAwareTrait;

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
     * PHP 5 allows developers to declare constructor methods for classes.
     * Classes which have a constructor method call this method on each newly-created object,
     * so it is suitable for any initialization that the object may need before it is used.
     *
     * Note: Parent constructors are not called implicitly if the child class defines a constructor.
     * In order to run a parent constructor, a call to parent::__construct() within the child constructor is required.
     *
     * @param array $data Dependencies.
     * @throws Exception If missing dependencies.
     * @return self
     * @link http://php.net/manual/en/language.oop5.decon.php
     */
    public function __construct(array $data)
    {
        if (!isset($data['logger'])) {
            throw new Exception(
                'You must set the logger in the Exporter Constructor.'
            );
        }

        $this->logger = $data['logger'];
        $this->templateFactory = $data['template_factory'];
        $this->view = $data['view'];
        $this->setTranslator($data['translator']);

        return $this;
    }

    /**
     * @param string         $templateIdent   The templateIdent as string.
     * @param ModelInterface $object          The ObjectType for context.
     * @param string|null    $controllerIdent The ControllerIdent string to override Object context.
     * @throws \InvalidArgumentException If the callable id not callable.
     * @return string
     */
    public function renderTemplate($templateIdent, ModelInterface $object, $controllerIdent = null)
    {
        $template = null;

        if ($controllerIdent && is_string($controllerIdent)) {
            $controllerIdent = explode('::', $controllerIdent);
            $controllerCallable = isset($controllerIdent[1]) ? $controllerIdent[1] : null;
            $controllerIdent = $controllerIdent[0];

            $template = $this->templateFactory->create($controllerIdent);

            if ($controllerCallable) {
                $method = [ $template, $controllerCallable ];
                if (!is_callable($method)) {
                    throw new \InvalidArgumentException(sprintf(
                        '%s::%s supplied in %s::%s is not a callable method.',
                        $controllerIdent,
                        $controllerCallable,
                        __CLASS__,
                        __FUNCTION__
                    ));
                }

                call_user_func($method, $object);
            } elseif (is_callable($template)) {
                $template($object);
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
            $template = $object;
        }

        return $this->view->render($templateIdent, $template);
    }
}
