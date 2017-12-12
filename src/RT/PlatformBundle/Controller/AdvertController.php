<?php

namespace RT\PlatformBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;


class AdvertController extends Controller
{
    public function indexAction($page)
    {
        // On ne sait pas combien de pages il y a
        // Mais on sait qu'une page doit être supérieure ou égale à 1
        if ($page < 0) {
            // On déclenche une exception NotFoundHttpException, cela va afficher
            // une page d'erreur 404 (qu'on pourra personnaliser plus tard d'ailleurs)
            throw new NotFoundHttpException('Page "'.$page.'" inexistante.');
        }

        // Ici, on récupérera la liste des annonces, puis on la passera au template

        // Mais pour l'instant, on ne fait qu'appeler le template
       //  return $this->render('RTPlatformBundle:Advert:index.html.twig');
        /*return $this->render('RTPlatformBundle:Advert:index.html.twig', array(
            'listAdverts' => array()
        ));*/
        // On récupère le repository
        $repository = $this->getDoctrine()
            ->getManager()
            ->getRepository('RTPlatformBundle:Theme')
        ;

        // On récupère l'entité avec tout le contneu de la DB en array
        $listTheme = $repository->findAll();


        // ou null si theme n'existe pas
        if (null === $listTheme) {
            throw new NotFoundHttpException("Il n'y a aucuns thèmes pour le moment, revenez plus tard !");
        }

        // On passe l'objet
        return $this->render('RTPlatformBundle:Advert:index.html.twig', array(
            'listTheme' => $listTheme
        ));
    }

    public function viewAction($id)
    {
        // Ici, on récupérera l'annonce correspondante à l'id $id

       /* return $this->render('RTPlatformBundle:Advert:view.html.twig', array(
            'id' => $id
        ));*/

        // On récupère le repository
        $repository = $this->getDoctrine()
            ->getManager()
            ->getRepository('RTPlatformBundle:Theme')
        ;

        // On récupère l'entité correspondante à l'id $id
        $theme = $repository->find($id);

        // $advert est donc une instance de OC\PlatformBundle\Entity\Advert
        // ou null si l'id $id  n'existe pas, d'où ce if :
        if (null === $theme) {
            throw new NotFoundHttpException("Le thème d'id ".$id." n'existe pas.");
        }

        // Le render ne change pas, on passait avant un tableau, maintenant un objet
        return $this->render('RTPlatformBundle:Advert:view.html.twig', array(
            'theme' => $theme
        ));
    }

    public function addAction(Request $request)
    {
        // La gestion d'un formulaire est particulière, mais l'idée est la suivante :

        // Si la requête est en POST, c'est que le visiteur a soumis le formulaire
        if ($request->isMethod('POST')) {
            // Ici, on s'occupera de la création et de la gestion du formulaire

            $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');

            // Puis on redirige vers la page de visualisation de cettte annonce
            return $this->redirectToRoute('rt_platform_view', array('id' => 5));
        }

        // Si on n'est pas en POST, alors on affiche le formulaire
        return $this->render('RTPlatformBundle:Advert:add.html.twig');
    }

    public function editAction($id, Request $request)
    {
        // Ici, on récupérera l'annonce correspondante à $id

        // Même mécanisme que pour l'ajout
        /*if ($request->isMethod('POST')) {
            $request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiée.');

            return $this->redirectToRoute('rt_platform_view', array('id' => 5));
        }

        return $this->render('RTPlatformBundle:Advert:edit.html.twig');*/

            // ...

            $advert = array(
                'title'   => 'Recherche développpeur Symfony',
                'id'      => $id,
                'author'  => 'Alexandre',
                'content' => 'Nous recherchons un développeur Symfony débutant sur Lyon. Blabla…',
                'date'    => new \Datetime()
            );

            return $this->render('RTPlatformBundle:Advert:edit.html.twig', array(
                'advert' => $advert
            ));

    }

