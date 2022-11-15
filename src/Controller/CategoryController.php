<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializationContext;

class CategoryController extends AbstractController
{
    #[Route('/category', name: 'app_category')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/CategoryController.php',
        ]);
    }

    #[Route('/api/categories', name: 'categories.getAll', methods: ['GET'])]
    public function getAllCategories(
        CategoryRepository $repository,
        Request $request,
        SerializerInterface $serializer,
        TagAwareCacheInterface $cache
    ) :JsonResponse
    {
        $idCache = 'getCategory';
        $jsonCategories = $cache->get($idCache, function (ItemInterface $item) use ($repository, $serializer, $request) {
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 5);
            $limit = min($limit, 20);

            $item->tag("getCategory");
            $context = SerializationContext::create()->setGroups('getAllCategories');


            $categories = $repository->findCategories($page, $limit);
            return $serializer->serialize($categories, 'json', $context);
        });

        return new JsonResponse($jsonCategories, Response::HTTP_OK, [], true);
    }

    #[Route('/api/category/{idCategory}', name: 'categories.getCategory', methods: ['GET'])]
    #[ParamConverter("category", options: ["id" => "idCategory"], class: 'App\Entity\Category')]
    public function getCategory(
        Category $category,
        CategoryRepository $repository,
        Request $request,
        SerializerInterface $serializer,
        TagAwareCacheInterface $cache
    ) :JsonResponse
    {
        $idCache = 'getCategory';
        $jsonCategory = $cache->get($idCache, function (ItemInterface $item) use ($repository, $serializer, $request, $category) {
            $item->tag("getCategory");
            $context = SerializationContext::create()->setGroups('getCategory');

            $categories = $repository->find($category);
            return $serializer->serialize($categories, 'json', $context);
        });

        return new JsonResponse($jsonCategory, Response::HTTP_OK, [], true);
    }

    #[Route('/api/category/{idCategory}', name: 'categorys.deleteCategory', methods: ['DELETE'])]
    #[ParamConverter("category", options: ["id" => "idCategory"], class: 'App\Entity\category')]
    public function deleteCategory(
        Category $category,
        EntityManagerInterface $entityManager,
        TagAwareCacheInterface $cache
    ) :JsonResponse
    {
        $cache->invalidateTags(["getCategory"]);
        $category->setStatus("0");
        return new JsonResponse(null, Response::HTTP_OK);
    }

    #[Route('/api/category', name: '$category.create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'êtes pas admin')]
    public function createCategory(
        Category $category,
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator,
        ValidatorInterface $validator,
    ) :JsonResponse
    {
        $updateCategory = $serializer->deserialize(
            $request->getContent(),
            Category::class,
            'json');
        $category->setName($updateCategory->getName() ? $updateCategory->getName() : $category->getName());
        $category->setType($updateCategory->getType() ? $updateCategory->getType() : $category->getType());
        $category->setStatus("1");

        $errors = $validator->validate($category);
        if ($errors->count() >0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }

        $entityManager->persist($category);
        $entityManager->flush();

        $location = $urlGenerator->generate("categorys.getCategory", ['idCategory' => $category->getId(), UrlGeneratorInterface::ABSOLUTE_URL]);
        $jsonCategory = $serializer->serialize($category, 'json', ['groups' => 'getCategory']);
        return new JsonResponse($jsonCategory, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/category/{id}', name: 'categorys.updatecategory', methods: ['PATCH'])]
    #[ParamConverter("category", options: ["id" => "idCategory"], class: 'App\Entity\Category')]
    public function updateCategory(
        Category $category,
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator,
        ValidatorInterface $validator,
    ) :JsonResponse
    {
        $category = $serializer->deserialize($request->getContent(), Category::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $category]);
        $category->setStatus(true);

        $content = $request->toArray();
        //$idBoutique = $content['idBoutique'];
        //$category->addBoutiqueCategorie($categorieRepository->find($idBoutique));

        $erors = $validator->validate($category);
        if ($erors->count() >0) {
            return new JsonResponse($serializer->serialize($erors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $entityManager->persist($category);
        $entityManager->flush();

        $location = $urlGenerator->generate("categorys.getCategory", ['idCategory' => $category->getId(), UrlGeneratorInterface::ABSOLUTE_URL]);
        $jsonCategory = $serializer->serialize($category, "json", ['groups' => 'getCategory']);
        return new JsonResponse($jsonCategory, Response::HTTP_CREATED, ["Location" => $location], true);
    }
}
