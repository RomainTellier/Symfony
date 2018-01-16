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
use Doctrine\ORM\Tools\Pagination\Paginator;


class AdvertController extends Controller
{
    public function userAction() {
        $user = $this->getUser();
        return $this->render('RTPlatformBundle::layout.html.twig', array(
            'user' => $user
        ));

    }
    public function listUsersAction() {
        $user = $this->getUser();
        $userManager = $this->get('fos_user.user_manager');
        $users = $userManager->findUsers();
        return $this->render('RTPlatformBundle:Advert:listUsers.html.twig', array(
            'users' =>   $users,
            'user' => $user
        ));
    }
    public function indexAction($page= 0)
    {
        // On sait qu'une page doit être supérieure ou égale à 0
        if ($page < 0) {
            // On déclenche une exception NotFoundHttpException, cela va afficher
            // une page d'erreur 404 (qu'on pourra personnaliser plus tard d'ailleurs)
            throw new NotFoundHttpException('Page "'.$page.'" inexistante.');
        }

        // Ici, on récupérera la liste des theme, puis on la passera au template

        // On récupère le repository
        $repository = $this->getDoctrine()
            ->getManager()
            ->getRepository('RTPlatformBundle:Theme')
        ;

        // On récupère l'entité avec tout le contenu de la DB en array
        $listTheme = $repository->findAll();
        $user = $this->getUser();
        // ou null si theme n'existe pas
        if (null === $listTheme) {
            throw new NotFoundHttpException("Il n'y a aucuns thèmes pour le moment, revenez plus tard !");
        }

            //Récupération des produits avec le numéro de page (1 si non renseigné)
            //et le maximum de produits à afficher (30 ici)
            $themesList = $this->getDoctrine()->getRepository('RTPlatformBundle:Theme')
                ->findAll($page, 5);

            //Informations pour la pagination: la page actuelle, le nom de la route,
            //le nombre de pages retournées (un count de $themeList donne le nombre total de theme)
            $pagination = array(
                'page' => $page+1,
                'route' => 'rt_platform_home',
                'pages_count' => ceil(count($themesList) / 5),
                'route_params' => array()
            );

        //On retourne le tout
        // On passe l'objet
        return $this->render('RTPlatformBundle:Advert:index.html.twig', array(
            'listTheme' => $listTheme,
            'user' => $user,
            'themesList' => $themesList,
            'pagination' => $pagination
        ));
    }

    public function viewAction($id, $page=1,Request $request)
    {
        $user = $this->getUser();

        // On récupère le repository
        $repository = $this->getDoctrine()
            ->getManager()
            ->getRepository('RTPlatformBundle:Theme')
        ;
        // On récupère l'entité correspondante à l'id $id
        $theme = $repository->find($id);


        if (null === $theme) {
            throw new NotFoundHttpException("Le thème d'id ".$id." n'existe pas.");
        }

        //Autre facon ici pour les discussion
        $em = $this->getDoctrine()->getManager();
        $listDiscussions = $em
            ->getRepository('RTPlatformBundle:Discussion')
            ->findBy(array('theme'=>$theme))
        ;
        /**
         * @var $paginator \Knp\Component\Pager\Paginator
         */
        $paginator = $this->get('knp_paginator');
        $result = $paginator->paginate(
          $listDiscussions,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 10)
        );

        $discussion = new Discussion();


        // On crée le FormBuilder grâce au service form factory
        $formBuilder = $this->get('form.factory')->createBuilder(FormType::class, $discussion);

        // On ajoute les champs de l'entité que l'on veut à notre formulaire
        $formBuilder

            ->add('content',      TextType::class)
            ->add('Enregistrer',         SubmitType::class)
        ;

        // À partir du formBuilder, on génère le formulaire
        $form = $formBuilder->getForm();