    public function deleteAction($id)
    {
        // Ici, on récupérera l'annonce correspondant à $id

        // Ici, on gérera la suppression de l'annonce en question

        return $this->render('RTPlatformBundle:Advert:delete.html.twig');
    }
    public function menuAction($limit)
    {
        /*// On fixe en dur une liste ici, bien entendu par la suite
        // on la récupérera depuis la BDD !
        $listAdverts = array(
            array('id' => 2, 'title' => 'Recherche développeur Symfony'),
            array('id' => 5, 'title' => 'Mission de webmaster'),
            array('id' => 9, 'title' => 'Offre de stage webdesigner')
        );

        return $this->render('RTPlatformBundle:Advert:menu.html.twig', array(
            // Tout l'intérêt est ici : le contrôleur passe
            // les variables nécessaires au template !
            'listAdverts' => $listAdverts
        ));
        */
        // On récupère le repository
        $repository = $this->getDoctrine()
            ->getManager()
            ->getRepository('RTPlatformBundle:Theme')
        ;

        // On récupère l'entité avec tout le contneu de la DB en array
        $listTheme = $repository->findAll();


        // ou null si theme n'existe pas
        if (null === $listTheme) {
            throw new NotFoundHttpException("Il n'y a aucuns thèmes pour le moment, revenez plus tard !");
        }

        // On passe l'objet
        return $this->render('RTPlatformBundle:Advert:menu.html.twig', array(
            'listTheme' => $listTheme
        ));

    }
}

//class AdvertController extends Controller
//{
//
//    // La route fait appel à RTPlatformBundle:Advert:view,
//    // on doit donc définir la méthode viewAction.
//    // On donne à cette méthode l'argument $id, pour
//    // correspondre au paramètre {id} de la route
//    public function viewAction($id, Request $request)
//    {
//        // Récupération de la session
//        /*$session = $request->getSession();
//
//        // On récupère le contenu de la variable user_id
//        $userId = $session->get('user_id');
//
//        // On définit une nouvelle valeur pour cette variable user_id
//        $session->set('user_id', 91);
//
//        // On n'oublie pas de renvoyer une réponse
//        return new Response("<body>Je suis une page de test, je n'ai rien à dire</body>");*/
//
//        // On récupère notre paramètre tag
//        $tag = $request->query->get('tag');
//
//        /*return new Response(
//            "Affichage de l'annonce d'id : ".$id.", avec le tag : ".$tag
//        );*/
//
//        // On utilise le raccourci : il crée un objet Response
//        // Et lui donne comme contenu le contenu du template
//        /*return $this->render('RTPlatformBundle:Advert:view.html.twig', array(
//            'id'  => $id,
//            'tag' => $tag,
//        ));*/
//
//        // return new JsonResponse(array('id' => $id));
//
//        // return $this->redirectToRoute('rt_platform_home');
//
//        return $this->render('RTPlatformBundle:Advert:view.html.twig', array(
//            'id' => $id
//        ));
//    }
//
//    // On récupère tous les paramètres en arguments de la méthode
//    public function viewSlugAction($slug, $year, $format)
//    {
//        return new Response(
//            "On pourrait afficher l'annonce correspondant au
//            slug '".$slug."', créée en ".$year." et au format ".$format."."
//        );
//    }
//
//	public function indexAction()
//	{
//		$content = $this->get('templating')->render('RTPlatformBundle:Advert:index.html.twig', array('nom' => 'RomainTheBOSS'));
//
//		return new Response($content);
//	}
//
//	public function page2Action()
//	{
//		$content = $this->get('templating')->render('RTPlatformBundle:Advert:page2.html.twig', array('nom' => 'RomainLekéké'));
//
//		return new Response($content);
//	}
//
//    public function addAction(Request $request)
//    {
//        $session = $request->getSession();
//
//        // Bien sûr, cette méthode devra réellement ajouter l'annonce
//
//        // Mais faisons comme si c'était le cas
//        $session->getFlashBag()->add('info', 'Annonce bien enregistrée');
//
//        // Le « flashBag » est ce qui contient les messages flash dans la session
//        // Il peut bien sûr contenir plusieurs messages :
//        $session->getFlashBag()->add('info', 'Oui oui, elle est bien enregistrée !');
//
//        // Puis on redirige vers la page de visualisation de cette annonce
//        return $this->redirectToRoute('rt_platform_view', array('id' => 5));
//    }
//}