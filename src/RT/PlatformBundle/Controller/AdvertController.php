<?php

namespace RT\PlatformBundle\Controller;


use RT\PlatformBundle\Entity\Discussion;
use RT\PlatformBundle\Entity\Theme;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

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

    public function viewAction($id, Request $request)
    {
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

        //Autre facon ici pour les discussion
        $em = $this->getDoctrine()->getManager();
        $discussion = $em->getRepository('RTPlatformBundle:Discussion')->find($id);
        $listDiscussions = $em
            ->getRepository('RTPlatformBundle:Discussion')
            ->findBy(array('theme' => $theme))
        ;

        $discussion = new Discussion();


        // On crée le FormBuilder grâce au service form factory
        $formBuilder = $this->get('form.factory')->createBuilder(FormType::class, $discussion);

        // On ajoute les champs de l'entité que l'on veut à notre formulaire
        $formBuilder
            ->add('date',         DateType::class)
            ->add('heure',        TimeType::class)
            ->add('pseudo',       TextType::class)
            ->add('content',      TextType::class)
            ->add('save',         SubmitType::class)
        ;

        // À partir du formBuilder, on génère le formulaire
        $form = $formBuilder->getForm();

        // Si la requête est en POST
        if ($request->isMethod('POST')) {
            // On fait le lien Requête <-> Formulaire
            // À partir de maintenant, la variable $discussion contient les valeurs entrées dans le formulaire par le visiteur
            $form->handleRequest($request);

            // On vérifie que les valeurs entrées sont correctes
            // (Nous verrons la validation des objets en détail dans le prochain chapitre)
            if ($form->isValid()) {
                $discussion->setTheme($theme);
                // On enregistre notre objet $discussion dans la base de données, par exemple
                $em = $this->getDoctrine()->getManager();
                $em->persist($discussion);
                $em->flush();

                $request->getSession()->getFlashBag()->add('notice', 'Discussion bien enregistrée !');

                // On redirige vers la page de visualisation de l'annonce nouvellement créée
                return $this->redirectToRoute('rt_platform_view', array('id' => $theme->getId()));
            }
        }

        // À ce stade, le formulaire n'est pas valide car :
        // - Soit la requête est de type GET, donc le visiteur vient d'arriver sur la page et veut voir le formulaire
        // - Soit la requête est de type POST, mais le formulaire contient des valeurs invalides, donc on l'affiche de nouveau

        // On passe la méthode createView() du formulaire à la vue
        // afin qu'elle puisse afficher le formulaire toute seule
        // on pase les objets
        return $this->render('RTPlatformBundle:Advert:view.html.twig', array(
            'theme' => $theme,
            'listDiscussions' => $listDiscussions,
            'discussion' => $discussion,
            'form' => $form->createView(),
        ));


    }

    public function addAction(Request $request)
    {
        // On crée un objet Advert
        $theme = new Theme();

        // On crée le FormBuilder grâce au service form factory
        $formBuilder = $this->get('form.factory')->createBuilder(FormType::class, $theme);

        // On ajoute les champs de l'entité que l'on veut à notre formulaire
        $formBuilder

            ->add('titre',     TextType::class)
            ->add('save',      SubmitType::class)
        ;
        // Pour l'instant, pas de candidatures, catégories, etc., on les gérera plus tard

        // À partir du formBuilder, on génère le formulaire
        $form = $formBuilder->getForm();

        // Si la requête est en POST
        if ($request->isMethod('POST')) {
            // On fait le lien Requête <-> Formulaire
            // À partir de maintenant, la variable $theme contient les valeurs entrées dans le formulaire par le visiteur
            $form->handleRequest($request);

            // On vérifie que les valeurs entrées sont correctes
            // (Nous verrons la validation des objets en détail dans le prochain chapitre)
            if ($form->isValid()) {
                // On enregistre notre objet $theme dans la base de données, par exemple
                $em = $this->getDoctrine()->getManager();
                $em->persist($theme);
                $em->flush();

                $request->getSession()->getFlashBag()->add('notice', 'Thème bien enregistré !');

                // On redirige vers la page de visualisation de l'annonce nouvellement créée
                return $this->redirectToRoute('rt_platform_view', array('id' => $theme->getId()));
            }
        }

        // À ce stade, le formulaire n'est pas valide car :
        // - Soit la requête est de type GET, donc le visiteur vient d'arriver sur la page et veut voir le formulaire
        // - Soit la requête est de type POST, mais le formulaire contient des valeurs invalides, donc on l'affiche de nouveau
        return $this->render('RTPlatformBundle:Advert:add.html.twig', array(
            'form' => $form->createView(),
        ));
        // On passe la méthode createView() du formulaire à la vue
        // afin qu'elle puisse afficher le formulaire toute seule


    }

    public function addDiscussionAction($id, Request $request)
    {
        // On récupère le repository
        $repository = $this->getDoctrine()
            ->getManager()
            ->getRepository('RTPlatformBundle:Theme')
        ;
        // On récupère l'entité correspondante à l'id $id
        $theme = $repository->find($id);

        $discussion = new Discussion();

        // On crée le FormBuilder grâce au service form factory
        $formBuilder = $this->get('form.factory')->createBuilder(FormType::class, $discussion);

        // On ajoute les champs de l'entité que l'on veut à notre formulaire
        $formBuilder
            ->add('date',         DateType::class)
            ->add('theme_id',     NumberType::class)
            ->add('heure',        TimeType::class)
            ->add('pseudo',       TextType::class)
            ->add('content',      TextType::class)
            ->add('save',         SubmitType::class)
        ;

        // À partir du formBuilder, on génère le formulaire
        $form = $formBuilder->getForm();

        // Si la requête est en POST
        if ($request->isMethod('POST')) {
            // On fait le lien Requête <-> Formulaire
            // À partir de maintenant, la variable $discussion contient les valeurs entrées dans le formulaire par le visiteur
            $form->handleRequest($request);

            // On vérifie que les valeurs entrées sont correctes
            // (Nous verrons la validation des objets en détail dans le prochain chapitre)
            if ($form->isValid()) {
                // On enregistre notre objet $discussion dans la base de données, par exemple
                $em = $this->getDoctrine()->getManager();
                $em->persist($discussion);
                $em->flush();

                $request->getSession()->getFlashBag()->add('notice', 'Discussion bien enregistrée !');

                // On redirige vers la page de visualisation de l'annonce nouvellement créée
                return $this->redirectToRoute('rt_platform_view', array('id' => $theme->getId()));
            }
        }

        // À ce stade, le formulaire n'est pas valide car :
        // - Soit la requête est de type GET, donc le visiteur vient d'arriver sur la page et veut voir le formulaire
        // - Soit la requête est de type POST, mais le formulaire contient des valeurs invalides, donc on l'affiche de nouveau
        return $this->render('RTPlatformBundle:Advert:addDiscussion.html.twig', array(
            'form' => $form->createView(),
        ));
        // On passe la méthode createView() du formulaire à la vue
        // afin qu'elle puisse afficher le formulaire toute seule


    }

    public function editAction($id, Request $request)
    {

// Récupération d'une annonce déjà existante, d'id $id.
        $theme = $this->getDoctrine()
            ->getManager()
            ->getRepository('RTPlatformBundle:Theme')
            ->find($id)
        ;

// Et on construit le formBuilder avec cette instance de l'annonce, comme précédemment
        $formBuilder = $this->get('form.factory')->createBuilder(FormType::class, $theme);

        // On ajoute les champs de l'entité que l'on veut à notre formulaire
        $formBuilder
            ->add('titre',     TextType::class)
            ->add('save',      SubmitType::class)
        ;
        // Pour l'instant, pas de candidatures, catégories, etc., on les gérera plus tard

        // À partir du formBuilder, on génère le formulaire
        $form = $formBuilder->getForm();

        // Si la requête est en POST
        if ($request->isMethod('POST')) {
            // On fait le lien Requête <-> Formulaire
            // À partir de maintenant, la variable $theme contient les valeurs entrées dans le formulaire par le visiteur
            $form->handleRequest($request);

            // On vérifie que les valeurs entrées sont correctes
            // (Nous verrons la validation des objets en détail dans le prochain chapitre)
            if ($form->isValid()) {
                // On enregistre notre objet $theme dans la base de données, par exemple
                $em = $this->getDoctrine()->getManager();
                $em->persist($theme);
                $em->flush();

                $request->getSession()->getFlashBag()->add('notice', 'Thème bien enregistré !');

                // On redirige vers la page de visualisation de l'annonce nouvellement créée
                return $this->redirectToRoute('rt_platform_view', array('id' => $theme->getId()));
            }
        }

        // À ce stade, le formulaire n'est pas valide car :
        // - Soit la requête est de type GET, donc le visiteur vient d'arriver sur la page et veut voir le formulaire
        // - Soit la requête est de type POST, mais le formulaire contient des valeurs invalides, donc on l'affiche de nouveau
        return $this->render('RTPlatformBundle:Advert:edit.html.twig', array(
            'form' => $form->createView(),
            'theme' => $theme,
        ));
        // On passe la méthode createView() du formulaire à la vue
        // afin qu'elle puisse afficher le formulaire toute seule

    }

    public function editDiscussionAction($id, Request $request)
    {
// On récupère le repository
        $repository = $this->getDoctrine()
            ->getManager()
            ->getRepository('RTPlatformBundle:Theme')
        ;
        // On récupère l'entité correspondante à l'id $id
        $theme = $repository->find($id);

// Récupération d'une discussion déjà existante, d'id $id.
        $discussion = $this->getDoctrine()
            ->getManager()
            ->getRepository('RTPlatformBundle:Discussion')
            ->find($id)
        ;

// Et on construit le formBuilder avec cette instance de l'annonce, comme précédemment
        $formBuilder = $this->get('form.factory')->createBuilder(FormType::class, $discussion);

        // On ajoute les champs de l'entité que l'on veut à notre formulaire
        $formBuilder
            ->add('content',     TextType::class)
            ->add('save',      SubmitType::class)
        ;
        // À partir du formBuilder, on génère le formulaire
        $form_edit = $formBuilder->getForm();

        // Si la requête est en POST
        if ($request->isMethod('POST')) {
            // On fait le lien Requête <-> Formulaire
            // À partir de maintenant, la variable $theme contient les valeurs entrées dans le formulaire par le visiteur
            $form_edit->handleRequest($request);

            // On vérifie que les valeurs entrées sont correctes
            // (Nous verrons la validation des objets en détail dans le prochain chapitre)
            if ($form_edit->isValid()) {
                // On enregistre notre objet $theme dans la base de données, par exemple
                $em = $this->getDoctrine()->getManager();
                $em->persist($discussion);
                $em->flush();

                $request->getSession()->getFlashBag()->add('notice', 'Discussion bien enregistré !');

                // On redirige vers la page de visualisation de l'annonce nouvellement créée
                return $this->redirectToRoute('rt_platform_view', array('id' => $theme->getId()));
            }
        }

        // À ce stade, le formulaire n'est pas valide car :
        // - Soit la requête est de type GET, donc le visiteur vient d'arriver sur la page et veut voir le formulaire
        // - Soit la requête est de type POST, mais le formulaire contient des valeurs invalides, donc on l'affiche de nouveau
        return $this->render('RTPlatformBundle:Advert:view.html.twig', array(
            'form_edit' => $form_edit->createView(),
        ));
        // On passe la méthode createView() du formulaire à la vue
        // afin qu'elle puisse afficher le formulaire toute seule

    }

    public function deleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $theme = $em->getRepository('RTPlatformBundle:Theme')->find($id);

        if (null === $theme) {
            throw new NotFoundHttpException("Le thème d'id ".$id." n'existe pas.");
        }

        // On crée un formulaire vide, qui ne contiendra que le champ CSRF
        // Cela permet de protéger la suppression d'annonce contre cette faille
        $form = $this->get('form.factory')->create();

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->remove($theme);
            $em->flush();

            $request->getSession()->getFlashBag()->add('info', "Le thème a bien été supprimé.");

            return $this->redirectToRoute('rt_platform_home');
        }

        return $this->render('RTPlatformBundle:Advert:delete.html.twig', array(
            'theme' => $theme,
            'form'   => $form->createView(),
        ));
    }
    public function deleteDiscussionAction(Request $request, $id_discussion)
    {
        $em = $this->getDoctrine()->getManager();

     //   $theme = $em->getRepository('RTPlatformBundle:Theme')->find();
        $discussion = $em->getRepository('RTPlatformBundle:Discussion')->find($id_discussion);

        if (null === $discussion) {
            throw new NotFoundHttpException("La discussion d'id ".$id_discussion." n'existe pas.");
        }

        // On crée un formulaire vide, qui ne contiendra que le champ CSRF
        // Cela permet de protéger la suppression d'annonce contre cette faille
        $form = $this->get('form.factory')->create();

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->remove($discussion);
            $em->flush();

            $request->getSession()->getFlashBag()->add('info', "La discussion a bien été supprimée.");

            return $this->redirectToRoute('rt_platform_view', array('id' => $discussion->getTheme()->getId()));
           // return $this->redirectToRoute('rt_platform_home');
        }

        return $this->render('RTPlatformBundle:Advert:deleteDiscussion.html.twig', array(
            'discussion' => $discussion,
            'form'   => $form->createView(),
        ));
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