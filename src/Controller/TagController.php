<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Form\TagType;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Repository\TagRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("tag")
 */
class TagController extends AbstractController
{
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var ArticleRepository
     */
    private $articleRepository;

    /**
     * @var TagRepository
     */
    private $tagRepository;

    /**
     * BlogController constructor.
     * @param CategoryRepository $categoryRepository
     * @param ArticleRepository $articleRepository
     * @param TagRepository $tagRepository
     * @param ObjectManager $manager
     */
    public function __construct(CategoryRepository $categoryRepository, ArticleRepository $articleRepository, TagRepository $tagRepository, ObjectManager $manager)
    {
        $this->categoryRepository = $categoryRepository;
        $this->manager = $manager;
        $this->articleRepository = $articleRepository;
        $this->tagRepository = $tagRepository;
    }


    /**
     * @Route("/", name="tag_index", methods="GET")
     * @param TagRepository $tagRepository
     * @return Response
     */
    public function index(TagRepository $tagRepository): Response
    {
        return $this->render('tag/index.html.twig', ['tags' => $tagRepository->findAll()]);
    }

    /**
     * @Route("/new", name="tag_new", methods="GET|POST")
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $tag = new Tag();
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($tag);
            $em->flush();

            return $this->redirectToRoute('tag_index');
        }

        return $this->render('tag/new.html.twig', [
            'tag' => $tag,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Show articles according to tag
     *
     * @Route("/{tag}", name="blog_show_tag")
     * @ParamConverter("tag", options={"mapping": {"tag": "name"}})
     * @param Tag $tag
     * @return                     Response A response instance
     */
    public function showByTag(Tag $tag)
    {
        return $this->render(
            'tag/show.html.twig', [
            'tag' => $tag,
            'articles' => $tag->getArticles()
        ]);
    }

    /**
     * @Route("/{id}/edit", name="tag_edit", methods="GET|POST")
     * @param Request $request
     * @param Tag $tag
     * @return Response
     */
    public function edit(Request $request, Tag $tag): Response
    {
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('tag_index', ['id' => $tag->getId()]);
        }

        return $this->render('tag/edit.html.twig', [
            'tag' => $tag,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="tag_delete", methods="DELETE")
     * @param Request $request
     * @param Tag $tag
     * @return Response
     */
    public function delete(Request $request, Tag $tag): Response
    {
        if ($this->isCsrfTokenValid('delete'.$tag->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($tag);
            $em->flush();
        }

        return $this->redirectToRoute('tag_index');
    }
}