        // Si la requête est en POST
        if ($request->isMethod('POST')) {
            // On fait le lien Requête <-> Formulaire
            // À partir de maintenant, la variable $discussion contient les valeurs entrées dans le formulaire par le visiteur
            $form->handleRequest($request);

            // On vérifie que les valeurs entrées sont correctes
            if ($form->isValid()) {
                $discussion->setTheme($theme);
                $discussion->setPseudo($user);
                // On enregistre notre objet $discussion dans la base de données, par exemple
                $em = $this->getDoctrine()->getManager();
                $em->persist($discussion);
                $em->flush();

                //$request->getSession()->getFlashBag()->add('notice', 'Discussion bien enregistrée !');

                // On redirige vers la page de visualisation de la discussion nouvellement créée
                return $this->redirectToRoute('rt_platform_view', array('id' => $theme->getId()));
            }
        }

        // on pase les objets
        return $this->render('RTPlatformBundle:Advert:view.html.twig', array(
            'theme' => $theme,
            'listDiscussions' => $result,
            'discussion' => $discussion,
            'form' => $form->createView(),
            'user' => $user,

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
            ->add('Enregistrer',      SubmitType::class)
        ;

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

               // $request->getSession()->getFlashBag()->add('notice', 'Thème bien enregistré !');

                // On redirige vers la page de visualisation de l'annonce nouvellement créée
                return $this->redirectToRoute('rt_platform_view', array('id' => $theme->getId()));
            }
        }
        $user = $this->getUser();


        return $this->render('RTPlatformBundle:Advert:add.html.twig', array(
            'form' => $form->createView(),
            'user' => $user,
        ));
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

               // $request->getSession()->getFlashBag()->add('notice', 'Thème bien enregistré !');

                // On redirige vers la page de visualisation de l'annonce nouvellement créée
                return $this->redirectToRoute('rt_platform_view', array('id' => $theme->getId()));
            }
        }
        $user = $this->getUser();

        // À ce stade, le formulaire n'est pas valide car :
        // - Soit la requête est de type GET, donc le visiteur vient d'arriver sur la page et veut voir le formulaire
        // - Soit la requête est de type POST, mais le formulaire contient des valeurs invalides, donc on l'affiche de nouveau
        return $this->render('RTPlatformBundle:Advert:edit.html.twig', array(
            'form' => $form->createView(),
            'theme' => $theme,
            'user' => $user,
        ));

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
                $em->persist($discussion);
                $em->flush();

             //   $request->getSession()->getFlashBag()->add('notice', 'Discussion bien enregistré !');

                // On redirige vers la page de visualisation de l'annonce nouvellement créée
                return $this->redirectToRoute('rt_platform_view', array('id' => $discussion->getTheme()->getId()));
            }
        }
        $user = $this->getUser();


        return $this->render('RTPlatformBundle:Advert:editDiscussion.html.twig', array(
            'form' => $form->createView(),
            'theme' => $theme,
            'user' => $user,
        ));

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

           // $request->getSession()->getFlashBag()->add('info', "Le thème a bien été supprimé.");

            return $this->redirectToRoute('rt_platform_home');
        }
        $user = $this->getUser();

        return $this->render('RTPlatformBundle:Advert:delete.html.twig', array(
            'theme' => $theme,
            'form'   => $form->createView(),
            'user' => $user,
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

         //   $request->getSession()->getFlashBag()->add('info', "La discussion a bien été supprimée.");

            return $this->redirectToRoute('rt_platform_view', array('id' => $discussion->getTheme()->getId()));
           // return $this->redirectToRoute('rt_platform_home');
        }
        $user = $this->getUser();

        return $this->render('RTPlatformBundle:Advert:deleteDiscussion.html.twig', array(
            'discussion' => $discussion,
            'form'   => $form->createView(),
            'user' => $user,
        ));
    }
    public function menuAction()
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
        $user = $this->getUser();

        // On passe l'objet
        return $this->render('RTPlatformBundle:Advert:menu.html.twig', array(
            'listTheme' => $listTheme,
            'user' => $user,
        ));

    }
}