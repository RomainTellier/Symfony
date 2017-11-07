<?php

namespace RT\PlatformBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class AdvertController extends Controller
{
	public function indexAction()
	{
		$content = $this->get('templating')->render('RTPlatformBundle:Advert:index.html.twig', array('nom' => 'RomainTheBOSS'));

		return new Response($content);
	}

	public function page2Action()
	{
		$content = $this->get('templating')->render('RTPlatformBundle:Advert:page2.html.twig', array('nom' => 'RomainLekéké'));

		return new Response($content);
	}
}