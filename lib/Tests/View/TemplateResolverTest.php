<?php

namespace Netgen\BlockManager\Tests\View;

use Netgen\BlockManager\View\TemplateResolver;
use Netgen\BlockManager\Tests\View\Stubs\View;
use Netgen\BlockManager\Configuration\ConfigurationInterface;
use Netgen\BlockManager\View\Matcher\MatcherInterface;
use DateTime;

class TemplateResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configurationMock;

    public function setUp()
    {
        $this->configurationMock = $this->getMock(ConfigurationInterface::class);
    }

    /**
     * @covers \Netgen\BlockManager\View\TemplateResolver::__construct
     * @covers \Netgen\BlockManager\View\TemplateResolver::resolveTemplate
     * @covers \Netgen\BlockManager\View\TemplateResolver::matches
     */
    public function testResolveTemplate()
    {
        $view = $this->getView();

        $matcherMock = $this->getMock(MatcherInterface::class);
        $matcherMock
            ->expects($this->once())
            ->method('setConfig')
            ->with($this->equalTo(array('paragraph')));
        $matcherMock
            ->expects($this->once())
            ->method('match')
            ->with($this->equalTo($view))
            ->will($this->returnValue(true));

        $this->configurationMock
            ->expects($this->any())
            ->method('getParameter')
            ->with($this->equalTo('view'))
            ->will(
                $this->returnValue(
                    array(
                        'context' => array(
                            'paragraph' => array(
                                'template' => 'some_template.html.twig',
                                'match' => array(
                                    'definition_identifier' => 'paragraph',
                                ),
                            ),
                        ),
                    )
                )
            );

        $templateResolver = new TemplateResolver(
            array(
                'definition_identifier' => $matcherMock,
            ),
            $this->configurationMock
        );

        self::assertEquals('some_template.html.twig', $templateResolver->resolveTemplate($view));
    }

    /**
     * @covers \Netgen\BlockManager\View\TemplateResolver::__construct
     * @covers \Netgen\BlockManager\View\TemplateResolver::resolveTemplate
     * @covers \Netgen\BlockManager\View\TemplateResolver::matches
     */
    public function testResolveTemplateWithEmptyMatchConfig()
    {
        $view = $this->getView();

        $this->configurationMock
            ->expects($this->any())
            ->method('getParameter')
            ->with($this->equalTo('view'))
            ->will(
                $this->returnValue(
                    array(
                        'context' => array(
                            'paragraph' => array(
                                'template' => 'some_template.html.twig',
                                'match' => array(),
                            ),
                        ),
                    )
                )
            );

        $templateResolver = new TemplateResolver(
            array(),
            $this->configurationMock
        );

        self::assertEquals('some_template.html.twig', $templateResolver->resolveTemplate($view));
    }

    /**
     * @covers \Netgen\BlockManager\View\TemplateResolver::__construct
     * @covers \Netgen\BlockManager\View\TemplateResolver::resolveTemplate
     * @covers \Netgen\BlockManager\View\TemplateResolver::matches
     */
    public function testResolveTemplateWithMultipleMatches()
    {
        $view = $this->getView();

        $this->configurationMock
            ->expects($this->any())
            ->method('getParameter')
            ->with($this->equalTo('view'))
            ->will(
                $this->returnValue(
                    array(
                        'context' => array(
                            'paragraph' => array(
                                'template' => 'some_template.html.twig',
                                'match' => array(),
                            ),
                            'paragraph_other' => array(
                                'template' => 'some_other_template.html.twig',
                                'match' => array(),
                            ),
                        ),
                    )
                )
            );

        $templateResolver = new TemplateResolver(
            array(),
            $this->configurationMock
        );

        self::assertEquals('some_template.html.twig', $templateResolver->resolveTemplate($view));
    }

    /**
     * @covers \Netgen\BlockManager\View\TemplateResolver::resolveTemplate
     * @expectedException \RuntimeException
     */
    public function testResolveTemplateThrowsRuntimeExceptionIfNoContext()
    {
        $this->configurationMock
            ->expects($this->any())
            ->method('getParameter')
            ->with($this->equalTo('view'))
            ->will($this->returnValue(array()));

        $templateResolver = new TemplateResolver(array(), $this->configurationMock);
        $templateResolver->resolveTemplate($this->getView());
    }

    /**
     * @covers \Netgen\BlockManager\View\TemplateResolver::resolveTemplate
     * @expectedException \RuntimeException
     */
    public function testResolveTemplateThrowsRuntimeExceptionIfEmptyContext()
    {
        $this->configurationMock
            ->expects($this->any())
            ->method('getParameter')
            ->with($this->equalTo('view'))
            ->will($this->returnValue(array('context' => array())));

        $templateResolver = new TemplateResolver(
            array(),
            $this->configurationMock
        );

        $templateResolver->resolveTemplate($this->getView());
    }

    /**
     * @covers \Netgen\BlockManager\View\TemplateResolver::resolveTemplate
     * @covers \Netgen\BlockManager\View\TemplateResolver::matches
     * @expectedException \RuntimeException
     */
    public function testResolveTemplateThrowsRuntimeExceptionIfNoMatch()
    {
        $view = $this->getView();

        $matcherMock = $this->getMock(MatcherInterface::class);
        $matcherMock
            ->expects($this->once())
            ->method('setConfig')
            ->with($this->equalTo(array('title')));
        $matcherMock
            ->expects($this->once())
            ->method('match')
            ->with($this->equalTo($view))
            ->will($this->returnValue(false));

        $this->configurationMock
            ->expects($this->any())
            ->method('getParameter')
            ->with($this->equalTo('view'))
            ->will(
                $this->returnValue(
                    array(
                        'context' => array(
                            'title' => array(
                                'match' => array(
                                    'definition_identifier' => 'title',
                                ),
                            ),
                        ),
                    )
                )
            );

        $templateResolver = new TemplateResolver(
            array(
                'definition_identifier' => $matcherMock,
            ),
            $this->configurationMock
        );

        $templateResolver->resolveTemplate($view);
    }

    /**
     * @covers \Netgen\BlockManager\View\TemplateResolver::resolveTemplate
     * @covers \Netgen\BlockManager\View\TemplateResolver::matches
     * @expectedException \RuntimeException
     */
    public function testResolveTemplateThrowsRuntimeExceptionIfNoMatcher()
    {
        $this->configurationMock
            ->expects($this->any())
            ->method('getParameter')
            ->with($this->equalTo('view'))
            ->will(
                $this->returnValue(
                    array(
                        'context' => array(
                            'title' => array(
                                'match' => array(
                                    'definition_identifier' => 'title',
                                ),
                            ),
                        ),
                    )
                )
            );

        $templateResolver = new TemplateResolver(
            array(),
            $this->configurationMock
        );

        $templateResolver->resolveTemplate($this->getView());
    }

    /**
     * @covers \Netgen\BlockManager\View\TemplateResolver::resolveTemplate
     * @covers \Netgen\BlockManager\View\TemplateResolver::matches
     * @expectedException \RuntimeException
     */
    public function testResolveTemplateThrowsRuntimeExceptionIfNoMatcherInterface()
    {
        $view = $this->getView();

        $matcherMock = $this->getMock(DateTime::class);

        $this->configurationMock
            ->expects($this->any())
            ->method('getParameter')
            ->with($this->equalTo('view'))
            ->will(
                $this->returnValue(
                    array(
                        'context' => array(
                            'title' => array(
                                'match' => array(
                                    'definition_identifier' => 'title',
                                ),
                            ),
                        ),
                    )
                )
            );

        $templateResolver = new TemplateResolver(
            array(
                'definition_identifier' => $matcherMock,
            ),
            $this->configurationMock
        );

        $templateResolver->resolveTemplate($view);
    }

    /**
     * Returns the view used for testing.
     *
     * @return \Netgen\BlockManager\View\ViewInterface
     */
    protected function getView()
    {
        $view = new View();
        $view->setContext('context');

        return $view;
    }
}
