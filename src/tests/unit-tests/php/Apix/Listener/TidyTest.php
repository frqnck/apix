<?php
namespace Apix\Listener;

use Apix\HttpRequest;
use Apix\Response;

class TidyTest extends \PHPUnit_Framework_TestCase
{

    protected $tidy, $response;

    public function setUp()
    {
        if (!extension_loaded('tidy')) {
            $this->markTestSkipped(
              'The Tidy extension is not available.'
            );
        }

        $request = HttpRequest::GetInstance();
        $this->response = new Response($request);
        $this->response->unit_test = true;

        $this->route = $this->getMock('Apix\Router');

        $this->route->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('/resource'));

        $this->route->expects($this->any())
            ->method('getController')
            ->will($this->returnValue('resource'));

        $options = array(
            'enable'    => true,
            'generic'   => array(
                'indent'        => true,
                'indent-spaces' => 2,
                'show-body-only' => true
            )
        );

        $this->tidy = new Tidy($options);
    }

    protected function tearDown()
    {
        unset($this->tidy);
        unset($this->response);
        unset($this->route);
    }

    public function testIsDisable()
    {
        $t = new Tidy( array('enable'=>false) );
        $this->assertFalse( $t->update($this->response) );
    }

    public function testGenerateAsXml()
    {
        $this->response->setFormat('xml');
        $results = array('results');
        $this->response->generate($this->route, $results);

        $this->tidy->update($this->response);

        $xml = "<root>\n  <resource>\n    <item>results</item>\n  </resource>\n</root>";

        $this->assertSame(
            '<?xml version="1.0" encoding="utf-8"?>' . PHP_EOL . $xml,
            $this->response->getOutput()
        );
    }

    public function testGenerateAsLst()
    {
        $this->response->setFormats(array('lst'));
        $this->response->setFormat('lst');
        $results = array('results');
        $this->response->generate($this->route, $results);

        $this->tidy->update($this->response);

        $html = "<ul>
  <li>root:
    <ul>
      <li>resource:
        <ul>
          <li>0: results
          </li>
        </ul>
      </li>
    </ul>
  </li>
</ul>";

        $this->assertSame(
            $html,
            $this->response->getOutput()
        );
    }

}