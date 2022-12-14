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
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\Serializer;

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
        SerializerInterface $serializer,
        Request $request,
        TagAwareCacheInterface $cache
    ) :JsonResponse
    {
        $idCache = 'getAllCategories';
        $jsonCategory = $cache->get($idCache, function (ItemInterface $item) use ($repository, $serializer, $request) {
            $item->tag('CategoryCache');
            $context = SerializationContext::create()->setGroups(["getAllCategories"]);

            $page = $request->get('page', 1);
            $limit = $request->get('limit', 5);
            $limit = min($limit, 20);

            $category = $this->json($repository->findCategories($page, $limit));
            return $serializer->serialize($category, 'json', $context);
        } );
        return new JsonResponse($jsonCategory, 200, [], true);
    }

    #[Route('/api/category/{idCategory}', name: 'categories.getCategory', methods: ['GET'])]
    #[ParamConverter("category", class: 'App\Entity\Category', options: ["id" => "idCategory"])]
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

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     */
    #[Route('/api/category/{idCategory}', name: 'categories.deleteCategory', methods: ['DELETE'])]
    #[ParamConverter("category", class: 'App\Entity\Category', options: ["id" => "idCategory"])]
    public function deleteCategory(
        Category $category,
        EntityManagerInterface $entityManager,
        TagAwareCacheInterface $cache
    ) :JsonResponse
    {
        $cache->invalidateTags(["getCategory"]);
        $category->setStatus("0");
        $entityManager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/category', name: 'category.create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'??tes pas admin')]
    public function createCategory(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
    ) :JsonResponse
    {
        $newCategory = $serializer->deserialize(
            $request->getContent(),
            Category::class,
            'json');

        $errors = $validator->validate($newCategory);
        if ($errors->count() >0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }
        $newCategory->setStatus("1");
        $entityManager->persist($newCategory);
        $entityManager->flush();

        $context = SerializationContext::create()->setGroups(["getAllCategories"]);

        //$location = $urlGenerator->generate("categories.getCategory", ['idCategory' => $category->getId(), UrlGeneratorInterface::ABSOLUTE_URL]);
        $jsonCategory = $serializer->serialize($newCategory, 'json', $context /*['groups' => 'getCategory']*/);
        return new JsonResponse($jsonCategory, Response::HTTP_CREATED, [], true);
    }

    // update route
    #[Route('/api/category/{idCategory}', name: 'Category.update', methods: ['PUT'])]
    #[ParamConverter("category", class: 'App\Entity\Category', options: ["id" => "idCategory"])]
    public function updateCategory(
        Category $category,
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        CategoryRepository $categoryRepository,
        ValidatorInterface $validator,
        UrlGeneratorInterface $urlGenerator
    ): JsonResponse {

        // $Category = $serializer->deserialize(
        //     $request->getContent(),
        //     Category::class,
        //     'json',
        //     [AbstractNormalizer::OBJECT_TO_POPULATE => $Category]
        // );

        $updateCategory = $serializer->deserialize(
            $request->getContent(),
            Category::class,
            'json'
        );

        $category->setName($updateCategory->getName() ? $updateCategory->getName() : $category->getName());
        $category->setType($updateCategory->getType() ? $updateCategory->getType() : $category->getType());
        $category->setStatus("1");

        $errors = $validator->validate($category);
        if ($errors->count() >0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }

        $entityManager->persist($category);
        $entityManager->flush();

        $location = $urlGenerator->generate("categories.getCategory", ['idCategory' => $category->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        $context = SerializationContext::create()->setGroups(["getAllCategories"]);

        $jsonBoutique = $serializer->serialize($category, 'json', $context);
        return new JsonResponse($jsonBoutique, Response::HTTP_CREATED, [$location => ''], true);
    }
}
