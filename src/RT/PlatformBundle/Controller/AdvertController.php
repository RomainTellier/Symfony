<?php

namespace RT\PlatformBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdvertController extends Controller
{

    // La route fait appel à OCPlatformBundle:Advert:view,
    // on doit donc définir la méthode viewAction.
    // On donne à cette méthode l'argument $id, pour
    // correspondre au paramètre {id} de la route
    public function viewAction($id, Request $request)
    {
        // On récupère notre paramètre tag
        $tag = $request->query->get('tag');

        return new Response(
            "Affichage de l'annonce d'id : ".$id.", avec le tag : ".$tag
        );
    }

    // On récupère tous les paramètres en arguments de la méthode
    public function viewSlugAction($slug, $year, $format)
    {
        return new Response(
            "On pourrait afficher l'annonce correspondant au
            slug '".$slug."', créée en ".$year." et au format ".$format."."
        );
    }

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